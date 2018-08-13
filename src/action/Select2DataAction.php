<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\action;

use shaqman\addition\helpers\Select2Helper;
use Yii;
use yii\base\Action;
use yii\db\QueryInterface;
use yii\di\Instance;
use yii\web\Response;

/**
 * Description of JsonDataAction
 *
 * @author syakur
 */
class Select2DataAction extends Action {

    public $pageSize = 25;
    public $idAttribute = "id";
    public $valueAttribute = "name";
    public $query;

    public function init() {
        parent::init();

        $this->query = Instance::ensure($this->query, QueryInterface::class);
    }

    public function run($q = null, $id = null, $page = 1) {
        $query = $this->query;
        if (!empty($q)) {
            $query = $query->andWhere(['like', $this->valueAttribute, explode(' ', $q)]);
        }

        $helper = new Select2Helper([
            'pageSize' => $this->pageSize,
            'idAttribute' => $this->idAttribute,
            'valueAttribute' => $this->valueAttribute,
            'query' => $query,
            'term' => $q
        ]);

        return Yii::createObject(['class' => Response::className(), 'format' => Response::FORMAT_JSON, 'data' => $helper->getData($page)]);
    }

}
