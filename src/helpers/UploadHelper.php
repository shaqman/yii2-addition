<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\helpers;

use Yii;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\base\Model;
use yii\di\Instance;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Description of UploadHelper
 *
 * @author Yasser
 *
 * @property array $files
 */
class UploadHelper extends BaseObject {

    private $uploadedFiles = [];
    /*
     * @var yii\base\Model
     */
    public $model;
    public $attribute;
    public $filenameTemplate = "{filename}_{index}.{extension}";
    public $delimiter = '#';
    public $saveInSession = false;
    public $sessionName;
    public $randomizeFileName = true;
    public $temporaryPath;
    public $path;
    public $fileIndex = 1;

    /**
     *
     * @param string $sessionName
     * @param string $config
     * @return UploadHelper
     */
    public static function getInstance($sessionName, $config = []) {
        $session = Yii::$app->session->get($sessionName);
        return $session instanceof UploadHelper ? $session : new UploadHelper(ArrayHelper::merge($config, ['sessionName' => $sessionName]));
    }

    public function init() {
        parent::init();

        $this->sessionName = $this->saveInSession && empty($this->sessionName) ? Yii::$app->controller->id . '/' . Yii::$app->controller->action->id : $this->sessionName;
        $session = Yii::$app->session->get($this->sessionName);
        if ($this->saveInSession && $session instanceof UploadHelper) {
            return $session;
        }

        if (empty($this->path)) {
            throw new Exception("Path cannot be empty.");
        }

        $this->path = Yii::getAlias($this->path);

        if (!FileHelper::createDirectory($this->path)) {
            throw new Exception("Directory ($this->path) cannot be created. Most likely you don't have permission to modify the filesystem");
        }

        $this->model = Instance::ensure($this->model, Model::class);
        if (!$this->model->isAttributeActive($this->attribute)) {
            throw new Exception("Invalid attribute.");
        }

        $this->temporaryPath = $this->temporaryPath ?: Yii::getAlias('@runtime/uploads');
        if (!FileHelper::createDirectory($this->temporaryPath)) {
            throw new Exception("Temporary directory ($this->temporaryPath) cannot be created. Most likely you don't have permission to modify the filesystem");
        }

        if ($this->randomizeFileName) {
            $this->filenameTemplate = str_replace("{filename}", time() . "_" . Yii::$app->security->generateRandomString(), $this->filenameTemplate);
        }

        if ($this->saveInSession) {
            Yii::$app->session->set($this->sessionName, $this);
        }
    }

    public function doUpload($useTemporaryFolder = true) {
        $files = UploadedFile::getInstances($this->model, $this->attribute);
        if (count($files) == 0) {
            return null;
        }

        $filenames = [];
        foreach ($files as $file) {
            $filenames[] = str_replace(['{filename}', '{index}', '{extension}'], [$file->name, str_pad($this->fileIndex++, 3, '0', STR_PAD_LEFT), $file->extension], $this->filenameTemplate);
            $filename = end($filenames);
            $path = $useTemporaryFolder ? $this->temporaryPath : $this->path;
            Yii::debug($path);
            if ($file->saveAs($path . DIRECTORY_SEPARATOR . $filename)) {
                $file->name = $filename;
                $this->uploadedFiles[] = $file;
                if ($this->saveInSession) {
                    Yii::$app->session->set($this->sessionName, $this);
                }
            } else {
                throw new Exception("File $filename was failed to saved in path: {$path}{$filename} with error: {$file->error}");
            }
        }

        return implode($this->delimiter, $filenames);
    }

    public function save($path = null) {
        $path = $path ?: $this->path;
        if (!FileHelper::createDirectory($path)) {
            throw new Exception("Directory '$path' cannot be created. Most likely you don't have permission to modify the filesystem");
        }

        if (count($this->uploadedFiles) == 0) {
            Yii::debug('Seems that no file(s) left needs to be saved. Removing session...', self::class);
            $this->sessionDestroy();
            return null;
        }

        $fileNames = [];
        foreach ($this->uploadedFiles as $index => $file) {
            $temporaryPath = $this->temporaryPath . DIRECTORY_SEPARATOR . $file->name;
            if (is_file($temporaryPath)) {
                $fileNames[] = $file->name;
                rename($temporaryPath, $this->path . DIRECTORY_SEPARATOR . end($fileNames));
            }

            unset($this->uploadedFiles[$index]);
        }

        $this->sessionDestroy();
        return $fileNames;
    }

    public function getFiles() {
        return $this->uploadedFiles;
    }

    public function sessionDestroy() {
        Yii::$app->session->remove($this->sessionName);
    }

}
