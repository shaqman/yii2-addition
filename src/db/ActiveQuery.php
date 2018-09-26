<?php

namespace shaqman\addition\db;

use yii\db\Expression;

/**
 * Description of ActiveQuery
 *
 * @author admin
 * @see ActiveRecord
 */
class ActiveQuery extends \yii\db\ActiveQuery {

    /**
     * @inheritdoc
     *
     * @param string|array|Expression $condition the conditions that should be put in the WHERE part.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @return $this the query object itself
     */
    public function where($condition, $params = [], $useParent = false) {
        if ($useParent) {
            return parent::where($condition, $params);
        }

        return $this->andWhere($condition, $params);
    }

}
