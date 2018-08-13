<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\helpers;

use yii\base\BaseObject;
use yii\data\ActiveDataProvider;
use yii\db\QueryInterface;
use yii\di\Instance;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * Description of Select2Helper
 *
 * @author syakur
 */
class Select2Helper extends BaseObject {

    const DATA_ARRAY = 1;
    const DATA_JSON = 2;
    const DATA_DROPDOWN = 3;

    public $pageSize = 25;
    public $idAttribute = "id";
    public $valueAttribute = "name";
    public $groupAttribute;
    public $query;
    public $term;

    public function init() {
        parent::init();

        $this->query = Instance::ensure($this->query, QueryInterface::class);
    }

    public static function instance($config = []) {
        return new Select2Helper($config);
    }

    public static function getAjaxConfig($route) {
        return [
            'url' => $route instanceof JsExpression ? $route : Url::to($route),
            'dataType' => 'json',
            'delay' => 250,
            'cache' => true
        ];
    }

    public function getData($page = 1, $format = self::DATA_ARRAY) {
        $valueAttribute = $this->valueAttribute;

        $dataProvider = new ActiveDataProvider([
            'query' => strstr($valueAttribute, '.') === false ? $this->query->select([$this->idAttribute, $valueAttribute]) : $this->query,
            'pagination' => [
                'pageSize' => $this->pageSize,
            ],
        ]);

        $dataProvider->getPagination()->setPage($page - 1);
        $totalData = $dataProvider->getTotalCount();
        $valueArray = array_values($dataProvider->getModels());

        if ($format == self::DATA_DROPDOWN) {
            return ArrayHelper::map($dataProvider->getModels(), $this->idAttribute, $this->valueAttribute, $this->groupAttribute);
        }

        $out = empty($valueArray) ? ['results' => [['id' => '-1', 'text' => $this->term]]] : ['results' => array_map(function($arr) {
                        if (strstr($this->valueAttribute, '.') !== false) {
                            $tmp = explode('.', $this->valueAttribute);
                            $relation = $tmp[0];
                            $attribute = $tmp[1];
                            $value = $arr->$relation->$attribute;
                        } else {
                            $value = $arr[$this->valueAttribute];
                        }

                        if (strstr($this->idAttribute, '.') !== false) {
                            $tmp = explode('.', $this->idAttribute);
                            $relation = $tmp[0];
                            $attribute = $tmp[1];
                            $id = $arr->$relation->$attribute;
                        }
                        return [
                            'id' => empty($id) ? $arr['id'] : $id,
                            'text' => empty($value) ? $arr['text'] : $value
                        ];
                    }, $valueArray)];

        if ($totalData > (($this->pageSize * $page) + $this->pageSize)) {
            $out = ArrayHelper::merge($out, ['pagination' => ['more' => true]]);
        }

        if ($format == self::DATA_JSON) {
            $out = Json::encode($out);
        }

        return $out;
    }

}
