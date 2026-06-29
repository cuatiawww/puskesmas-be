<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "modul".
 *
 * @property int $id
 * @property string $nama_modul Nama modul/menu
 * @property string $label Label yang ditampilkan di sidebar
 * @property string|null $deskripsi Deskripsi modul
 * @property string|null $icon Icon class (ph-duotone ph-xxx)
 * @property int|null $urutan Urutan tampil di sidebar
 * @property bool|null $is_active Status aktif/nonaktif
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property SubModul[] $subModuls
 */
class Modul extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'modul';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_user');
    }

    public function rules()
    {
        return [
            [['deskripsi', 'icon'], 'default', 'value' => null],
            [['urutan'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => 1],
            [['nama_modul', 'label'], 'required'],
            [['urutan'], 'integer'],
            [['is_active'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['nama_modul', 'label'], 'string', 'max' => 100],
            [['deskripsi'], 'string', 'max' => 255],
            [['icon'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama_modul' => 'Nama Modul',
            'label' => 'Label',
            'deskripsi' => 'Deskripsi',
            'icon' => 'Icon',
            'urutan' => 'Urutan',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getSubModuls()
    {
        return $this->hasMany(SubModul::class, ['modul_id' => 'id']);
    }

    public function getJumlahSubModul()
    {
        return $this->getSubModuls()->count();
    }
}
