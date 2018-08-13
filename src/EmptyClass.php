<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition;

/**
 * Description of EmptyClass
 *
 * @author syakur
 */
class EmptyClass {

    public static $instance = null;

    public function __call($name, $params) {
        return EmptyClass::getInstance();
    }

    public static function getInstance() {
        if (static::$instance === null) {
            EmptyClass::$instance = new EmptyClass();
        }

        return static::$instance;
    }

    public function __toString() {
        return '';
    }

}
