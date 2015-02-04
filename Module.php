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
        \Yii::$app->cache->flush();
        parent::init();

        // custom initialization code goes here
    }

    public function install($moduleName)
    {
        try {
            $identity = \Yii::$app->user->identityClass;
        } catch (\Exception $e) {
            $identity = null;
        }
        if ($identity && ($id = \Yii::$app->user->id)) {
            return $this->setRootUser($id);
        }

        $url = Html::a("link", Url::toRoute(["/{$moduleName}/auth-item/update", "id"=>"root", "pid"=>""]));
        /**
         * @var ActiveRecord $identity
         */
        if ($identity && class_exists($identity) && ($identity::find()->count())) {
            $id = $identity::find()->orderBy([$identity::primaryKey() => SORT_ASC])->one()->primaryKey;
            $this->setRootUser($id);
            $message = \Yii::t('modules/rbac', 'Root attached to first user. You may attach Root role manually: '.$url);
        } else {
            $message = \Yii::t('modules/rbac', 'No user found. You may create user and attach Root role manually: '.$url);
        }

        return \Yii::$app->session->setFlash('error', $message);
    }

    private function setRootUser($id)
    {
        return (new AuthAssignment(['item_name' => AuthItem::ROLE_ROOT, 'user_id' => $id]))->save(false);
    }


}
