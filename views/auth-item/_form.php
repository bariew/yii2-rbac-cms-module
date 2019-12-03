<?php

use bariew\rbacModule\models\AuthItem;
use bariew\rbacModule\models\AuthRule;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\jui\AutoComplete;
use yii\rbac\Item;

/**
 * @var yii\web\View           $this
 * @var AuthItem               $model
 * @var yii\widgets\ActiveForm $form
 */

$form = ActiveForm::begin();
if ($model->type == Item::TYPE_ROLE) {
    echo $form->field($model, 'name')->textInput();
} else {
    if ($model->isNewRecord) {
        echo
            Html::activeLabel($model, 'name')
            . '<br />'
            . AutoComplete::widget([
                'model'         => $model,
                'attribute'     => 'name',
                'clientOptions' => [
                    'source'    => array_values($model::permissionList()),
                    'delay'     => 0,
                    'autoFocus' => true,
                    'minLength' => 0
                ],
            ]);
    }
}
if ($model->type == Item::TYPE_PERMISSION) {
    echo $form->field($model, 'rule_name')->dropDownList(AuthRule::listAll(), ['prompt' => false]);
}
echo $form->field($model, 'description')->textarea(['rows' => 6]);
echo Html::tag(
    "div",
    Html::submitButton(
        $model->getIsNewRecord()
            ? Yii::t('modules/rbac', 'Create')
            : Yii::t('modules/rbac', 'Update'),
        ['class' => $model->getIsNewRecord() ? 'btn btn-success' : 'btn btn-primary']
    ),
    ["class" => "form-group"]
);
ActiveForm::end();
