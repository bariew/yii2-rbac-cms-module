<?php

namespace bariew\rbacModule\widgets;

use bariew\rbacModule\models\AuthItem;
use yii\base\Widget;

class AuthItemMenu extends Widget
{
    /**
     * @var \bariew\rbacModule\models\AuthItem
     */
    public $model;

    /**
     * @inheritDoc
     */
    public function run()
    {
        $model = $this->model ?: AuthItem::findOne(['name' => AuthItem::ROLE_ROOT]);

        return $model->menuWidget([], 'menuCallback');
    }
}