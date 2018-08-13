<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\behavior;

/**
 * Description of BlameableBehavior
 *
 * @author syakur
 */
class BlameableBehavior extends \yii\behaviors\BlameableBehavior {

    protected function getValue($event) {
        if (\Yii::$app instanceof \yii\console\Application || \Yii::$app->user->isGuest) {
            return 0;
        }

        return parent::getValue($event);
    }

}
