<?php

/*
 * The MIT License
 *
 * Copyright 2017 syakur.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace shaqman\addition\widgets;

use kartik\growl\Growl;
use Yii;
use yii\helpers\Inflector;

/**
 * Description of GrowlAlert
 *
 * @author syakur
 */
class GrowlAlert extends Alert {

    public $growlTypes = [
        'error' => 'growl',
        'danger' => 'danger',
        'success' => 'success',
        'info' => 'info',
        'warning' => 'warning'
    ];
    public $growlIcons = [
        'error' => 'glyphicon glyphicon-remove-sign',
        'danger' => 'glyphicon glyphicon-remove-sign',
        'success' => 'glyphicon glyphicon-ok-sign',
        'info' => 'glyphicon glyphicon-info-sign',
        'warning' => 'glyphicon glyphicon-exclamation-sign'
    ];

    public function init() {
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();

        foreach ($flashes as $type => $data) {
            if (isset($this->alertTypes[$type])) {
                $data = (array) $data;
                foreach ($data as $i => $message) {
                    echo Growl::widget([
                        'type' => $this->growlTypes[$type],
                        'title' => Inflector::humanize($type),
                        'icon' => $this->growlIcons[$type],
                        'body' => $message,
                        'showSeparator' => true,
                        'delay' => 500,
                        'pluginOptions' => [
                            'timer' => 10000,
                            'allow_dismiss' => true,
                            'showProgressbar' => true,
                            'placement' => [
                                'from' => 'top',
                                'align' => 'right',
                            ]
                        ]
                    ]);
                }

                $session->removeFlash($type);
            }
        }
    }

}
