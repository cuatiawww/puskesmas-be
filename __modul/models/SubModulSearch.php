<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\SubModul;

class SubModulSearch extends SubModul
{
    public function rules()
    {
        return [
            [['id', 'modul_id', 'parent_id', 'urutan'], 'integer'],
            [['nama_sub_modul', 'label', 'route', 'icon'], 'safe'],
            [['is_active'], 'boolean'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = SubModul::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'modul_id' => $this->modul_id,
            'parent_id' => $this->parent_id,
            'urutan' => $this->urutan,
            'is_active' => $this->is_active,
        ]);

        $query->andFilterWhere(['ilike', 'nama_sub_modul', $this->nama_sub_modul])
            ->andFilterWhere(['ilike', 'label', $this->label])
            ->andFilterWhere(['ilike', 'route', $this->route])
            ->andFilterWhere(['ilike', 'icon', $this->icon]);

        return $dataProvider;
    }
}
