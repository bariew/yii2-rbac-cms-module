<?php

use backend\components\ActiveForm;

/**
 * @var yii\web\View $this
 * @var bariew\rbacModule\models\AuthAssignment $model
 * @var yii\widgets\ActiveForm $form
 */

echo $this->render('//layouts/form-header', ['model' => $model]);

$form = ActiveForm::begin();

echo $form->field($model, 'item_name')->relationInput();
echo $form->field($model, 'user_id')->relationInput();
?>
<div class="form-group">
    <?= \yii\helpers\Html::submitButton($model->isNewRecord ? Yii::t('modules/rbac', 'create') : Yii::t('modules/rbac', 'update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end(); ?>

