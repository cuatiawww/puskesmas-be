<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class DataLokasiSearch extends DataLokasi
{
    public function rules()
    {
        return [
            [['id', 'nomor_checkin'], 'integer'],
            [['nama_lokasi'], 'safe'],
            [['is_active'], 'boolean'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = DataLokasi::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'nomor_checkin' => $this->nomor_checkin,
            'is_active' => $this->is_active,
        ]);

        $query->andFilterWhere(['ilike', 'nama_lokasi', $this->nama_lokasi]);

        return $dataProvider;
    }
}
