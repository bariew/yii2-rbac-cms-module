<?php
/**
 * MyClass class file.
 * @copyright (c) 2014, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\rbacModule\models;
use Yii;
use \yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "auth_assignment".
 *
 * @property string $item_name
 * @property integer $user_id
 * @property integer $created_at
 *
 * @property AuthItem $itemName
 */
class AuthAssignment extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_assignment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_name', 'user_id'], 'required'],
            [['user_id', 'created_at'], 'integer'],
            [['item_name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'item_name'  => Yii::t('modules/rbac', 'Role'),
            'user_id'    => Yii::t('modules/rbac', 'User ID'),
            'created_at' => Yii::t('modules/rbac', 'Created at'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'      => TimestampBehavior::className(),
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemName()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'item_name']);
    }
    
    /**
     * Searches all user assignments.
     * @param object $user user instance
     * @return \yii\web\Query search object
     */
    public static function userAssignments($user)
    {
        $names = self::find()->where(['user_id' => $user->id])->select('item_name')->column();
        return AuthItem::find()->where(['in', 'name', $names]);
    }

    public static function userList()
    {
        if (!$user = self::userInstance()) {
            return [];
        }
        foreach (['name', 'username', 'login', 'id'] as $attribute) {
            if ($user->hasAttribute($attribute)) {
                return ArrayHelper::map($user::find()->all(), 'id', $attribute);
            }
        }
        return [];
    }

    public static function userInstance()
    {
        if (!isset(Yii::$app->user)) {
            return false;
        }
        $className = Yii::$app->user->identityClass;
        return new $className();
    }
}
