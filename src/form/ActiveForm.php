<?php

namespace shaqman\addition\form;

use aki\vue\AxiosAsset;
use aki\vue\Vue;
use kartik\dialog\Dialog;
use kartik\form\ActiveForm as kartikActiveForm;
use shaqman\addition\helpers\ArrayHelper;
use shaqman\addition\helpers\UrlHelper;
use shaqman\widgets\inlinescript\InlineScript;
use Yii;
use yii\base\InvalidCallException;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\web\JsExpression;

/*
 * @method common\components\form\ActiveField field(\yii\base\Model $model, string $attribute, array $options = [])
 *
 */

class ActiveForm extends kartikActiveForm {

    /** @const string This is used to identify whether the dialog should automatically be closed. */
    const SUCCESS_TOKEN = '##31337##';

    public $fieldClass = ActiveField::class;
    public $vue = false;

    /** @var Vue */
    private $vueHolder = null;
    private $modelList = [];

    public function run() {
        if (!empty($this->_fields)) {
            throw new InvalidCallException('Each beginField() should have a matching endField() call.');
        }

        $content = ob_get_clean();
        if ($this->vue) {
            $this->vueHolder = Vue::begin([
                        'id' => $this->id . "-vue-app",
                        'jsName' => Inflector::id2camel($this->id . "-vue-app"),
                        'data' => $this->modelList,
            ]);
        }
        echo Html::beginForm($this->action, $this->method, $this->options);
        echo $content;

        if ($this->enableClientScript) {
            $this->registerClientScript();
        }

        echo Html::endForm();
        if ($this->vue) {
            $this->vueHolder->end();
        }
    }

    private function bootstrapFixConfig() {
        // Style adjustments for bootstrap.
        // Original style makes it inline on a single line.
        // This adjustments below makes it inline but full,
        // hence making it seems like its two lines
        return [
            'labelOptions' => [
                'class' => 'control-label col-md-12',
                'style' => 'padding:0'
            ],
        ];
    }

    private function prepareDialog($dialog, $attribute, $route) {
        if ($dialog instanceof Dialog) {
            echo $dialog;
        } else {
            AxiosAsset::register($this->view);

            $libName = $attribute . 'Dialog';

            InlineScript::begin(['key' => 'af-global-script']);
            ?>

            <script>
                axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

                jQuery(document).on('pjax:timeout', function (event) {
                    event.preventDefault();
                });
            </script>

            <?php
            InlineScript::end();
            InlineScript::begin(['key' => $attribute . '-script']);
            ?>

            <script>
                showDialog<?= Inflector::camelize($attribute) ?> = function () {
            <?= $libName ?>.dialog('<span class="bootstrap-dialog-button-icon glyphicon glyphicon-asterisk icon-spin"></span> Loading...', function () {});
                };

                afResetButtonCondition<?= Inflector::camelize($attribute) ?> = function (event) {
                    dialog = event.data.dialog;
                    cancelButton = dialog.getButton('<?= $attribute ?>-cancel-btn');
                    cancelButton.enable();
                    cancelButton.stopSpin();

                    saveButton = dialog.getButton('<?= $attribute ?>-save-btn');
                    saveButton.enable();
                    saveButton.stopSpin();
                    dialog.setClosable(true);

                    jQuery(document).off('pjax:error', {dialog: dialog}, afResetButtonCondition<?= Inflector::camelize($attribute) ?>);
                };

                afSuccessButtonCondition<?= Inflector::camelize($attribute) ?> = function (event, data) {
                    dialog = event.data.dialog;
                    if (data.includes('<?= self::SUCCESS_TOKEN ?>')) {
                        dialog.close();
                    } else {
                        afResetButtonCondition<?= Inflector::camelize($attribute) ?>(event);
                    }
                    jQuery(document).off('pjax:success', {dialog: dialog}, afSuccessButtonCondition<?= Inflector::camelize($attribute) ?>);
                };

            </script>

            <?php
            InlineScript::end();

            echo Dialog::widget([
                'libName' => $libName,
                'overrideYiiConfirm' => false,
                'options' => [// customized BootstrapDialog options
                    'size' => Dialog::SIZE_WIDE,
                    'type' => Dialog::TYPE_DEFAULT, // bootstrap contextual color
                    'title' => Inflector::humanize($attribute . ' form'),
                    'closable' => true,
                    'onshow' => new JsExpression("function(dialog){"
                            . "axios.get('" . Yii::$app->urlManager->createUrl($route) . "').then("
                            . "function (response) {dialog.\$modalBody.html(response.data);}"
                            . ");"
                            . "}"),
                    'buttons' => [
                        [
                            'id' => $attribute . '-cancel-btn',
                            'label' => 'Cancel',
                            'action' => new JsExpression("function(dialog) { dialog.close(); }")
                        ],
                        [
                            'id' => $attribute . '-save-btn',
                            'label' => 'Save',
                            'action' => new JsExpression("function(dialog) {
                    this.disable();
                    dialog.getButton('$attribute-cancel-btn').disable();
                    this.spin();
                    dialog.setClosable(false);
                    dialog.getModalBody().find('form').submit()
                    jQuery(document).on('pjax:error', { dialog: dialog }, afResetButtonCondition" . Inflector::camelize($attribute) . ");
                    jQuery(document).on('pjax:success', { dialog: dialog }, afSuccessButtonCondition" . Inflector::camelize($attribute) . ");
                }")
                        ],
                    ]
                ]
            ]);
        }
    }

    public function field($model, $attribute, $options = []) {
        $this->modelList[$model->formName()] = $model->getAttributes();

        if ($options === true) {
            trigger_error("Options as boolean is deprecated. Consider refactoring the code to use options['bootstrap_fix'=>true] instead.", E_USER_DEPRECATED);
            $options = $this->bootstrapFixConfig();
        }

        if (is_array($options)) {
            if (!empty($options['bootstrap_fix'])) {
                $options = ArrayHelper::merge($options, $this->bootstrapFixConfig());
                unset($options['bootstrap_fix']);
            }
            if (!empty($options['includeCreateAddon'])) {
                $createAddonOptions = $options['createAddonOptions'];

                $title = empty($createAddonOptions['title']) ? 'Add' : $createAddonOptions['title'];
                $route = empty($createAddonOptions['route']) ? ['create'] : $createAddonOptions['route'];
                $iconClass = empty($createAddonOptions['iconClass']) ? 'glyphicon glyphicon-plus' : $createAddonOptions['iconClass'];
                $buttonClass = empty($createAddonOptions['buttonClass']) ? 'btn btn-default' : $createAddonOptions['buttonClass'];
                $dialog = empty($createAddonOptions['dialog']) ? false : $createAddonOptions['dialog'];

                $route = ArrayHelper::merge($route, ['return' => UrlHelper::getCurrentUrl()]);

                if ($dialog) {
                    $this->prepareDialog($dialog, $attribute, $route);
                }

                $addonArray = [
                    'content' => $dialog ? Html::a("<i class='$iconClass' onclick=''></i>", null, ['class' => $buttonClass, 'title' => $title, 'onclick' => 'showDialog' . Inflector::camelize($attribute) . '()']) : Html::a("<i class='$iconClass' onclick=''></i>", $route, ['class' => $buttonClass, 'title' => $title]),
                    'asButton' => true
                ];

                $options = ArrayHelper::merge($options, ['addon' => ['append' => [$addonArray]]]);
                unset($options['createAddonOptions']);
                unset($options['includeCreateAddon']);
            }
        }

        return parent::field($model, $attribute, $options);
    }

}
