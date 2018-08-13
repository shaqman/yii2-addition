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

use kartik\base\InputWidget;
use shaqman\addition\helpers\ArrayHelper;
use yii\base\Exception;
use yii\helpers\Html;

/**
 * Description of MultipleWidget
 *
 * @author syakur
 */
class MultipleWidget extends InputWidget {

    public $id;
    public $key;
    public $index;
    public $widgets;
    public $model;
    public $attribute;
    public $view;
    public $template = "<div>{input}</div>";

    public function init() {
        parent::init();

        if (empty($this->widgets) || !is_array($this->widgets) || count($this->widgets) == 0) {
            throw new Exception("Invalid configuration. Widgets must contain an array of widgets with it's configuration.");
        }

        $primaryWidgetFound = false;
        foreach ($this->widgets as $widget) {
            if (empty($widget['class']) && empty($widget['inputType'])) {
                throw new Exception("Invalid configuration. Either class or inputType must be set.");
            }
        }
    }

    private function decorateAttributeName($attribute) {
        return '[' . $this->key . ']' . $attribute;
    }

    public function run() {
        parent::run();

        foreach ($this->widgets as $widget) {
            if (!is_string($widget)) {
                if (!empty($widget['primaryWidget'])) {
                    // TODO: Evaluate a better way to determine when an attribute needs to be decorated
                    $widget['decorateAttribute'] = isset($widget['decorateAttribute']) ? $widget['decorateAttribute'] : true;
                    $widgetOptions = [
                        'model' => $this->model,
                        'attribute' => empty($widget['decorateAttribute']) ? $this->attribute : $this->decorateAttributeName($this->attribute),
                        'options' => [
                            'class' => isset($this->field->form->options['class']) && $this->field->form->options['class'] == 'kv-editable-form' ? 'kv-editable-input' : ''
                        ]
                    ];
                    if (isset($widget['inputType'])) {
                        $widget = ArrayHelper::merge($widget, $widgetOptions);
                    } else {
                        $widget['options'] = ArrayHelper::merge(empty($widget['options']) ? [] : $widget['options'], $widgetOptions);
                    }
                } else {
                    if (!empty($widget['name'])) {
                        $widget['options'] = ArrayHelper::merge(empty($widget['options']) ? [] : $widget['options'], [
                                    'name' => $widget['name'],
                        ]);
                    }
                }

                if (isset($widget['class'])) {
                    $class = $widget['class'];
                    $input = $class::widget($widget['options']);
                } elseif (isset($widget['inputType'])) {
                    $inputType = isset($widget['attribute']) ? 'active' . ucfirst($widget['inputType']) : $widget['inputType'];
                    $value = empty($widget['value']) ? null : $widget['value'];
                    $options = ArrayHelper::merge(["class" => "form-control"], (empty($widget['options']) ? [] : $widget['options']));
                    if (isset($widget['attribute'])) {
                        $widget['attribute'] = $this->decorateAttributeName($widget['attribute']);
                        $input = Html::$inputType($this->model, $widget['attribute'], $options);
                    } else {
                        unset($options['model']);
                        unset($options['attribute']);
                        $input = Html::$inputType($widget['name'], $value, $options);
                    }
                }
            } else {
                $input = $widget;
            }

            echo str_replace('{input}', $input, $this->template);
        }
    }

}
