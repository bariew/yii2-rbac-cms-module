<?php
/**
 * Module class file.
 *
 * @copyright (c) 2014, Bariew
 * @license       http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\rbacModule;

use bariew\rbacModule\models\AuthAssignment;
use bariew\rbacModule\models\AuthItem;
use yii\db\ActiveRecordInterface;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Module for access (roles, permissions, ip-access etc) application functionality.
 *
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'bariew\rbacModule\controllers';

    public function install($moduleName)
    {
        try {
            $identity = \Yii::$app->getUser()->identityClass;
        } catch (\Exception $e) {
            $identity = NULL;
        }
        if ($identity && ($id = \Yii::$app->getUser()->getId())) {
            return $this->setRootUser($id);
        }
        $url = Html::a("link", Url::toRoute(["/{$moduleName}/auth-item/update", "id" => AuthItem::ROLE_ROOT, "pid" => ""]));
        /** @var ActiveRecordInterface $identity */
        if ($identity && class_exists($identity) && ($identity::find()->count())) {
            $id = $identity::find()->orderBy([reset($identity::primaryKey()) => SORT_ASC])->one()->primaryKey;
            $this->setRootUser($id);
            $message = \Yii::t('modules/rbac', 'Root attached to first user. You may attach Root role manually: ' . $url);
        } else {
            $message = \Yii::t('modules/rbac', 'No user found. You may create user and attach Root role manually: ' . $url);
        }

        return \Yii::$app->session->setFlash('error', $message);
    }

    private function setRootUser($id)
    {
        return (new AuthAssignment(['item_name' => AuthItem::ROLE_ROOT, 'user_id' => $id]))->save(false);
    }
}
