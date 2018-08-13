<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\helpers;

/**
 * Description of ArrayHelper
 *
 * @author syakur
 */
class ArrayHelper extends \yii\helpers\ArrayHelper {

    public static function last($arr, $keyOnly = false) {
        end($arr);
        if ($keyOnly) {
            return key($arr);
        }

        return current($arr);
    }

    /**
     * Gets an array based an insensitive search key
     * @param string $key the key to be retrieved
     * @param array $haystack array source
     * @return mixed the value or null if not found
     */
    public static function get($key, $haystack) {
        $haystack = array_change_key_case($haystack);
        if (!empty($haystack[strtolower($key)])) {
            return $haystack[strtolower($key)];
        }

        return null;
    }

    public static function getDuplicates($raw) {
        $dupes = array();
        natcasesort($raw);
        reset($raw);

        $old_key = NULL;
        $old_value = NULL;
        foreach ($raw as $key => $value) {
            if ($value === NULL) {
                continue;
            }
            if (strcasecmp($old_value, $value) === 0) {
                $dupes[$old_key] = $old_value;
                $dupes[$key] = $value;
            }
            $old_value = $value;
            $old_key = $key;
        }

        return $dupes;
    }

}
