<?php

namespace shaqman\addition\db;

use Yii;

/**
 * Description of ActiveRecord
 *
 * @author admin
 */

/** TODO: Remove this class and consolidate it's member to BaseActiveRecord */
class ActiveRecord extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find() {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

    public function setIsNewRecord($value) {
        parent::setIsNewRecord($value);

        $attributesToBeCleared = ['id', 'row_status', 'created_by', 'created_at', 'updated_by', 'updated_at'];
        foreach ($attributesToBeCleared as $currentAttribute) {
            if ($this->hasAttribute($currentAttribute)) {
                $this->setAttribute($currentAttribute, null);
            }
        }
    }

}
