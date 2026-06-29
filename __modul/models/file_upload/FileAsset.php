<?php

namespace app\models\file_upload;

use app\components\Helper;
use Yii;
use app\models\Browser;
use yii\web\Response;
use app\models\notifikasi\NotifikasiModel;

/**
 * This is the model class for table "file_asset".
 *
 * @property int $id
 * @property string|null $file_path
 * @property string|null $hash
 * @property string|null $tipe_file
 * @property string|null $ukuran
 * @property int|null $id_user
 * @property string|null $update_date
 */
class FileAsset extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'file_asset';
    }

    //  public static function getDb()
    // {
    //     return Yii::$app->get('db_asset');
    // }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file_path', 'hash', 'tipe_file', 'ukuran'], 'string'],
            [['id_user'], 'default', 'value' => null],
            [['id_user'], 'integer'],
            [['update_date', 'file_name'], 'safe'],
        ];
    }

    public static function check_file(string $uxid) : bool
    {
         /** @var \yii\web\Response $response */
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;

        $id = $uxid;

        $fa = FileAsset::findOne(['hash' => $id]);
        if (!$fa) {
           return false;
        }

        $storage = Yii::$app->params['storage'] ?? [];
        if (Yii::$app->has('fs') && (($storage['driver'] ?? '') === 's3')) {
            $path = trim(str_replace('\\', '/', (string)$fa['file_path']), '/');
            $name = ltrim(str_replace('\\', '/', (string)$fa['file_name']), '/');
            $key = $path === '' ? $name : $path . '/' . $name;

            try {
                return Yii::$app->fs->fileExists($key);
            } catch (\Throwable $e) {
                return false;
            }
        }

        $base = realpath(Yii::getAlias('@webroot'.$fa['file_path'])); // samakan dengan upload_path-mu
        $real = $base.DIRECTORY_SEPARATOR.$fa['file_name'];
        
        if (!$real || !$base || strpos($real, $base) !== 0 || !is_file($real)) {
            return false;
        }

        return true;

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
            'file_path' => 'File Path',
            'hash' => 'Hash',
            'tipe_file' => 'Tipe File',
            'ukuran' => 'Ukuran',
            'id_user' => 'Id User',
            'update_date' => 'Update Date',
        ];
    }
}
