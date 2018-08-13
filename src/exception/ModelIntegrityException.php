<?php

namespace shaqman\addition\exception;

use yii\base\Model;
use yii\db\IntegrityException;

/**
* 
*/
class ModelIntegrityException extends IntegrityException
{
	
	private $model;

	public function __construct($message, Model $model, $code = 0, \Exception $previous = null)
    {
    	$this->model = $model;
        parent::__construct($message, [], $code, $previous);
    }

    public function getModel()
    {
    	return $this->model;
    }

}