<?php
/**
 * MyClass class file.
 * @copyright (c) 2014, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\rbacModule\models;

use Yii;
use \yii\db\ActiveRecord;
use yii\rbac\Rule;

/**
 * This is the model class for table "auth_rule".
 *
 * @property string $name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property AuthItem[] $authItems
 */
class AuthRule extends ActiveRecord
{
    public $ruleClass;

    public $rule;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_rule}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'ruleClass'], 'required'],
            [['ruleClass'], 'rulePathValidator'],
            [['created_at', 'updated_at'], 'integer'],
            [['name', 'ruleClass'], 'string', 'max' => 64]
        ];
    }

    public function rulePathValidator($attribute)
    {
        $class = $this->$attribute;
        if (!preg_match('/^.*\\.*$/', $class)) {
            return $this->addError($attribute, 'Path must match "someAlias\...\className" pattern');
        }
        if (!class_exists($class)) {
            return $this->addError($attribute, "Class does not exist");
        }
        /**
         * @var Rule $rule
         */
        $rule = new $class();
        if (!$rule instanceof Rule) {
            return $this->addError($attribute, 'Rule must be instance of yii\rbac\Rule');
        }
        $rule->name = $this->name;
        $this->rule = $rule;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'       => Yii::t('modules/rbac', 'name'),
            'data'       => Yii::t('modules/rbac', 'data'),
            'created_at' => Yii::t('modules/rbac', 'created_at'),
            'updated_at' => Yii::t('modules/rbac', 'updated_at'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItems()
    {
        return $this->hasMany(AuthItem::className(), ['rule_name' => 'name']);
    }

    public static function listAll()
    {
        $names = self::find()->select('name')->orderBy('name ASC')->column();
        return array_combine($names, $names);
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->rule = unserialize($this->data);
        $this->ruleClass = get_class($this->rule);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->data = serialize($this->rule);
        return true;
    }
}
