<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\helpers;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Description of UrlHelper
 *
 * @author syakur
 */
class UrlHelper {

    public static function getCurrentUrl() {
        return urlencode(Yii::$app->controller->route . '|' . Yii::$app->request->queryString);
    }

    public static function parse($encodedUrl) {
        $url = explode('|', urldecode($encodedUrl));
        $return = [$url[0]];
        $params = $url[1];
        if (strstr($params, '&') === false) {
            $return = ArrayHelper::merge($return, static::queryToArray($params));
        } else {
            $params = explode('&', $params);
            foreach ($params as $currentParam) {
                $return = ArrayHelper::merge($return, static::queryToArray($currentParam));
            }
        }

        return $return;
    }

    private static function queryToArray($arr) {
        $out = [];
        if(strstr($arr,'=') !== false) {
            $current = explode('=', $arr);
            $out = [$current[0] => $current[1]];
        }

        return $out;
    }

}
