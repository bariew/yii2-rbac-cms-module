<?php
/**
 * AuthItemController class file.
 *
 * @copyright (c) 2014, Bariew
 * @license       http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\rbacModule\controllers;

use bariew\rbacModule\models\AuthItem;
use Yii;
use yii\rbac\Item;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Контроллер служит для создания "ролей".
 * В таблице, в которой хранятся роли, точно также хранятся ещё и "права доступа",
 * однако в рамках данного контроллера создание "прав доступа" недоступно.
 *
 * @see yii\rbac\Role
 * @see yii\rbac\Permission
 * @see yii\rbac\DbManager
 * @property string $title
 */
class AuthItemController extends Controller
{
    public $enableCsrfValidation = false;

    public $modelClass = AuthItem::class;

    public $indexView = "index";
    public $formView = "form";
    public $tabs = ['settings', 'users', 'permissions'];


    /**
     * @return string
     */
    public function getTitle()
    {
        return Yii::t('modules/rbac', 'title_roles');
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render($this->indexView);
    }

    /**
     * @param integer $id mode id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $pid = Yii::$app->getRequest()->get('pid');
        $model = $this->findModel($id);
        $parent = $pid ? $this->findModel($pid) : $model;
        if ($model->load(\Yii::$app->getRequest()->post()) && $model->updateItem()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('modules/rbac', 'Saved'));

            return $this->redirect(['update', 'id' => $model->name, 'pid' => $parent->name]);
        }

        return $this->renderForm($model);
    }

    /**
     * Creates new role attached to $id owner.
     *
     * @param integer $id parent id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionCreate($id)
    {
        $parent = $this->findModel($id);
        $model = $this->findModel();
        $model->type = Item::TYPE_ROLE;
        if ($model->load(\Yii::$app->getRequest()->post()) && $parent->addChild($model)) {
            Yii::$app->getSession()->setFlash('success', Yii::t('modules/rbac', 'Role saved'));

            return $this->redirect(['update', 'id' => $model->name, 'pid' => $parent->name]);
        }

        return $this->renderForm($model);
    }

    /**
     * Detaches model from parent.
     * And deletes model if there's no more parents.
     *
     * @param integer $id mode id
     * @return void action view
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
    }

    /**
     * Detaches model from old parent.
     * And attaches to the new one.
     *
     * @param integer $id  mode id
     * @param integer $pid parent id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionTreeMove($id, $pid)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $child = $this->findModel($id);
        $oldParent = $this->findModel($pid);
        $newParent = $this->findModel(Yii::$app->request->post('pid'));
        $child->move($oldParent, $newParent);

        return $child->nodeAttributes($child, $newParent->id, time());
    }

    /**
     * @return string
     */
    protected function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * @param bool $id
     * @return AuthItem
     * @throws NotFoundHttpException
     */
    protected function findModel($id = false)
    {
        /** @var AuthItem $authItemClassName */
        $authItemClassName = $this->getModelClass();
        if (!$id) {
            return new $authItemClassName();
        }
        if (($model = $authItemClassName::findOne(['name' => $id])) !== NULL) {
            return $model;
        } else {
            throw new NotFoundHttpException('Model not found.');
        }
    }

    /**
     * @param $model
     * @return string
     */
    protected function renderForm($model)
    {
        return $this->render($this->formView, ['model' => $model, 'tabs' => $this->tabs]);
    }
}
