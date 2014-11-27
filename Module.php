<?php
/**
 * Module class file.
 * @copyright (c) 2014, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\rbacModule;

/**
 * Module for access (roles, permissions, ip-access etc) application functionality.
 * 
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class Module extends \yii\base\Module
{
    public $params = [
        'menu'  => [
            'label'    => 'Auth',
            'items' => [
                ['label' => 'Roles & Permissions', 'url' => ['/rbac/auth-item/index']],
                ['label' => 'Rules', 'url' => ['/rbac/auth-rule/index']],
            ]
        ]
    ];

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'bariew\rbacModule\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
