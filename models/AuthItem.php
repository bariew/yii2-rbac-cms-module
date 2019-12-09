<?php
/**
 * AuthItem class file.
 *
 * @copyright (c) 2014, Bariew
 * @license       http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\rbacModule\models;

use bariew\rbacModule\components\TreeBuilder;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\rbac\Item;
use yii\web\HttpException;

/**
 * Модель управления ролями пользователей.
 *
 * @property string              $name
 * @property integer             $type
 * @property string              $description
 * @property string              $rule_name
 * @property string              $data
 * @property integer             $created_at
 * @property integer             $updated_at
 * @property AuthAssignment      $authAssignment
 * @property AuthRule            $ruleName
 * @property \yii\db\ActiveQuery $parents
 * @property array               $users
 * @property \yii\db\ActiveQuery $authItemChildren
 * @property string              $id
 * @property \yii\db\ActiveQuery $authItemParents
 * @property mixed               $roles
 * @property \yii\db\ActiveQuery $authAssignments
 * @property \yii\db\ActiveQuery $permissions
 * @property array               $children
 * @property \yii\rbac\Item      $item
 * @property AuthItemChild       $authItemChild
 * @method string menuWidget($data = [], $callback = false) bariew\rbacModule\components\TreeBuilder
 * @method array nodeAttributes($model = false, $pid = '', $uniqueKey = false) bariew\rbacModule\components\TreeBuilder
 */
class AuthItem extends ActiveRecord
{
    const ROLE_ROOT = 'root';
    const ROLE_DEFAULT = 'default';
    const ROLE_GUEST = 'guest';

