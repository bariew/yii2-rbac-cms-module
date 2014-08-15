<div class="row">
    <div class="col-md-3 well">
        <?= \Yii::$app->controller->menu; ?>
    </div>
    <div class="col-md-9">
    <?php
    $items = [[
        'label' => 'Settings',
        'content' => $this->render('_form', compact('model')),
    ]];
    if (!$model->isNewRecord) {
        $items[] = [
            'label' => 'Users',
            'url' => ['auth-assignment/role-users', 'name'=>$model->name],
            'visible'   => false
        ];
    }

    echo \yii\jui\Tabs::widget([
        'items' => $items,
        'options' => ['tag' => 'div'],
        'itemOptions' => ['tag' => 'div'],
        'headerOptions' => ['class' => 'my-class'],
        'clientOptions' => ['collapsible' => false],
    ]); ?>
    </div>
</div>
