<?php
/**
 * AuthAssignmentController class file.
 * @copyright (c) 2014, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\rbacModule\controllers;

use yii\web\Controller;
use \bariew\rbacModule\models\AuthItem;
use Yii;
use \bariew\rbacModule\models\AuthAssignment;
use yii\helpers\Html;
use yii\web\HttpException;

/**
 * Контроллер отвечает за назначение определенных "ролей" для "пользователей".
 *
 * @see yii\rbac\Role
 * @see yii\rbac\Permission
 * @see yii\rbac\DbManager
 */
class AuthAssignmentController extends Controller
{
    /**
     * @inheritdoc
     */
    protected $modelClass = 'AuthAssignment';

    /**
     * Название раздела.
     *
     * @return string 
     */
    public function getTitle()
    {
        return Yii::t('modules/rbac', 'title_roles_to_managers');
    }
    
    /**
     * Renders manager permission AuthItem tree.
     * @param integer $id manager id.
     * @return mixed view
     */
    public function actionManagerPermissions($id)
    {
        $user = Manager::findOne($id);
        $permissions = AuthAssignment::userAssignments($user)
            ->select('name')->column();
        $treeWidget = Html::tag("div", Yii::t('modules/rbac', 'manual_rbac_tree'), ['class' => 'manual'])
            . AuthItem::findOne('root')
            ->menuWidget(['selected'=>$permissions], 'checkboxCallback');
        return $this->render('userPermissions', compact('treeWidget'));
    }

    public function actionRoleUsers($name)
    {
        $users = AuthAssignment::userList();
        $role = AuthItem::findOne($name);
        echo $this->renderPartial('role-users', compact('role', 'users'));
    }

    /**
     * Attaches or detaches user role/permission.
     * @param string $id permission/role name.
     * @param integer $user_id user id.
     * @param integer $add 1/0 whether to add or to remove user permission.
     * @throws \yii\web\HttpException only_root_remove_denied
     */
    public function actionChange($id, $user_id, $add)
    {
        $authItem = AuthItem::findOne($id);
        if ($add) {
            Yii::$app->authManager->assign($authItem, $user_id);
        } else {
            $rootCount = AuthAssignment::find()->where(['item_name' => $id])->count();
            if ($id == 'root' && ! $rootCount < 2) {
                throw new HttpException(403, Yii::t('access', 'only_root_remove_denied'));
            }
            Yii::$app->authManager->revoke($authItem, $user_id);
        }
    }
}
