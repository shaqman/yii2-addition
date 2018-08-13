<?php

namespace shaqman\addition\widgets;

use yii\helpers\Html;

class DatePicker extends \kartik\date\DatePicker {

    protected function renderInput() {
        return parent::renderInput() . Html::style(".datepicker { z-index: 11510 !important; }");
    }

}
