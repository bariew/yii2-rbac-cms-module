<?php

use yii\bootstrap\ActiveForm;
use \yii\jui\AutoComplete;
use \yii\helpers\Html;
/**
 * @var yii\web\View $this
 * @var bariew\rbacModule\models\AuthItem $model
 * @var yii\widgets\ActiveForm $form
 */

    $form = ActiveForm::begin();

    if ($model->type == 1) {
        echo $form->field($model, 'name')->textInput();
    } else if ($model->isNewRecord) {

        echo Html::activeLabel($model, 'name')
        . '<br />'
        . AutoComplete::widget([
              'model' => $model,
              'attribute' => 'name',
              'clientOptions' => [
                  'source' => array_values($model::permissionList()),
              ],
        ]);
    }

    echo $form->field($model, 'description')->textarea(['rows' => 6]);

?>
<div class="form-group">
    <?= \yii\helpers\Html::submitButton($model->isNewRecord ? Yii::t('modules/rbac', 'create') : Yii::t('modules/rbac', 'update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end(); ?>
