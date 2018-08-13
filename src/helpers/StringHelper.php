<?php

namespace shaqman\addition\helpers;

class StringHelper extends \yii\helpers\StringHelper {

    static function lcfirst($str) {
        return strtolower(substr($str, 0, 1)) . substr($str, 1);
    }

}
