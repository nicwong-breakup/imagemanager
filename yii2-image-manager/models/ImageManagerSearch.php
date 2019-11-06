<?php

namespace noam148\imagemanager\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use noam148\imagemanager\models\ImageManager;
use noam148\imagemanager\Module;

/**
 * ImageManagerSearch represents the model behind the search form about `common\modules\imagemanager\models\ImageManager`.
 */
class ImageManagerSearch extends ImageManager
{
    public $globalSearch, $idOrder;
    
    public function __construct($string = '') {
        if($string !== '') {
            $arr = explode(',', $string);
            $reverseArr = array_reverse($arr);
            $this->idOrder = implode(',', $reverseArr);
        }
    }
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['globalSearch'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        if(isset($this->idOrder) && $this->idOrder != '' && $this->idOrder != null)
        $query = ImageManager::find()->orderBy([new \yii\db\Expression('FIELD (id, '.$this->idOrder.') desc')]);
        else
        $query = ImageManager::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
			'pagination' => [
				'pagesize' => 50,
			],
			'sort'=> ['defaultOrder' => ['created'=>SORT_DESC]]
        ]);

        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
        }

        // Get the module instance
        $module = Module::getInstance();

        // if ($module->setBlameableBehavior) {
        //     $query->andWhere(['createdBy' => Yii::$app->user->id]);
        // }

        $query->andFilterWhere(['like', 'fileName', $this->globalSearch]);

        return $dataProvider;
    }
}
