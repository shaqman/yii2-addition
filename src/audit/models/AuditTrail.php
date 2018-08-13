<?php

namespace shaqman\addition\audit\models;

use common\models\User;
use Yii;

class AuditTrail extends \bedezign\yii2\audit\models\AuditTrail {

    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

}
