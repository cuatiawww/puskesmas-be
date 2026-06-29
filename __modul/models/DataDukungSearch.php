<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class DataDukungSearch extends DataDukung
{
    public function rules()
    {
        return [
            [['id_dukung','nama_jemaah','nama_dokumen','jenis_dokumen','status_verifikasi'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = DataDukung::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [ 'pageSize' => 20 ],
            'sort' => [ 'defaultOrder' => ['id' => SORT_DESC] ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id]);

        $query->andFilterWhere(['ilike', 'id_dukung', $this->id_dukung])
              ->andFilterWhere(['ilike', 'nama_jemaah', $this->nama_jemaah])
              ->andFilterWhere(['ilike', 'nama_dokumen', $this->nama_dokumen])
              ->andFilterWhere(['ilike', 'jenis_dokumen', $this->jenis_dokumen])
              ->andFilterWhere(['ilike', 'status_verifikasi', $this->status_verifikasi]);

        return $dataProvider;
    }
}
