<?php

use bariew\rbacModule\widgets\AuthItemMenu;
use yii\jui\Tabs;

/**
 * @var yii\web\View                      $this
 * @var bariew\rbacModule\models\AuthItem $model
 * @var array                             $tabs
 */
?>
<div class="row">
    <div class="col-md-3 well">
        <?= AuthItemMenu::widget(['model' => $model]); ?>
    </div>
    <div class="col-md-9">
        <?php
        $tabItems = [
            'settings' => [
                'label'   => Yii::t('modules/rbac', 'Settings'),
                'content' => $this->render('_form', compact('model'))
            ]
        ];
        if (!$model->getIsNewRecord()) {
            $tabItems = array_merge($tabItems, [
                'users'       => [
                    'label'   => Yii::t('modules/rbac', 'Users'),
                    'url'     => ['auth-assignment/role-users', 'name' => $model->name],
                    'visible' => false
                ],
                'permissions' => [
                    'label'   => Yii::t('modules/rbac', 'Permissions'),
                    'url'     => ['auth-item-child/tree', 'name' => $model->name],
                    'visible' => false
                ]
            ]);
        }
        echo Tabs::widget([
            'items'         => array_intersect_key($tabItems, array_flip($tabs)),
            'options'       => ['tag' => 'div'],
            'itemOptions'   => ['tag' => 'div'],
            'headerOptions' => ['class' => 'my-class'],
            'clientOptions' => ['collapsible' => false],
        ]); ?>
    </div>
</div>
