<?php
use yii\helpers\Html;
use yii\grid\GridView;
/* @var $this yii\web\View */
/* @var $searchModel bariew\rbacModule\models\search\AuthRuleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = Yii::t('modules/rbac', 'Auth Rules');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-rule-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?= Html::a(Yii::t('modules/rbac', 'Create {modelClass}', [
                    'modelClass' => 'Auth Rule',
                ]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'name',
                'data:ntext',
                'created_at',
                'updated_at',
                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
</div>
