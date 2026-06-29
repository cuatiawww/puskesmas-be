<?php

namespace app\models;

use Yii;

class MasterWilayah extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'master_wilayah';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_user');
    }

    public function rules()
    {
        return [
            [['nama_wilayah', 'level_wilayah'], 'required'],
            [['tbl_wilayah_id', 'level_wilayah', 'parent_tbl_wilayah_id', 'parent_master_wilayah_id', 'regional', 'status_aktif'], 'integer'],
            [['alamat_kantor', 'nama_dinkes', 'nama_kadinkes', 'keterangan'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['nama_wilayah', 'email_legacy'], 'string', 'max' => 255],
            [['username_legacy'], 'string', 'max' => 150],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama_wilayah' => 'Nama Wilayah',
            'level_wilayah' => 'Level Wilayah',
            'parent_master_wilayah_id' => 'Parent Wilayah',
        ];
    }

    public function getParent()
    {
        return $this->hasOne(self::className(), ['id' => 'parent_master_wilayah_id']);
    }
}
