<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\helpers;

use Yii;

/**
 * Description of UploadHelper
 *
 * @author Yasser
 */
class MailHelper {

	public static function send($view, $data, $for, $subject) {
		$baseUrl = Yii::$app->urlManager->baseUrl;

        $mail = Yii::$app
                ->mailer
                ->compose(
                    ['html' => "$view-html", 'text' => "$view-text"],
                    $data
                )
                ->setFrom([Yii::$app->params['systemEmail'] => Yii::$app->name])
                ->setTo($for)
                ->setSubject($subject)
                ->send();

        Yii::$app->urlManager->setBaseUrl($baseUrl);
        return $mail;
	}

}