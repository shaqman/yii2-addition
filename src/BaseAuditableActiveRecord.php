<?php

namespace shaqman\addition;

use common\models\User;
use shaqman\addition\behavior\BlameableBehavior;
use shaqman\addition\traits\DropDownHelper;
use yii\behaviors\TimestampBehavior;

abstract class BaseAuditableActiveRecord extends BaseActiveRecord {

    use DropDownHelper;

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::className(),
            BlameableBehavior::className(),
        ];
    }

    public function attributeLabels() {
        return [
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function removeBaseAuditable() {
        $this->created_by = null;
        $this->updated_by = null;
        $this->created_at = null;
        $this->updated_by = null;
    }

    /**
     * @return User
     */
    public function getCreator() {
        return User::findOne($this->created_by);
    }

}
