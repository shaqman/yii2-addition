<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\grid;

/**
 * Description of FormattedIdColumn
 *
 * @author syakur
 */
class FormattedIdColumn extends \yii\grid\Column {

    /**
     * @inheritdoc
     */
    public $header = 'ID';
    public $prepend = '';
    public $length = 9;

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index) {
        $id = $this->prepend . str_pad($model->id, $this->length, '0', STR_PAD_LEFT);

        return $id;
    }

}
