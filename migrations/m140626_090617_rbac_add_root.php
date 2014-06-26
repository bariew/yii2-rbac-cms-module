<?php

use yii\db\Schema;
use \bariew\rbacModule\models\AuthItem;

class m140626_090617_rbac_add_root extends \yii\db\Migration
{
    public function up()
    {
        $this->insert(AuthItem::tableName(), [
            'name'  => 'root',
            'type'  => 1
        ]);
    }

    public function down()
    {
        return AuthItem::deleteAll('name="root"');
    }
}
