<?php

namespace shaqman\addition\traits;

trait PrettifiedDate {

    public function __get($name) {
        if (strstr($name, "_ui_format") !== false) {
            $name = str_replace("_ui_format", "", $name);
            if ($this->$name !== null) {
                $date = $this->$name;
                try {
                    $date = is_int($date) ? date(\Yii::$app->params['genericDateFormat'], $date) : $date;
                    return (new \DateTime($date))->format(\Yii::$app->params['htmlControlDateFormatPhp']);
                } catch (Exception $ex) {

                }
            }
        }

        return parent::__get($name);
    }

}
