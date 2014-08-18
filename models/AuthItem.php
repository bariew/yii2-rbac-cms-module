<?php
/**
 * AuthItem class file.
 * @copyright (c) 2014, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\rbacModule\models;

use Yii;
use yii\base\Event;
use yii\helpers\FileHelper;
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
    const ROLE_ROOT = 'root';
    const ROLE_DEFAULT = 'default';
    const ROLE_GUEST = 'guest';

    /**
     * @var array container for autItem tree for menu widget.
     */
    public $childrenTree = [];

    public static $userRoles;

    public static function defaultRoleList()
    {
        return [
            self::ROLE_ROOT => Yii::t('modules/rbac', 'role_root'),
            self::ROLE_DEFAULT => Yii::t('modules/rbac', 'role_default'),
            self::ROLE_GUEST => Yii::t('modules/rbac', 'role_guest'),
        ];
    }

    public function isDefaultRole($role)
    {
        return in_array($role, array_keys(self::defaultRoleList()));
    }


    public static function typeList()
    {
        return [
            Item::TYPE_ROLE => Yii::t('modules/rbac', 'role'),
            Item::TYPE_PERMISSION => Yii::t('modules/rbac', 'permission'),
        ];
    }

    public static function permissionList()
    {
        $result = [];
        foreach (Yii::$app->modules as $name => $config) {
            $module = Yii::$app->getModule($name);
            $controllerFiles = FileHelper::findFiles($module->controllerPath);
            foreach ($controllerFiles as $file) {
                $name = preg_replace('/.*\/(\w+)Controller\.php$/', '$1', $file);
                $id = self::getRouteName($name);
                $controller = $module->createControllerByID($id);
                $actions = array_keys($controller->actions());
                $reflection = new \ReflectionClass($controller);
                foreach ($reflection->getMethods() as $method) {
                    if (!preg_match('/action([A-Z].*)/', $method->name, $matches)) {
                        continue;
                    }
                    $actions[] = self::getRouteName($matches[1]);
                }
                foreach ($actions as $action) {
                    $result[] = self::createPermissionName([$module->id, $controller->id, $action]);
                }
            }
        }
        asort($result);
        return $result;
    }

    public static function getRouteName($string)
    {
        return strtolower(
            implode('-',
                preg_split('/([[:upper:]][[:lower:]]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY)
            )
        );
    }


    /**
     * Creates valid permission name for controller action.
     * @param array $data items for permission name (moduleId, ControllerId, ActionId).
     * @return string permission name.
     */
    public static function createPermissionName($data)
    {
        return implode('/', $data);
    }

    protected static function setUserAccess($user_id)
    {
        Yii::$app->authManager->defaultRoles = AuthItemChild::childList(self::ROLE_DEFAULT);
        self::$userRoles[$user_id] = Yii::$app->authManager->getRolesByUser($user_id);
    }

    /**
     * Check whether the user has access to permission.
     * @param mixed $permissionName permission name or its components for self::createPermissionName.
     * @param mixed $user user
     * @param array $params
     * @return boolean whether user has access to permission name.
     */
    public static function checkAccess($permissionName, $user = false, $params = [])
    {
        if (is_array($permissionName)) {
            $permissionName = self::createPermissionName($permissionName);
        }
        if (!$user) {
            $user = Yii::$app->user;
        }
        if ($user->isGuest && !Yii::$app->authManager->defaultRoles) {
            Yii::$app->authManager->defaultRoles = AuthItemChild::childList(self::ROLE_GUEST);
        } else if (!isset(self::$userRoles[$user->id])) {
            self::setUserAccess($user->id);
        }

        if (isset(self::$userRoles[$user->id][self::ROLE_ROOT])) {
            return true;
        }

        return $user->can($permissionName, $params);
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
            [['name'], 'defaultRoleRenameRule'],
            [['type'], 'default', 'value' => Item::TYPE_ROLE],
            [['created_at', 'updated_at', 'type'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 64],
            [['rule_name'], 'filter', 'filter' => function ($value) { return ($value) ? $value : null;}]
        ];
    }

    public function defaultRoleRenameRule($attribute)
    {
        if ($this->isAttributeChanged($attribute) && $this->isDefaultRole(@$this->oldAttributes['name'])) {
            return $this->addError($attribute, Yii::t('modules/rbac', 'default_role_renaming_error'));
        }
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
                'childrenAttribute' => 'childrenTree',
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
        return $oldParent->removeChild($this)
            ? $newParent->addChild($this)
            : false;
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
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['item_name' => 'name']);
    }

    /**
     * Gets items attached to current one by AuthItemChild relation.
     * @return array of AuthItems
     */
    public function getUsers()
    {
        if (!$user = AuthAssignment::userInstance()) {
            return [];
        }
        return $this->hasMany($user::className(), ['id' => 'user_id'])
            ->via('authAssignments');
    }

    /**
     * Some times in views you just need give them 'id'
     * @return string model name
     */
    public function getId()
    {
        return $this->name;
    }

    public function getRuleName()
    {
        return $this->rule_name;
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        if ($this->isDefaultRole($this->name)) {
            throw new HttpException(403, Yii::t('modules/rbac', 'default_role_delete_error'));
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
