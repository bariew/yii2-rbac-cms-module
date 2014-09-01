<?php
namespace bariew\rbacModule\models\search;
use Yii;
use yii\data\ActiveDataProvider;
use bariew\rbacModule\models\AuthItem;
use \yii\rbac\Role;
/**
 * AuthItem represents the model behind the search form about `bariew\rbacModule\models\AuthItem`.
 */
class AuthItemSearch extends AuthItem
{
    public function rules()
    {
        return [
            [['name', 'description', 'rule_name', 'data'], 'safe'],
            [['type', 'created_at', 'updated_at'], 'integer'],
        ];
    }
    /**
     * Поиск ролей в базе данных.
     * Поиск идет только для записей с type => 1, что означает "роль", а не "право доступа".
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = AuthItem::find();
        $query->where(['type' => Role::TYPE_ROLE]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $query->andFilterWhere([
                'type' => $this->type,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]);
        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'rule_name', $this->rule_name])
            ->andFilterWhere(['like', 'data', $this->data]);
        return $dataProvider;
    }
}