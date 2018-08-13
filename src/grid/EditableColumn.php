<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\grid;

use Closure;
use yii\helpers\ArrayHelper;

/**
 * Description of EditableColumn
 *
 * @author syakur
 */
class EditableColumn extends \kartik\grid\EditableColumn {

    public $randomizeId = false;
    public $editableAttribute;
    public $transferKey = false;
    public $transferIndex = false;
    private $originalAttribute;

    public function renderDataCellContent($model, $key, $index) {
        if ($this->randomizeId) {
            if (!empty($this->editableOptions) && $this->editableOptions instanceof Closure) {
                $this->editableOptions = call_user_func($this->editableOptions, $model, $key, $index, $this);
            }

            $this->editableOptions = ArrayHelper::merge($this->editableOptions, ['options' => ['id' => md5(time() . rand() . '-' . $this->attribute . '-' . $index)]]);
            if ($this->transferKey) {
                $this->editableOptions = ArrayHelper::merge($this->editableOptions, ['options' => ['key' => $key]]);
            }

            if ($this->transferIndex) {
                $this->editableOptions = ArrayHelper::merge($this->editableOptions, ['options' => ['index' => $index]]);
            }
        }

        if (!empty($this->editableAttribute)) {
            $this->originalAttribute = $this->attribute;
            $this->attribute = $this->editableAttribute;
        }
        $result = parent::renderDataCellContent($model, $key, $index);
        if (!empty($this->originalAttribute)) {
            $this->attribute = $this->originalAttribute;
        }

        return $result;
    }

}
