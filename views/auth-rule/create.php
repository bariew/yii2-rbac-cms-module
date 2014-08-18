<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model bariew\rbacModule\models\AuthRule */

$this->title = Yii::t('modules/rbac', 'Create {modelClass}', [
    'modelClass' => 'Auth Rule',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('modules/rbac', 'Auth Rules'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-rule-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
