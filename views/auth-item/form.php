<?php

    echo \yii\jui\Tabs::widget([
        'items' => [
            [
                'label' => 'Settings',
                'content' => $this->render('_form', compact('model')),
            ],
            [
                'label' => 'Users',
                'url' => ['auth-assignment/role-users', 'name'=>$model->name],

            ],
        ],
        'options' => ['tag' => 'div'],
        'itemOptions' => ['tag' => 'div'],
        'headerOptions' => ['class' => 'my-class'],
        'clientOptions' => ['collapsible' => false],
    ]);