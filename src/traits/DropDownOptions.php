<?php

namespace shaqman\addition\traits;

use shaqman\addition\helpers\StringHelper;
use yii\helpers\ArrayHelper;

trait DropDownOptions {

    protected $modelNamespace = "common\\models\\options\\";

    protected function prepareMapArray($arr) {
        $options = array();
        foreach ($arr as $value) {
            $options[] = ["id" => $value, "name" => $value];
        }

        return $options;
    }

    protected function getOptions($array, $prepareArrayFormat = true) {
        return ArrayHelper::map(($prepareArrayFormat ? $this->prepareMapArray($array) : $array), "id", "name");
    }

    protected function decamelize($input) {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    public function __call($name, $params) {
        if (strstr($name, 'get') !== false && strstr($name, 'Options') !== false) {
            $currentAttribute = str_replace("ForJson", "", str_replace("Options", "", str_replace("get", "", $name)));
            $methodName = StringHelper::lcfirst($currentAttribute) . 'Options';
            if (method_exists($this, $methodName)) {
                if (strstr($name, 'ForJson') !== false) {
                    return $this->prepareMapArray($this->$methodName());
                }
                return $this->getOptions($this->$methodName());
            }
            if (array_key_exists($this->decamelize($currentAttribute) . "_id", $this->attributes) ||
                    array_key_exists(StringHelper::lcfirst($currentAttribute) . "Id", $this->attributes)) {
                $currentAttribute = $this->modelNamespace . $currentAttribute;
                return $this->getOptions($currentAttribute::find()->all(), false);
            }
            if (!in_array(StringHelper::lcfirst($currentAttribute), $this->attributes())) {
                return [];
            }
        }

        return parent::__call($name, $params);
    }

}
