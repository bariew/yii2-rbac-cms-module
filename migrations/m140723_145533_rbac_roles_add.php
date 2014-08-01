<?php

use \bariew\rbacModule\models\AuthItem;
use \bariew\rbacModule\models\AuthItemChild;
use yii\db\Migration;

class m140723_145533_rbac_roles_add extends Migration
{
    public $roles = ['root', 'default'];

    public function up()
    {
        foreach ($this->roles as $role) {
            $this->insert(AuthItem::tableName(), [
                    'name'  => $role,
                    'type'  => 1
                ]);
        }
        $this->insert(AuthItemChild::tableName(), [
                'parent'  => $this->roles[0],
                'child'   => $this->roles[1]
            ]);
    }

    public function down()
    {
        AuthItemChild::deleteAll(['parent' => $this->roles]);
        AuthItemChild::deleteAll(['child' => $this->roles]);
        AuthItem::deleteAll(['name' => $this->roles]);
        return true;
    }
}
