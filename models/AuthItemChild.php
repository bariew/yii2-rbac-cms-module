<?php
/**
 * MyClass class file.
 * @copyright (c) 2014, Bariew
 * @license http://www.opensource.org/licenses/bsd-license.php
 */


namespace bariew\rbacModule\models;

use Yii;
use yii\db\ActiveRecord;
use app\controllers\SiteController;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "auth_item_child".
 *
 * @property string $parent
 * @property string $child
 *
 * @property AuthItem $role
 * @property AuthItem $permission
 */
class AuthItemChild extends ActiveRecord
{
    /**
     * Gets role list.
     * @return array list
     */
    public static function roleList()
    {
        return AuthItem::roleList();
    }
    
    public static function permissionList()
    {
      $result = [];
        $modules = array_merge([Yii::$app], \Yii::$app->modules);
        foreach ($modules as $moduleName => $data) {
            $module = is_object($data) ? $data : Yii::$app->getModule($moduleName);
            try {
                $controllerFiles = FileHelper::findFiles($module->controllerPath);
                foreach ($controllerFiles as $file) {
                    if (!preg_match('/.*[\/\\\](\w+)Controller\.php$/', $file, $matches)) {
                        continue;
                    }
                    $id = self::getRouteName($matches[1]);
                    $controller = $module->createControllerByID($id);
                    foreach (self::controllerActions($controller) as $action) {
                        $result[$module->id][$controller->id][$action]
                            = AuthItem::createPermissionName([$module->id, $controller->id, $action]);
                    }
                }
            } catch (\Exception $e) {}
        }
        ksort($result);
        return $result;
    }

    private static function controllerActions(\yii\base\Controller $controller)
    {
        $actions = array_keys($controller->actions());
        $reflection = new \ReflectionClass($controller);
        foreach ($reflection->getMethods() as $method) {
            if (!preg_match('/^action([A-Z].*)/', $method->name, $matches)) {
                continue;
            }
            $actions[] = self::getRouteName($matches[1]);
        }
        return $actions;
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
     * Gets list of all children name for the parent.
     * @param string $parent parent name.
     * @return array child list.
     */
    public static function childList($parent)
    {
        return self::find()->where(compact('parent'))->select('child')->column();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_item_child}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent', 'child'], 'required'],
            [['parent', 'child'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'parent' => Yii::t('modules/rbac', 'Role'),
            'child'  => Yii::t('modules/rbac', 'Permission'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(AuthItem::class, ['name' => 'parent']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPermission()
    {
        return $this->hasOne(AuthItem::class, ['name' => 'child']);
    }
}
