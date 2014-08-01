<?php
/**
 * AuthItemController class file.
 * @copyright (c) 2014, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\rbacModule\controllers;
use bariew\rbacModule\models\AuthItem;
use Yii;
use yii\web\Controller;
use yii\helpers\Html;
use \yii\web\NotFoundHttpException;
use \yii\web\Response;
/**
 * Контроллер служит для создания "ролей".
 * В таблице, в которой хранятся роли, точно также хранятся ещё и "права доступа",
 * однако в рамках данного контроллера создание "прав доступа" недоступно.
 *
 * @see yii\rbac\Role
 * @see yii\rbac\Permission
 * @see yii\rbac\DbManager
 */
class AuthItemController extends Controller
{
    /**
     *@inheritdoc
     */
    public $layout = '//menu';

    public $enableCsrfValidation = false;
    /**
     * Generates menu.
     * @return Widget menu
     */
    public function getMenu()
    {
        $model = $this->findModel('root');
        return $model->menuWidget([], 'menuCallback');
    }
    /**
     * Название раздела.
     *
     * @return string
     */
    public function getTitle()
    {
        return Yii::t('modules/rbac', 'title_roles');
    }
    
    /**
     * @inheritdoc
     */
    public function actionIndex()
    {
        return $this->render('index', compact('model'));
    }
    
    /**
     * Обновление модели.
     *
     * @param integer $id mode id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $pid = Yii::$app->request->get('pid');
        $model = $this->findModel($id);
        $parent = $pid ? $this->findModel($pid) : $model;
        if ($model->load(\Yii::$app->request->post())) {
            if (!$model->save() && Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $model->getErrors();               
            }
            Yii::$app->session->setFlash('success', Yii::t('modules/rbac', 'model_success_saved_{id}'));
            return $this->redirect(['update', 'id' => $model->name, 'pid' => $parent->name]);
        }
        return $this->render('form', compact('model'));
    }
    
    /**
     * Creates new role attached to $id owner.
     * @param integer $id parent id
     * @return \yii\web\View action view
     */
    public function actionTreeCreateRole($id)
    {
        $parent = $this->findModel($id);
        $model =  $this->findModel();
        $model->type = 1;
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            $parent->addChild($model);
            Yii::$app->session->setFlash('success', Yii::t('modules/rbac', 'model_success_saved_{id}'));
            return $this->redirect(['update', 'id' => $model->name, 'pid' => $parent->name]);
        }
        return $this->render('_form', compact('model'));
    }
    
    /**
     * Creates new permission attached to $id owner.
     * @param integer $id parent id
     * @return \yii\web\View action view
     */
    public function actionTreeCreatePermission($id)
    {
        $parent = $this->findModel($id);
        $model = $this->findModel();
        $model->type = 2;
        if ($model->load(\Yii::$app->request->post())) {
            if (!$child = AuthItem::findOne(['name'=>$model->name])) {
                $child = $model;
            }

            if (!$child->isNewRecord || $child->save()) {
                $parent->addChild($child);
                Yii::$app->session->setFlash('success', Yii::t('modules/rbac', 'model_success_saved_{id}'));
                return $this->redirect(['update', 'id' => $model->name, 'pid' => $parent->name]);
            }
        }
        return $this->render('_form', compact('model'));
    }
    
    /**
     * Detaches model from parent. 
     * And deletes model if there's no more parents.
     * @param integer $id mode id
     * @param integer $pid parent id
     * @return \yii\web\View action view
     */
    public function actionTreeDelete($id, $pid)
    {
        $model =  $this->findModel($id);
        $oldParent = $this->findModel($pid);
        $oldParent->removeChild($model);
        if (!$model->parents) {
            $model->delete();
        }
    }
    
    /**
     * Detaches model from old parent. 
     * And attaches to the new one.
     * @param integer $id mode id
     * @param integer $pid parent id
     * @return \yii\web\View action view
     */
    public function actionTreeMove($id, $pid)
    {
        $child = $this->findModel($id);  
        $oldParent = $this->findModel($pid);
        $newParent = $this->findModel(Yii::$app->request->post('pid'));
        $child->move($oldParent, $newParent);
        echo json_encode($child->nodeAttributes($child, $newParent->id, time()));
    }
    
    /**
     * Attaches model to new parent. 
     * @param integer $id mode id
     * @return \yii\web\View action view
     */
    public function actionTreeCopy($id)
    {
        $child = $this->findModel($id);
        $newParent = $this->findModel(Yii::$app->request->post('pid'));
        $newParent->addChild($child);
        echo json_encode($child->nodeAttributes($child, $newParent->id, time()));
    }
    
    /**
     * @inheritdoc
     */
    protected function findModel($id = false)
    {
        if (!$id) {
            return new AuthItem();
        }
        if (($model = AuthItem::findOne(['name'=>$id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    } 
}
