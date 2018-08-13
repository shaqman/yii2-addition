<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\grid;

use kartik\grid\Module;
use Yii;
use yii\web\Response;

class EditableColumnAction extends \kartik\grid\EditableColumnAction {

    public $customSaveCallback;

    public function run() {
        $m = Yii::$app->getModule(Module::MODULE);
        if (is_array($this->formName)) {
            $formNames = $this->formName;
            while (count($formNames)) {
                $this->formName = array_pop($formNames);
                $out = $this->validateEditable();
                if ($out['message'] === '') {
                    break;
                }
            }
        } else {
            $out = $this->validateEditable();
        }

        unset($m);
        return Yii::createObject(['class' => Response::className(), 'format' => Response::FORMAT_JSON, 'data' => $out]);
    }

    protected function validateEditable() {
        if (empty($this->customSaveCallback)) {
            return parent::validateEditable();
        } else {
            return call_user_func($this->customSaveCallback);
        }
    }

}
