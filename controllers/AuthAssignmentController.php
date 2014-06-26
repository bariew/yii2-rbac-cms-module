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
        return Yii::t('backend', 'title_roles_to_managers');
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
        $treeWidget = Html::tag("div", Yii::t('backend', 'manual_rbac_tree'), ['class' => 'manual'])
            . AuthItem::findOne('root')
            ->menuWidget(['selected'=>$permissions], 'checkboxCallback');
        return $this->render('userPermissions', compact('treeWidget'));
    }
    
    /**
     * Attaches or detaches user role/permission.
     * @param string $id permission/role name.
     * @param integer $user_id user id.
     * @param integer $add 1/0 whether to add or to remove user permission.
     */
    public function actionChange($id, $user_id, $add)
    {
        $authItem = AuthItem::findOne($id);
        if ($add) {
            Yii::$app->authManager->assign($authItem, $user_id);
        } else {
            Yii::$app->authManager->revoke($authItem, $user_id);
        }
    }
}
