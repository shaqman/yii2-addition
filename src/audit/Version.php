<?php

namespace shaqman\addition\audit;

use shaqman\addition\audit\models\AuditTrail;

class Version extends \bedezign\yii2\audit\components\Version {

    public static function versions($class, $id, $onlyChanged = true) {
        /** @var AuditTrail[] $trails */
        $trails = AuditTrail::find()
                ->andWhere(['model' => $class, 'model_id' => $id])
                ->orderBy(['entry_id' => SORT_ASC, 'id' => SORT_ASC])
                ->all();

        if ($onlyChanged) {
            $versions = [];
            foreach ($trails as $trail) {
                if ($trail->action == 'DELETE') {
                    $versions[$trail->entry_id][$trail->action] = $trail->action;
                } else {
                    $versions[$trail->entry_id][$trail->field] = $trail->new_value;
                }
            }
        }
        return $onlyChanged ? $versions : $trails;
    }

}
