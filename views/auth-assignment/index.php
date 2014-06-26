<?php

use backend\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var bariew\rbacModule\models\search\AuthAssignmentSearch $searchModel
 */

echo $this->render('//layouts/index-header');
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'columns'      => [
        [
            'class' => \backend\grid\RelationColumn::className(),
            'relation'  => 'manager',
            'attribute' => 'user_id',
        ],
        [
            'class' => \backend\grid\RelationColumn::className(),
            'attribute' => 'item_name',
        ],
        [
            'class' => \backend\grid\DateColumn::className(),
            'format' => 'datetime',
            'attribute' => 'created_at',
        ],
    ],
]);
