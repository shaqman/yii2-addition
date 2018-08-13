<?php

namespace shaqman\addition\traits;

use yii\helpers\ArrayHelper;

trait DropDownHelper {

    /**
     *
     * @param mixed $where Default null.
     * @param String $value Default id.
     * @param String $label Default name.
     * @param String $group Default null.
     * @param mixed $orderBy Default null.
     * @return []
     */
    public static function toDropDownData($where = null, $value = 'id', $label = 'name', $group = null, $orderBy = null) {
        $model = $where instanceof \yii\db\QueryInterface ? $where : static::find();

        if (strstr($label, '.') !== FALSE) {
            $relationName = explode('.', $label)[0];
            $model->joinWith($relationName);
        }

        if ($where && !($where instanceof \yii\db\QueryInterface)) {
            $model->andWhere($where);
        }

        if ($orderBy) {
            $model->orderBy($orderBy);
        }
        return ArrayHelper::map($model->all(), $value, $label, $group);
    }

}
