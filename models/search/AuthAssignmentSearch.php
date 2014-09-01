<?php
namespace bariew\rbacModule\models\search;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use bariew\rbacModule\models\AuthAssignment;
/**
 * AuthAssignmentSearch represents the model behind the search form about `bariew\rbacModule\models\AuthAssignment`.
 */
class AuthAssignmentSearch extends AuthAssignment
{
    public function rules()
    {
        return [
            [['item_name'], 'safe'],
            [['user_id'], 'integer'],
            [['created_at'], 'string']
        ];
    }
    public function scenarios()
    {
// bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }
    public function search($params)
    {
        $query = AuthAssignment::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $query->andFilterWhere(['user_id' => $this->user_id]);
        if ($this->created_at) {
            $time = strtotime($this->created_at);
            $query->andFilterWhere(['between', 'created_at', $time, $time+86400]);
        }
        $query->andFilterWhere(['like', 'item_name', $this->item_name]);
        return $dataProvider;
    }
}