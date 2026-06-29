<?php

namespace app\models;

use Yii;
use app\models\level_user\LevelUser;

/**
 * This is the model class for table "hak_akses".
 *
 * @property int $id
 * @property int $level_user_id ID level user
 * @property int $modul_id ID modul
 * @property int $sub_modul_id ID sub modul
 * @property bool $can_view Permission untuk view/lihat
 * @property bool $can_create Permission untuk create/tambah
 * @property bool $can_update Permission untuk update/edit
 * @property bool $can_delete Permission untuk delete/hapus
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property LevelUser $levelUser
 * @property Modul $modul
 */
class HakAkses extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hak_akses';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_user');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['level_user_id'], 'required'],
            [['level_user_id', 'modul_id', 'sub_modul_id'], 'integer'],
            [['can_view', 'can_create', 'can_update', 'can_delete'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['level_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => LevelUser::className(), 'targetAttribute' => ['level_user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'level_user_id' => 'Level User',
            'modul_id' => 'Modul',
            'sub_modul_id' => 'Sub Modul',
            'can_view' => 'Lihat',
            'can_create' => 'Tambah',
            'can_update' => 'Edit',
            'can_delete' => 'Hapus',
            'created_at' => 'Dibuat',
            'updated_at' => 'Diubah',
        ];
    }

    /**
     * Gets query for [[LevelUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLevelUser()
    {
        return $this->hasOne(\app\models\level_user\LevelUser::className(), ['id' => 'level_user_id']);
    }

    /**
     * Gets query for [[Modul]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getModul()
    {
        return $this->hasOne(Modul::className(), ['id' => 'modul_id']);
    }
}
