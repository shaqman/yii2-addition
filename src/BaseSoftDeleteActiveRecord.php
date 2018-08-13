<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition;

use yii\helpers\ArrayHelper;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * Description of BaseBlameableActiveRecord
 *
 * @author syakur
 */
abstract class BaseSoftDeleteActiveRecord extends BaseAuditableActiveRecord {

    const ROW_STATUS_ACTIVE = 0;
    const ROW_STATUS_DELETED = 1;

    public static function find($status = self::ROW_STATUS_ACTIVE) {
        $query = parent::find();
        if ($status === null) {
            return $query;
        }
        return $query->where([static::tableName() . '.row_status' => $status]);
    }

    public function rules() {
        return ArrayHelper::merge(parent::rules(), [[['row_status'], 'default', 'value' => static::ROW_STATUS_ACTIVE]]);
    }

    public function attributeLabels() {
        return ArrayHelper::merge(parent::attributeLabels(), ['row_status' => 'Row Status']);
    }

    public function behaviors() {
        return ArrayHelper::merge(parent::behaviors(), [
                    'softDeleteBehavior' => [
                        'class' => SoftDeleteBehavior::className(),
                        'softDeleteAttributeValues' => [
                            'row_status' => self::ROW_STATUS_DELETED
                        ],
                    ]
        ]);
    }

}
