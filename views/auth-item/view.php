<?php

use backend\components\DetailView;
use backend\grid\DateColumn;

echo $this->render('//layouts/view-header', compact('model'))
?>

<div class="col-md-6">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            'description',
            [
                'class' => DateColumn::className(),
                'format' => 'datetime',
                'attribute' => 'created_at',
            ],
            [
                'attribute'    => 'type',
                'value'         => $model->getTypes()[$model->type]
            ]
        ],
    ]) ?>
</div>