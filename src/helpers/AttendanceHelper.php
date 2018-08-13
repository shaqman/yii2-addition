<?php

/*
 * The MIT License
 *
 * Copyright 2017 syakur.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace shaqman\addition\helpers;

use common\models\attendance\EntryLog;
use common\models\attendance\Fingerprint;
use common\models\attendance\SystemLog;
use common\models\attendance\User;
use common\models\attendance\UserMapping;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Yii;
use yii\base\BaseObject;
use yii\base\DynamicModel;
use yii\base\Exception;
use yii\helpers\Json;
use yii\helpers\VarDumper;

/**
 * Description of AttendanceHelper
 *
 * @author syakur
 */
class AttendanceHelper extends BaseObject {

    private $modeList = [
        'get-att-log' => 'test',
        'get-user-info' => 'test',
        'get-device-info' => 'test',
        'get-device-log' => 'test'
    ];
    private $configFile = 'config';
    private $resultFile = 'result';
    // Later, make this as parameterized
    public $pythonExecutablePath;
    public $pythonScriptBasePath;
    public $ip;
    public $port;
    public $memoryLimit = 1024;

    public function init() {
        parent::init();
        ini_set("memory_limit", $this->memoryLimit . "M");
        set_time_limit(60 * 60); // 1 hour(s)

        if (empty($this->ip) || empty($this->port)) {
            throw new Exception("Ip and port contains valid input.");
        }

        $this->pythonExecutablePath = $this->pythonExecutablePath ?: Yii::$app->params['att']['python.executable'];
        $this->pythonScriptBasePath = $this->pythonScriptBasePath ?: Yii::$app->params['att']['python.scriptBasePath'];
    }

    private function getExecutable($filename, $extension = 'py') {
        return "{$this->pythonExecutablePath} {$this->getFile($filename, $extension)}";
    }

    private function getFile($filename, $extension = 'py') {
        return "{$this->pythonScriptBasePath}$filename.$extension";
    }

    private function generateConfigFile() {
        $model = new DynamicModel(['ip', 'port']);
        $model->ip = $this->ip;
        $model->port = $this->port;

        $fp = fopen($this->getFile($this->configFile, 'json'), 'w');
        fwrite($fp, Json::encode($model));
        fclose($fp);
    }

    private function readResultFile() {
        return Json::decode(file_get_contents($this->getFile($this->resultFile, 'json')));
    }

    public function execute($mode) {
        if (!in_array($mode, array_keys($this->modeList))) {
            throw new Exception('$mode should be one of get-att-log, get-user-info, get-device-info, or get-device-log');
        }

        $this->generateConfigFile();

        $process = new Process($this->getExecutable($mode));
        $process->setTimeout(60 * 60); //60 minutes
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $this->readResultFile();
    }

    private static function isPortOpened($ip, $port) {
        $portOpened = false;
        $connection = @fsockopen($ip, $port);
        if (is_resource($connection)) {
            $portOpened = true;
            fclose($connection);
        }

        return $portOpened;
    }

    public static function loadDevicesInfo($devices) {
        foreach ($devices as &$device) {
            if (static::isPortOpened($device->ip_address, $device->port)) {
                $attHelper = new AttendanceHelper([
                    'ip' => $device->ip_address,
                    'port' => $device->port
                ]);

                $device->load($attHelper->execute('get-device-info'));
                $device->save();
            }
        }

        return $devices;
    }

