<?php
/**
 * AuthItem class file.
 * @copyright (c) 2014, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\rbacModule\models;

use Yii;
use yii\base\Event;
use \yii\rbac\Item;
use yii\db\ActiveRecord;
use \bariew\rbacModule\components\TreeBuilder;
use \yii\behaviors\TimestampBehavior;
use \yii\web\HttpException;
use yii\helpers\ArrayHelper;

/**
 * Модель управления ролями пользователей.
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property AuthAssignment $authAssignment
 * @property AuthRule $ruleName
 * @property AuthItemChild $authItemChild
 */
class AuthItem extends ActiveRecord
{
    public static function typeList()
    {
        return [
            Item::TYPE_ROLE => Yii::t('modules/rbac', 'role'),
            Item::TYPE_PERMISSION => Yii::t('modules/rbac', 'permission'),
        ];
    }
    /**
     * Checks whether current user has access to current controller action.
     * @param Event $event controller beforeAction event.
     */
    public static function checkActionAccess($event)
    {
        $controller = $event->sender;
        $permissionName = [$controller->module->id, $controller->id, $controller->action->id];
        if (!self::checkAccess($permissionName, Yii::$app->user)) {
            Yii::$app->session->setFlash('error', Yii::t('backend', 'rbac_access_denied'));
            $controller->redirect(Yii::$app->request->referrer);
        }
    }

    /**
     * Creates valid permission name for controller action.
     * @param array $data items for permission name (moduleId, ControllerId, ActionId).
     * @return string permission name.
     */
    public static function createPermissionName($data)
    {
        return implode('_', $data);
    }

    /**
     * Check whether the user has access to permission.
     * @param mixed $permissionName permission name or its components for self::createPermissionName.
     * @param mixed $user user
     * @return boolean whether user has access to permission name.
     */
    public static function checkAccess($permissionName, $user = false)
    {
        if (!$user) {
            $user = Yii::$app->user;
        }
        if (is_array($permissionName)) {
            $permissionName = self::createPermissionName($permissionName);
        }
        $auth = Yii::$app->authManager;
        if (isset($auth->getRolesByUser($user->id)['root'])) {
            return true;
        }
        $permissions = array_keys($auth->getPermissions());
        if (!in_array($permissionName, $permissions)) {
            return true;
        }

        return $user->can($permissionName);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'unique'],
            [['type'], 'default', 'value' => Item::TYPE_ROLE],
            [['created_at', 'updated_at', 'type'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => self::typeList()[$this->type],
            'type' => Yii::t('modules/rbac', 'type'),
            'description' => Yii::t('modules/rbac', 'description'),
            'rule_name' => Yii::t('modules/rbac', 'rbac_rule_name'),
            'data' => Yii::t('modules/rbac', 'data'),
            'created_at' => Yii::t('modules/rbac', 'created_at'),
            'updated_at' => Yii::t('modules/rbac', 'updated_at'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class' => TreeBuilder::className(),
                'actionPath' => '/rbac/auth-item/update'
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemParents()
    {
        return $this->hasMany(AuthItemChild::className(), ['child' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParents()
    {
        return $this->hasMany(self::className(), ['name' => 'parent'])->via('authItemParents');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren()
    {
        return $this->hasMany(AuthItemChild::className(), ['parent' => 'name']);
    }

    /**
     * Gets items attached to current one by AuthItemChild relation.
     * @return array of AuthItems
     */
    public function getChildren()
    {
        return $this->hasMany(self::className(), ['name' => 'child'])
            ->via('authItemChildren');
    }

    public function getRoles()
    {
        return $this->hasMany(self::className(), ['name' => 'child'])
            ->via('authItemChildren')
            ->where(['type' => Item::TYPE_ROLE]);
    }

    /**
     * Gets permissions AuthItems attached to current model through AuthItemChild.
     * @return \yii\db\ActiveQuery search object.
     */
    public function getPermissions()
    {
        return $this->hasMany(self::className(), ['name' => 'child'])
            ->via('authItemChildren')
            ->where(['type' => Item::TYPE_PERMISSION]);
    }

    /**
     * Detaches this model from its old parent
     * and attaches to the new one.
     * @param AuthItem $oldParent item
     * @param AuthItem $newParent item
     * @return boolean whether model has been moved.
     */
    public function move($oldParent, $newParent)
    {
        if ($oldParent->removeChild($this)) {
            return $newParent->addChild($this);
        }
        return false;
    }

    /**
     * Attaches child related to this model by AuthItemChild.
     * @param AuthItem $item child.
     * @return integer whether child is attached.
     */
    public function addChild(AuthItem $item)
    {
        return Yii::$app->authManager->addChild($this, $item);
    }

    /**
     * Detaches child related to this model by AuthItemChild.
     * @param AuthItem $item child.
     * @return integer whether child is detached.
     */
    public function removeChild($item)
    {
        return Yii::$app->authManager->removeChild($this, $item);
    }

    /**
     * Находит в таблице все роли.
     *
     * @return \yii\db\ActiveQuery
     */
    public static function findRoles()
    {
        return static::find()
            ->where(['type' => Item::TYPE_ROLE]);
    }

    /**
     * Находит в таблице все права доступа.
     *
     * @return \yii\db\ActiveQuery
     */
    public static function findPermissions()
    {
        return static::find()
            ->where(['type' => Item::TYPE_PERMISSION]);
    }

    /**
     * Generates readable role list.
     * @return array role list
     */
    public static function roleList()
    {
        $roles = self::findRoles()->indexBy('name')->asArray()->all();
        $roles = ArrayHelper::map($roles, 'name', 'name');
        array_unshift($roles, '');
        return $roles;
    }

    /**
     * Generates readable permission list.
     * @return array permission list
     */
    public static function permissionList()
    {
        $result = [];
        $items = self::findPermissions()->select('name')->column();
        $y = 'Yii';
        foreach ($items as $name) {
            $result[$name] = $y::t('access', $name);
        }
        return $result;
    }

    /**
     * Some times in views you just need give them 'id'
     * @return string model name
     */
    public function getId()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        if ($this->name == 'root') {
            throw new HttpException(403, Yii::t('modules/rbac', 'delete_error'));
        }
        foreach ($this->getChildren()->all() as $item) {
            $this->removeChild($item);
        }
        foreach ($this->getParents()->all() as $item) {
            $item->removeChild($this);
        }
        return true;
    }
}
