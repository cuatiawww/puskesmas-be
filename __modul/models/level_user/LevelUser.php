<?php

namespace app\models\level_user;

use Yii;
use app\models\Browser;
use app\models\notifikasi\NotifikasiModel;
/**
 * This is the model class for table "level_user".
 *
 * @property int $id
 * @property string $nama_level Nama level user (Super Admin, Admin, User, dll)
 * @property string|null $deskripsi Deskripsi level user
 * @property bool|null $is_active Status aktif/nonaktif
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property HakAkses[] $hakAkses
 * @property User[] $users
 */
class LevelUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'level_user';
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
            [['nama_level'], 'required'],
            [['deskripsi'], 'string'],
            [['is_active'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['nama_level'], 'string', 'max' => 100],
            [['nama_level'], 'unique'],
        ];
    }

    public function catat_log($nama_log, $variabel=NULL){
		// $user = Yii::$app->user->identity;
		// $id_user = $user['id_user'];
		// //$browser = get_browser(null, true);
		// $browser = new \Browser();        
		// $nama_browser = $browser->getBrowser()." ".$browser->getVersion()." (".$browser->getPlatform().")";
		// $ip = Yii::$app->request->remoteIP;
		// $tanggal = date('Y-m-d');
		// $jam = date('H:i:s');
		
		// //$params = [1 => $id];

        // $model_notifikasi = new NotifikasiModel();
        // $model_notifikasi->id_user = $id_user;
        // $model_notifikasi->modul = $nama_log;
        // $model_notifikasi->variabel = $variabel;
        // $model_notifikasi->ip = $ip;
        // $model_notifikasi->browser = $nama_browser;
        // $model_notifikasi->tanggal = $tanggal;
        // $model_notifikasi->jam = $jam;
        // $model_notifikasi->save(false);		
		
	}

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama_level' => 'Nama Level',
            'deskripsi' => 'Deskripsi',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[HakAkses]].
     *
     * @return \yii\db\ActiveQuery
     */
    // public function getHakAkses()
    // {
    //     return $this->hasMany(HakAkses::className(), ['level_user_id' => 'id']);
    // }

    // /**
    //  * Gets query for [[Users]].
    //  *
    //  * @return \yii\db\ActiveQuery
    //  */
    // public function getUsers()
    // {
    //     return $this->hasMany(User::className(), ['level_user_id' => 'id']);
    // }
}
