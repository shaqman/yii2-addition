<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\traits;

use Yii;

/**
 * Description of ConfigHelper
 *
 * @author syakur
 */
trait ConfigHelper {

    public function getDsnAttribute($name, $dsn) {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return $match[1];
        } else {
            return null;
        }
    }

    protected function backupDb() {
        shell_exec($this->getMySqlCommand('mysqldump') . ' > ' . Yii::$app->params['backup-filename']);
    }

    protected function restoreDb() {
        shell_exec($this->getMySqlCommand() . ' < ' . Yii::$app->params['backup-filename']);
    }

    private function getMySqlCommand($command = 'mysql') {
        $mysql_host = $this->getDsnAttribute('host', Yii::$app->db->dsn) . ' ';
        $mysql_credential = '-h ' . $mysql_host . ' -u ' . Yii::$app->db->username . ' --password=' . Yii::$app->db->password . ' ';
        $mysql_database = $this->getDsnAttribute('dbname', Yii::$app->db->dsn) . ' ';
        $mysql_command = $command . $mysql_credential . ' ';

        return $mysql_command . $mysql_database . ' ';
    }

}
