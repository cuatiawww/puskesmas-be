<?php

namespace app\models\badword;

use Yii;

/**
 * This is the model class for table "badword".
 *
 * @property int $id
 * @property string $word
 * @property string $lang
 */
class BadwordModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'badword';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['word', 'lang'], 'required'],
            [['word', 'lang'], 'string'],
            [['word'], 'match', 'pattern'=> '/^([a-zA-Z]*)$/','message'=> "ERROR!!! Hanya Masukkan Kata ({attribute})"],
        ];
    }

    public function catat_log($nama_log, $variabel=NULL){
		$user = Yii::$app->user->identity;
		$id_user = $user['id_user'];
		//$browser = get_browser(null, true);
		$browser = new \Browser();        
		$nama_browser = $browser->getBrowser()." ".$browser->getVersion()." (".$browser->getPlatform().")";
		$ip = Yii::$app->request->remoteIP;
		$tanggal = date('Y-m-d');
		$jam = date('H:i:s');
		
		//$params = [1 => $id];
		//$connection = Yii::$app->db->createCommand('delete from member_modul_kat where id_modul_kat=?')->bindValues($params)->query();
		$connection = Yii::$app->db->createCommand()->insert('notifikasi', [
			'id_user' => $id_user,
			'modul' => $nama_log,
			'variabel' => $variabel,
			'ip' => $ip,
			'browser' => $nama_browser,
			'tanggal' => $tanggal,
			'jam' => $jam,
		])->execute();
		
	}

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'word' => 'Word',
            'lang' => 'Language',
        ];
    }
}
