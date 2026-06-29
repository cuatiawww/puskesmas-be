<?php
namespace app\components;

use app\models\badword\BadwordModel;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\web\UploadedFile;

class Helper extends Component
{
    public function MyFunction($param1,$param2){
        return $param1+$param2; // (:)
    }
 
    public function encrypt_aes128cbc($data, $key="K0Q0T2kgHFP6Kr64GDER", $iv="QI0Ce9yBtr"){

        $key1 = substr(sha1($key), 0,16);
        $iv1 = substr(sha1($iv), 0,16);
        $hasil = openssl_encrypt($data, "AES-256-CBC", $key1, true, $iv1);

        return base64_encode($hasil);
    }

    public function decrypt_aes128cbc($data, $key="K0Q0T2kgHFP6Kr64GDER", $iv="QI0Ce9yBtr"){

        $key1 = substr(sha1($key), 0,16);
        $iv1 = substr(sha1($iv), 0,16);
        
        $encrypted_data = base64_decode($data);	
        //echo "HASIL ".$encrypted_data;
        $hasil = openssl_decrypt($encrypted_data, "AES-256-CBC", $key1, OPENSSL_RAW_DATA, $iv1);
        
        return json_decode($hasil);
        //return base64_decode($hasil);
    }

     public function decrypt_aes128cbc_str($data, $key="K0Q0T2kgHFP6Kr64GDER", $iv="QI0Ce9yBtr"){

        $key1 = substr(sha1($key), 0,16);
        $iv1 = substr(sha1($iv), 0,16);
        
        $encrypted_data = base64_decode($data);	
        //echo "HASIL ".$encrypted_data;
        $hasil = openssl_decrypt($encrypted_data, "AES-256-CBC", $key1, OPENSSL_RAW_DATA, $iv1);
        return $hasil;
        //return base64_decode($hasil);
    }

    public function getDataURI($imagePath) {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->file($imagePath);
                                    //return 'data:'.$type.';base64,'.base64_encode(file_get_contents($imagePath));
        return base64_encode(file_get_contents($imagePath));
    }

    public function generate_keyword($string, $lang)
    {
        mb_internal_encoding('UTF-8');
        $stopwords = array();
        if($lang == 'id'){
            $badword = BadwordModel::find()->where(['lang' => 'Id'])->asArray()->all();
            foreach($badword as $key=>$val){
                $stopwords[] = $val['word'];
            }
        }elseif($lang == 'eng'){
            $badword = BadwordModel::find()->where(['lang' => 'Eng'])->asArray()->all();
            foreach($badword as $key=>$val){
                $stopwords[] = $val['word'];
            }
        }
       
        $string = preg_replace('/[\pP]/u', '', trim(preg_replace('/\s\s+/iu', '', mb_strtolower($string))));
        $matchWords = array_filter(explode(' ',$string) , function ($item) use ($stopwords) { return !($item == '' || in_array($item, $stopwords) || mb_strlen($item) <= 2 || is_numeric($item));});
        $wordCountArr = array_count_values($matchWords);
        arsort($wordCountArr);
        $array =  array_keys(array_slice($wordCountArr, 0, 10));

        if($array !=NULL){
            return implode(",", $array);
        }else{
            return "";
        }

    }

    public function limit_kata($kata, $n) {
        $a = explode(' ', $kata);
        return implode(' ', array_slice($a, 0, $n));
      }


      public function crypt_str() {
        $crypt = new classVigenere('multiwebsolusindo.com');
        return $crypt;
    }

    
}