<?php

namespace shaqman\addition\form;

use kartik\form\ActiveField as kartikActiveField;
use shaqman\addition\EmptyClass;
use shaqman\addition\helpers\ArrayHelper;
use Yii;
use yii\helpers\Html;
use yii\web\View;

class ActiveField extends kartikActiveField {

    private $withContainer = true;

    public function withContainer($withContainer = true) {
        $this->withContainer = $withContainer;
        return $this;
    }

    public function isIgnoredAttribute() {
        $attribute = strstr($this->attribute, '[') !== false ? substr($this->attribute, 0, strpos($this->attribute, '[')) : $this->attribute;
        if (!$this->model->hasProperty($attribute) && !in_array($attribute, $this->model->attributes())) {
            Yii::trace("Attribute $attribute not found in model {$this->model->className()}. Returning empty control");
            return EmptyClass::getInstance();
        }

        return false;
    }

    public function render($content = null) {
        if ($this->isIgnoredAttribute() !== false) {
            Yii::trace("Rendering as empty class since $this->attribute is being ignored.");
            return '';
        }

        return parent::render($content);
    }

    public function widget($class, $config = array()) {
        if ($this->isIgnoredAttribute() !== false) {
            return $this->isIgnoredAttribute();
        }

        return parent::widget($class, $config);
    }

    public function dropDownList($items, $options = array()) {
        if ($this->isIgnoredAttribute() !== false) {
            return $this->isIgnoredAttribute();
        }

        $options = ArrayHelper::merge(['prompt' => 'Select...'], $options);

        return parent::dropDownList($items, $options);
    }

    public function begin() {
        if ($this->withContainer) {
            return parent::begin();
        }
        return "";
    }

    public function end() {
        if ($this->withContainer) {
            return parent::end();
        }
        return "";
    }

    protected function generateAddon() {
        if ($this->withContainer) {
            return parent::generateAddon();
        }

        // Modified from parent \kartik\form\ActiveField
        // This should have a better way of overriding from child
        if (empty($this->addon)) {
            return '{input}';
        }

        $addon = $this->addon;
        $prepend = static::getAddonContent(ArrayHelper::getValue($addon, 'prepend', ''));
        $append = static::getAddonContent(ArrayHelper::getValue($addon, 'append', ''));
        $content = $prepend . '{input}' . $append;
        $contentBefore = ArrayHelper::getValue($addon, 'contentBefore', '');
        $contentAfter = ArrayHelper::getValue($addon, 'contentAfter', '');
        $content = $contentBefore . $content . $contentAfter;
        return $content;
    }

    public function input($type, $options = array()) {
        if ($this->isIgnoredAttribute() !== false) {
            return $this->isIgnoredAttribute();
        }

        return parent::input($type, $options);
    }

    public function textInput($options = array()) {
        if ($this->form->vue) {
            if (ArrayHelper::getValue($options, ':value') === null) {
                $options = ArrayHelper::merge($options, ['v-model' => "{$this->model->formName()}.$this->attribute"]);
            }
        }

        return parent::textInput($options);
    }

    public function label($label = null, $options = array()) {
        if ($this->isIgnoredAttribute() !== false) {
            return $this->isIgnoredAttribute();
        }

        return parent::label($label, $options);
    }

    private function prepareOtherOptions(&$options) {
        $includeOther = isset($options['includeOther']) && $options['includeOther'] === true;
        $otherOptions = [];
        if ($includeOther) {
            $otherOptions['otherAttribute'] = $options['otherAttribute'];
            $otherOptions['otherText'] = $options['otherText'];
            $otherOptions['selector'] = "check-button-other-" . $options['otherAttribute'];

            $options += ["class" => $otherOptions['selector']];

            unset($options['includeOther']);
            unset($options['otherAttribute']);
            unset($options['otherText']);
        }

        return $otherOptions;
    }

    public function radioButtonGroup($items, $options = array()) {
        if ($this->isIgnoredAttribute() !== false) {
            return $this->isIgnoredAttribute();
        }

        $otherOptions = $this->prepareOtherOptions($options);
        $result = parent::radioButtonGroup($items, $options);
        if (count($otherOptions) > 0) {
            Yii::$app->view->on(View::EVENT_END_PAGE, function ($event) {
                // TODO make this as a generic script
                $selector = str_replace("-", "_", $event->data['options']['selector']);
                $script = <<<EOT
                    var div{$selector} = $(".{$event->data['options']['otherAttribute']}-other");
                    $(document).ready(function () {
                        $($(".{$event->data['options']['selector']}").children(".btn-success").each(function () {
                            if ($(this).html().indexOf('{$event->data['options']['otherText']}') >= 0) {
                                $(this).click(function () {
                                    div{$selector}.fadeIn();
                                });
                            } else {
                                $(this).click(function () {
                                    div{$selector}.fadeOut();
                                });
                            }
                        }));
                    });
EOT;
                echo Html::script($script);
            }, ["options" => $otherOptions]);


            $otherAttribute = $otherOptions['otherAttribute'];
            $result .= $this->form->field($this->model, $otherOptions['otherAttribute'], ["options" => [
                            "class" => $otherOptions['otherAttribute'] . "-other",
                            "style" => "display: " . ($this->model->$otherAttribute == $otherOptions['otherText'] ? "initial" : "none")
                ]])->textInput();
        }

        return $result;
    }

}
