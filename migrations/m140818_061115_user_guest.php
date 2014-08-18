<?php

use yii\db\Schema;
use yii\db\Migration;
use bariew\rbacModule\models\AuthItem;
use bariew\rbacModule\models\AuthItemChild;

class m140818_061115_user_guest extends Migration
{
    public function getRoles()
    {
        return [
            AuthItem::ROLE_GUEST
        ];
    }

    public function up()
    {
        foreach ($this->getRoles() as $role) {
            $this->insert(AuthItem::tableName(), [
                'name'  => $role,
                'type'  => \yii\rbac\Item::TYPE_ROLE
            ]);
        }
        $this->insert(AuthItemChild::tableName(), [
            'parent'  => AuthItem::ROLE_ROOT,
            'child'   => AuthItem::ROLE_GUEST
        ]);
    }

    public function down()
    {
        AuthItemChild::deleteAll(['parent' => $this->getRoles()]);
        AuthItemChild::deleteAll(['child' => $this->getRoles()]);
        AuthItem::deleteAll(['name' => $this->getRoles()]);
        return true;
    }
}
