<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "puskesmas_penyakit".
 *
 * @property int $id
 * @property int $puskesmas_id
 * @property int $tahun
 * @property int $bulan
 * @property string $nama_penyakit
 * @property int|null $jumlah_kasus
 * @property int $ranking
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property PuskesmasProfile $puskesmas
 */
class PuskesmasPenyakit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'puskesmas_penyakit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['puskesmas_id', 'tahun', 'bulan', 'nama_penyakit', 'ranking'], 'required'],
            [['puskesmas_id', 'tahun', 'bulan', 'jumlah_kasus', 'ranking'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['nama_penyakit'], 'string', 'max' => 255],
            [['puskesmas_id', 'tahun', 'bulan', 'ranking'], 'unique', 'targetAttribute' => ['puskesmas_id', 'tahun', 'bulan', 'ranking']],
            [['puskesmas_id'], 'exist', 'skipOnError' => true, 'targetClass' => PuskesmasProfile::class, 'targetAttribute' => ['puskesmas_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'puskesmas_id' => 'Puskesmas',
            'tahun' => 'Tahun',
            'bulan' => 'Bulan',
            'nama_penyakit' => 'Nama Penyakit',
            'jumlah_kasus' => 'Jumlah Kasus',
            'ranking' => 'Ranking',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Puskesmas]].
     */
    public function getPuskesmas()
    {
        return $this->hasOne(PuskesmasProfile::class, ['id' => 'puskesmas_id']);
    }
}
