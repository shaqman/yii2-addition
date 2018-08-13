<?php

namespace shaqman\addition;

use common\widgets\GrowlAlert;
use shaqman\addition\form\ActiveForm;
use Yii;
use yii\base\Event;
use yii\bootstrap\BootstrapAsset;
use yii\web\Controller;
use yii\web\View;
use yii\widgets\Pjax;

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

/**
 * Description of BaseSecureController
 *
 * @author syakur
 */
abstract class BaseController extends Controller {

    protected function filteredAssetBundles() {
        return [BootstrapAsset::class];
    }

    public function renderAjax($view, $params = array()) {
        $filteredBundles = $this->filteredAssetBundles();
        Event::on(View::className(), View::EVENT_AFTER_RENDER, function ($e) use($filteredBundles) {
            foreach ($filteredBundles as $bundle) {
                $e->sender->assetBundles[$bundle] = false;
            }
        });

        return parent::renderAjax($view, $params);
    }

    public function renderPjaxResult($content = 'Success', $useAlert = true) {
        ob_start();
        Pjax::begin();
        if ($useAlert) {
            echo ActiveForm::SUCCESS_TOKEN;
            Yii::$app->session->setFlash('info', $content);
            echo GrowlAlert::widget();
        } else {
            echo $content;
        }
        Pjax::end();

        $result = ob_get_contents();
        ob_end_clean();

        return $this->renderContent($result);
    }

}
