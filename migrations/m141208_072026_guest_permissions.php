<?php

use yii\db\Schema;
use yii\db\Migration;
use \bariew\rbacModule\models\AuthItem;
use bariew\rbacModule\models\AuthItemChild;

class m141208_072026_guest_permissions extends Migration
{
    private function getPermissions()
    {
        return [
            AuthItem::ROLE_GUEST => [
                'app/site/index',
                'app/site/error',
                'user/default/login',
                'debug/default/toolbar',
            ],
            AuthItem::ROLE_DEFAULT => [
                'user/default/logout',
            ],
        ];
    }

    public function up()
    {
        foreach ($this->getPermissions() as $role => $permissions) {
            foreach ($permissions as $permission) {
                $this->insert(AuthItem::tableName(), [
                    'name'  => $permission,
                    'type'  => \yii\rbac\Item::TYPE_PERMISSION
                ]);
                $this->insert(AuthItemChild::tableName(), [
                    'parent'  => AuthItem::ROLE_GUEST,
                    'child'   => $permission
                ]);
            }
        }
        return true;
    }

    public function down()
    {
        foreach ($this->getPermissions() as $role => $permissions) {
            AuthItemChild::deleteAll(['parent' => $role, 'child' => $permissions]);
            AuthItem::deleteAll(['name' => $permissions]);
        }

        return true;
    }
}
