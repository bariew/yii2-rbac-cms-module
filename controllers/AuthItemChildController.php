<?php
/**
 * AuthItemChildController class file.
 * @copyright (c) 2014, Bariew
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace bariew\rbacModule\controllers;
use bariew\rbacModule\models\AuthItem;
use Yii;
use yii\web\Controller;
use \yii\web\NotFoundHttpException;
use bariew\rbacModule\models\AuthItemChild;
use yii\rbac\Item;

/**
 * Контроллер служит для создания "ролей".
 * В таблице, в которой хранятся роли, точно также хранятся ещё и "права доступа",
 * однако в рамках данного контроллера создание "прав доступа" недоступно.
 *
 * @see yii\rbac\Role
 * @see yii\rbac\Permission
 * @see yii\rbac\DbManager
 */
class AuthItemChildController extends Controller
{
    /**
     * Название раздела.
     *
     * @return string
     */
    public function getTitle()
    {
        return Yii::t('modules/rbac', 'Auth item childs');
    }
    
    /**
     * Обновление модели.
     *
     * @param integer $name parent model name
     * @return mixed
     */
    public function actionTree($name)
    {
        $parent = $this->findParentModel($name);
        $children = $parent->getPermissions()->select('name')->column();
        $actions = AuthItemChild::permissionList();
        return $this->render('tree', compact('parent', 'children', 'actions'));
    }
    
    /**
     * Creates new permission attached to $id owner.
     * @param integer $id parent id
     * @return \yii\web\View action view
     */
    public function actionAdd($id, $parent_id, $add = 1)
    {
        $parent = $this->findParentModel($parent_id);
        $model = $this->findParentModel();
        $model->type = Item::TYPE_PERMISSION;
        $model->name = $id;
        if ((!$child = $model::findOne($id)) && $add) {
            $child = $model;
        }
        if ($add) {
            $parent->addChild($child);
        } else if ($child){
            $parent->removeChild($child);
        }
    }

    /**
     * @param bool $id
     * @throws NotFoundHttpException
     * @return AuthItemChild
     */
    protected function findModel($id = false)
    {
        if (!$id) {
            $model = new AuthItemChild();
        }else if (!$model = AuthItemChild::findOne($id)) {
            throw new NotFoundHttpException('Model not found.');
        }
        return $model;
    }
    
    /**
     * @param bool $id
     * @throws NotFoundHttpException
     * @return AuthItem
     */
    protected function findParentModel($id = false)
    {
        if (!$id) {
            $model = new AuthItem();
        }else if (!$model = AuthItem::findOne($id)) {
            throw new NotFoundHttpException('Model not found.');
        }
        $model->type = \yii\rbac\Item::TYPE_ROLE;
        return $model;
    }
}