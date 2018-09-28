<?php

namespace shaqman\addition;

use shaqman\addition\db\ActiveRecord;
use shaqman\addition\traits\DropDownHelper;

abstract class BaseActiveRecord extends ActiveRecord {

    use DropDownHelper;

    /** We assume this is the average seconds of a single page load.
     * That way the request should only survive for a single request.
     */
    const CACHE_DURATION = 3;

    protected static $perRequestCache = [];

    protected function getOrSetFromCache($key, $callable) {
        $out = isset(static::$perRequestCache[$key]) ? static::$perRequestCache[$key] : null;
        if ($out === null) {
            $out = is_callable($callable) ? call_user_func($callable, $this) : $callable;
            static::$perRequestCache[$key] = $out;
        }
        return $out;
    }

    public static function get($condition) {
        $model = static::find()->where($condition)->one();
        if ($model === null) {
            $model = new static();
        }

        return $model;
    }

    public function changedAttributes($attribute = null) {
        $changedAttributes = array_diff($this->dirtyAttributes, $this->attributes);
        if (is_string($attribute)) {
            return isset($changedAttributes[$attribute]);
        }
        // TODO: Make a chaeck if it is an array of attributes
        return $changedAttributes;
    }

}