    private static function loadUserDataToDatabase($device, $rawUser) {
        $user = User::find()->joinWith('userMappings')->where(['device_id' => $device->id, UserMapping::tableName() . '.enroll_sequence' => $rawUser['enrollSequence']])->one() ?: new User();
        $user->load($rawUser);
        $user->row_status = $rawUser['isEnabled'] ? User::ROW_STATUS_ACTIVE : User::ROW_STATUS_DELETED;
        $user->fullname = $user->fullname ?: "Unknown user ({$device->id}:{$rawUser['enrollSequence']})";
        if (!$user->save()) {
            Yii::error(VarDumper::dumpAsString($user->getErrors()));
        } else {
            foreach ($rawUser['fingerprint'] as $rawFingerprint) {
                $fingerprint = Fingerprint::find()->where(['user_id' => $user->id, 'finger_id' => $rawFingerprint['fingerId']])->one() ?: new Fingerprint();
                $fingerprint->load($rawFingerprint);
                $fingerprint->user_id = $user->id;
                if (!$fingerprint->save()) {
                    Yii::error(VarDumper::dumpAsString($fingerprint->getErrors()));
                }
            }

            $userMapping = UserMapping::find()->where(['user_id' => $user->id, 'device_id' => $device->id])->one() ?: new UserMapping();
            $userMapping->load($rawUser);
            $userMapping->user_id = $user->id;
            $userMapping->device_id = $device->id;
            if (!$userMapping->save()) {
                Yii::error(VarDumper::dumpAsString($userMapping->getErrors()));
            }

            return $user;
        }
        return null;
    }

    public static function loadUserFromDevices($devices) {
        foreach ($devices as &$device) {
            if (static::isPortOpened($device->ip_address, $device->port)) {
                $attHelper = new AttendanceHelper([
                    'ip' => $device->ip_address,
                    'port' => $device->port
                ]);

                $rawUsers = $attHelper->execute('get-user-info');
                foreach ($rawUsers as $rawUser) {
                    static::loadUserDataToDatabase($device, $rawUser);
                }
            }
        }
    }

    private static function loadEntryLogToDatabase($device, $rawLog) {
        $entryLog = EntryLog::find()->where(['entry_time' => $rawLog['entryTime'], 'device_id' => $device->id])->one();
        if (!$entryLog) {
            $entryLog = new EntryLog();
            $entryLog->load($rawLog);
            $entryLog->device_id = $device->id;
            $entryLog->user_id = UserMapping::getUserMapping($device->id, $rawLog['enrollSequence'])->user_id;
            if (!$entryLog->save()) {
                Yii::error(VarDumper::dumpAsString($entryLog->getErrors()));
            }
        }

        return $entryLog;
    }

    public static function loadEntryLogFromDevices($devices) {
        foreach ($devices as &$device) {
            if (static::isPortOpened($device->ip_address, $device->port)) {
                $attHelper = new AttendanceHelper([
                    'ip' => $device->ip_address,
                    'port' => $device->port
                ]);

                $rawLogs = $attHelper->execute('get-att-log');
                foreach ($rawLogs as $rawLog) {
                    static::loadEntryLogToDatabase($device, $rawLog);
                }
            }
        }
    }

    private static function loadDeviceLogToDatabase($device, $rawLog) {
        $deviceLog = SystemLog::find()->where(['event_time' => $rawLog['eventTime'], 'device_id' => $device->id])->one();
        if (!$deviceLog) {
            $userMapping = UserMapping::getUserMapping($device->id, $rawLog['enrollSequence']);

            $deviceLog = new SystemLog();
            $deviceLog->load($rawLog);
            $deviceLog->device_id = $device->id;
            $deviceLog->user_id = $userMapping->user_id;
            if (!$deviceLog->save()) {
                Yii::error($rawLog);
                Yii::error(VarDumper::dumpAsString($deviceLog->attributes));
                Yii::error(VarDumper::dumpAsString($deviceLog->getErrors()));
            }
        }

        return $deviceLog;
    }

    public static function loadDeviceLogFromDevices($devices) {
        foreach ($devices as &$device) {
            if (static::isPortOpened($device->ip_address, $device->port)) {
                $attHelper = new AttendanceHelper([
                    'ip' => $device->ip_address,
                    'port' => $device->port
                ]);

                $rawLogs = $attHelper->execute('get-device-log');
                foreach ($rawLogs as $rawLog) {
                    static::loadDeviceLogToDatabase($device, $rawLog);
                }
            }
        }
    }

}
