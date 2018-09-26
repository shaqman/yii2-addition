<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\mailer;

use Yii;
use yii\base\Exception;
use yii\swiftmailer\Message as SwiftMessage;

/**
 * Description of Message
 *
 * @author syakur
 *
 * @property mixed $from
 */
class Message extends SwiftMessage {

    public function getFrom() {
        if (count(parent::getFrom()) == 0) {
            if (empty(Yii::$app->params['email']['system'])) {
                throw new Exception("Parameter email['system'] is not found. "
                        . "Cannot set default from for email sending. "
                        . "Please check your configured params.");
            }

            $this->setFrom(Yii::$app->params['email']['system']);
        }

        return parent::getFrom();
    }

}
