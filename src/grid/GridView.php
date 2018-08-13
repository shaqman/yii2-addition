<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\grid;

class GridView extends \kartik\grid\GridView {

    public $includeTableHeader = true;

    public function renderTableHeader() {
        if ($this->includeTableHeader) {
            return parent::renderTableHeader();
        }

        return '';
    }
}