    public static $userRoles = [];
    public static $defaultRoles = [];
    /**
     * @var array container for autItem tree for menu widget.
     */
    public $childrenTree = [];

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
            [['name'], 'defaultRoleRenameRule'],
            [['type'], 'default', 'value' => Item::TYPE_ROLE],
            [['created_at', 'updated_at', 'type'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 64],
            [
                ['rule_name'], 'filter',
                'filter' => function ($value) {
                    return ($value) ? $value : NULL;
                }
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            [
                'class'             => TreeBuilder::class,
                'childrenAttribute' => 'childrenTree',
                'actionPath'        => '/rbac/auth-item/update'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'        => self::typeList()[$this->type],
            'type'        => Yii::t('modules/rbac', 'Type'),
            'description' => Yii::t('modules/rbac', 'Description'),
            'rule_name'   => Yii::t('modules/rbac', 'Rule name'),
            'data'        => Yii::t('modules/rbac', 'Data'),
            'created_at'  => Yii::t('modules/rbac', 'Created at'),
            'updated_at'  => Yii::t('modules/rbac', 'Updated at'),
        ];
    }

    public static function defaultRoleList()
    {
        return [
            self::ROLE_ROOT    => Yii::t('modules/rbac', 'role_root'),
            self::ROLE_DEFAULT => Yii::t('modules/rbac', 'role_default'),
            self::ROLE_GUEST   => Yii::t('modules/rbac', 'role_guest'),
        ];
    }

    public static function typeList()
    {
        return [
            Item::TYPE_ROLE       => Yii::t('modules/rbac', 'role'),
            Item::TYPE_PERMISSION => Yii::t('modules/rbac', 'permission'),
        ];
    }

    /**
     * Creates valid permission name for controller action.
     *
     * @param array $data items for permission name (moduleId, ControllerId, ActionId).
     * @return string permission name.
     */
    public static function createPermissionName($data)
    {
        return implode('/', $data);
    }

    /**
     * Check whether the user has access to permission.
     *
     * @param mixed $permissionName permission name or its components for self::createPermissionName.
     * @param mixed $user           user
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

        if (!$user->isGuest && !isset(self::$userRoles[$user->id])) {
            self::$userRoles[$user->id] = Yii::$app->authManager->getRolesByUser($user->id);
        }

        if (isset(self::$userRoles[$user->id][self::ROLE_ROOT])) {
            return true;
        }

        if (!self::$defaultRoles) {
            self::setDefaultRoles($user->id);
        }
        if (in_array($permissionName, self::$defaultRoles)) {
            return true;
        }

        return $user->can($permissionName, $params);
    }

    protected static function setDefaultRoles($user_id)
    {
        self::$defaultRoles = $user_id
            ? AuthItemChild::childList(self::ROLE_DEFAULT)
            : AuthItemChild::childList(self::ROLE_GUEST);

        self::$defaultRoles
            = Yii::$app->authManager->defaultRoles
            = array_merge(
            Yii::$app->authManager->defaultRoles,
            self::$defaultRoles
        );
    }

    public function isDefaultRole($role)
    {
        return in_array($role, array_keys(self::defaultRoleList()));
    }

    public function defaultRoleRenameRule($attribute)
    {
        if ($this->isAttributeChanged($attribute) && $this->isDefaultRole(@$this->oldAttributes['name'])) {
            return $this->addError($attribute, Yii::t('modules/rbac', 'default_role_renaming_error'));
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemParents()
    {
        return $this->hasMany(AuthItemChild::class, ['child' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParents()
    {
        return $this->hasMany(self::class, ['name' => 'parent'])->via('authItemParents');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren()
    {
        return $this->hasMany(AuthItemChild::class, ['parent' => 'name']);
    }

    /**
     * Gets items attached to current one by AuthItemChild relation.
     *
     * @return array of AuthItems
     */
    public function getChildren()
    {
        return $this->hasMany(self::class, ['name' => 'child'])
            ->via('authItemChildren');
    }

    public function getRoles()
    {
        return $this->hasMany(self::class, ['name' => 'child'])
            ->via('authItemChildren')
            ->where(['type' => Item::TYPE_ROLE]);
    }

    /**
     * Gets permissions AuthItems attached to current model through AuthItemChild.
     *
     * @return \yii\db\ActiveQuery search object.
     */
    public function getPermissions()
    {
        return $this->hasMany(self::class, ['name' => 'child'])
            ->via('authItemChildren')
            ->where(['type' => Item::TYPE_PERMISSION]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::class, ['item_name' => 'name']);
    }

    /**
     * Gets items attached to current one by AuthItemChild relation.
     *
     * @return array of AuthItems
     */
    public function getUsers()
    {
        if (!$user = AuthAssignment::userInstance()) {
            return [];
        }

        return $this->hasMany(get_class($user), ['id' => 'user_id'])
            ->via('authAssignments');
    }

    /**
     * Some times in views you just need give them 'id'
     *
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

    public function getItem()
    {
        return new Item([
            'ruleName'    => $this->rule_name,
            'createdAt'   => $this->created_at,
            'updatedAt'   => $this->updated_at,
            'name'        => $this->name,
            'type'        => $this->type,
            'description' => $this->description,
            'data'        => $this->data
        ]);
    }

    public function updateItem()
    {
        return Yii::$app->authManager->update($this->oldAttributes['name'], $this->getItem());
    }

    /**
     * Detaches this model from its old parent
     * and attaches to the new one.
     *
     * @param AuthItem $oldParent item
     * @param AuthItem $newParent item
     * @return int|false whether model has been moved.
     */
    public function move($oldParent, $newParent)
    {
        return
            $oldParent->removeChild($this)
                ? $newParent->addChild($this)
                : false;
    }

    /**
     * Attaches child related to this model by AuthItemChild.
     *
     * @param AuthItem $item child.
     * @return integer whether child is attached.
     */
    public function addChild(AuthItem $item)
    {
        if ($item->isNewRecord && !$item->save()) {
            return false;
        }

        return Yii::$app->authManager->addChild($this, $item);
    }

    /**
     * Detaches child related to this model by AuthItemChild.
     *
     * @param AuthItem $item child.
     * @return integer whether child is detached.
     */
    public function removeChild($item)
    {
        return Yii::$app->authManager->removeChild($this, $item);
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

        return Yii::$app->authManager->remove($this->getItem());
    }
}
