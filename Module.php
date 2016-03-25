<?php
/**
 * Module class file.
 * @copyright (c) 2014, Bariew
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\rbacModule;

use bariew\rbacModule\models\AuthAssignment;
use bariew\rbacModule\models\AuthItem;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\db\ActiveRecord;

/**
 * Module for access (roles, permissions, ip-access etc) application functionality.
 * 
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class Module extends \yii\base\Module
{
    public $dbConnection;
    public $params = [
        'menu'  => [
            'label' => 'Settings',
            'items' => [[
                'label'    => 'Auth',
                'items' => [
                    ['label' => 'Roles & Permissions', 'url' => ['/rbac/auth-item/index']],
                    ['label' => 'Rules', 'url' => ['/rbac/auth-rule/index']],
                ]       
            ]]
        ]
    ];

    public static function getDb()
    {
        /** @var self $module */
        $module = \Yii::$app->getModule('rbac');
        return $module->dbConnection
            ? \Yii::createObject($module->dbConnection)
            : \Yii::$app->db;
    }
}
