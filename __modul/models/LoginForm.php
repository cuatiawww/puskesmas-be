<?php

namespace app\models;

use Yii;
use yii\base\Model;

use app\models\Browser;
use app\models\data_dmt\DataDmt;
use app\models\data_user\DataUserModel;
use app\models\profesi\DmtProfesi;
use app\models\PersonilUser;

class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;
	
	public $captcha;
	
	public $reCaptcha;

     public $verifyCode;


    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
           
             [['verifyCode'], 'required'],
            ['verifyCode', function($attribute){
                if (!\app\components\CaptchaCustom::validateInput($this->$attribute, true)) {
                    $this->addError($attribute, 'Kode Capcha salah atau kadaluarsa.');
                }
            }],
			
		  
			
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();		
            
            if(!$user){
                    \Yii::$app->session['error_login'] = "1";
                    \Yii::$app->session['error_login_message'] = "Maaf Username Anda Salah atau Tidak ditemukan, Hubungi Call Center 0811 163 119 atau Halo Kemkes Untuk Pertanyaan Lebih Lanjut";
                    $this->addError($attribute, 'Username Atau Password Anda Tidak Sesuai.');
            } else{
                if ($this->isInactiveUser($user)) {
                    \Yii::$app->session['error_login'] = "1";
                    \Yii::$app->session['error_login_message'] = "Akun Anda belum aktif atau masih menunggu approval Admin Pusat.";
                    $this->addError($attribute, 'Akun Anda belum aktif atau masih menunggu approval.');
                    return;
                }
            
                if (!$user->validatePassword($this->password)) {
                    \Yii::$app->session['error_login'] = "1";
                    \Yii::$app->session['error_login_message'] = "Maaf Password Anda Salah, Hubungi Call Center 0811 163 119 atau Halo Kemkes Untuk Pertanyaan Lebih Lanjut";
                    $this->addError($attribute, 'Username Atau Password Anda Tidak Sesuai.');
                } 
            }
           
        }
    }

    protected function isInactiveUser($user): bool
    {
        if ($user && $user->hasAttribute('is_active')) {
            $value = $user->getAttribute('is_active');
            if ($value === false || $value === 0 || (string) $value === '0') {
                return true;
            }
        }

        if ($user && $user->hasAttribute('status')) {
            $value = $user->getAttribute('status');
            if ($value === false || $value === 0 || (string) $value === '0') {
                return true;
            }
        }

        return false;
    }

    public function catat_log($nama_log, $variabel=NULL){
            // $user = $this->getUser();
            // if(!$user){
            //     $user = Yii::$app->user->identity;
            // }
            
            // $id_user = $user['id_user'];
            // $browser = new \Browser();
            
            // $nama_browser = $browser->getBrowser()." ".$browser->getVersion()." (".$browser->getPlatform().")";
            // $ip = Yii::$app->request->remoteIP;
            // $tanggal = date('Y-m-d');
            // $jam = date('H:i:s');
            
            // //$params = [1 => $id];
            // //$connection = Yii::$app->db->createCommand('delete from member_modul_kat where id_modul_kat=?')->bindValues($params)->query();
            // $connection = Yii::$app->db->createCommand()->insert('notifikasi', [
            //     'id_user' => $id_user,
            //     'modul' => $nama_log,
            //     'variabel' => $variabel,
            //     'ip' => $ip,
            //     'browser' => $nama_browser,
            //     'tanggal' => $tanggal,
            //     'jam' => $jam,
            // ])->execute();
        
		
	}

    public function login()
    {
        if ($this->validate()) {		
            
			
			
             
             $this->catat_log("Login");
            //  print_r($user);
            //  ///kalau mau login by level
            //  //Yii::$app->session['level'] = $user->id_user_level;
            //  exit;
            
             return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
            
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        ///kalau mau login dari tabel lain    
        // if (!$this->_user) {
        //     $this->_user = PersonilUser::findByUsername($this->username);
        // }
		
        return $this->_user;
    }
}
