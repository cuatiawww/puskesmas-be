<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class ModulSearch extends Modul
{
    public function rules()
    {
        return [
            [['id', 'urutan'], 'integer'],
            [['nama_modul', 'label', 'deskripsi'], 'string'],
            [['is_active'], 'boolean'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Modul::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['urutan' => SORT_ASC, 'id' => SORT_DESC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'urutan' => $this->urutan,
            'is_active' => $this->is_active,
        ]);

        $query->andFilterWhere(['ilike', 'nama_modul', $this->nama_modul])
              ->andFilterWhere(['ilike', 'label', $this->label])
              ->andFilterWhere(['ilike', 'deskripsi', $this->deskripsi]);

        return $dataProvider;
    }
}
