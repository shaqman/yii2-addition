<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\widgets;

use yii\helpers\Html;
use yii\widgets\DetailView as YiiDetailView;

/**
 * Description of DetailView
 *
 * @author syakur
 */
class DetailView extends YiiDetailView {

    public $emptyString = "No data found.";

    public function run() {
        if (is_array($this->model) && count($this->model) == 0) {
            $content = Html::tag('p', $this->emptyString, ['class' => 'text-muted bg-info']);
            echo Html::tag('div', $content, ['class' => 'col-md-12 col-sm-12 col-xs-12']);
        } else {
            parent::run();
        }
    }

}
