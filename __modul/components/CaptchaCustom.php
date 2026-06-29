<?php
namespace app\components;

use Yii;
use yii\captcha\CaptchaAction;
use yii\web\Response;

/* 
 -- How To Setup
1. Di Model : 
    public $verifyCode;
    public function rules()
    {
        [['verifyCode'], 'required'],
        ['verifyCode', function($attribute){
            if (!\app\components\CaptchaCustom::validateInput($this->$attribute, true)) {
                $this->addError($attribute, 'Kode verifikasi salah atau kedaluwarsa.');
            }
        }],
    }

2. Controller/siteController.php
   public function actions()
    {
        return [
            
            'captcha' => [
                'class' => \app\components\CaptchaCustom::class,
                // opsional override:
                'minLength' => 5, 'maxLength' => 6, 'width' => 140, 'height' => 48,
            ],
        ];
    }

3. Di View/Form 
    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => false,
        'validateOnBlur' => false,
        'validateOnChange' => false,
        ); ?>
    <div class="form-group gap-2 text-left" style="margin-bottom:8px;">
        <div class="col-sm-12">
            <img id="capimg"
            src="<?= Url::to(['site/captcha', 't' => time()]) ?>"
            alt="captcha"
            style="border-radius:6px;box-shadow:0 0 0 1px #ddd;width:50%;">
        </div>
        

        <div class="col-sm-12">	
            <a href="#" onclick="return (function(el){ el.src = '<?= Url::to(['site/captcha']) ?>' + '?refresh=1&t=' + Date.now(); return false; })(document.getElementById('capimg'))">
                ↻ Reload
            </a>
        </div>
    </div>

    <?= $form->field($model, 'verifyCode', [
        'template' => "{label}\n<div class='col-sm-12 '>{input}{hint}{error}</div>",
            'labelOptions' => [ 'class' => 'col-sm-12' ]
    ])->textInput(['placeholder'=>'Masukkan kode di atas'])->label(false) ?>

    
4. Pastiin di controller tidak double validasi

    public function actionLogin()
    {
        if (!Yii::$app->user->getIsGuest()) {
            return $this->goHome();
        }
        //print_r($request);
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) { // <— HAPUS validate()
            $model->catat_log('Login', "");
            return $this->goBack();
        }

        
        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

*/

class CaptchaCustom extends CaptchaAction
{
    const SESSION_KEY = '_gd_captcha_code';

    public $minLength = 4;
    public $maxLength = 5;
    public $width     = 140;
    public $height    = 48;

    public $bgColor   = [245,247,250]; // RGB
    public $textColor = [30,50,90];    // RGB
    public $noiseDots = 200;
    public $noiseLines = 20;
    public $caseInsensitive = true;

    // public function run()
    // {
    //     // 1) Generate code & save to session
    //     $code = $this->generateCode();
        
    //     $code_enc = self::encrypt_aes128cbc($code);
    //     //Yii::$app->session->set(self::SESSION_KEY, $this->caseInsensitive ? strtolower($code) : $code);
    //     Yii::$app->session->set(self::SESSION_KEY, $this->caseInsensitive ? $code_enc : $code_enc);

    //     // 2) Build image with GD built-in font (no TTF)
    //     $im = imagecreatetruecolor($this->width, $this->height);

    //     $bg  = imagecolorallocate($im, ...$this->bgColor);
    //     imagefilledrectangle($im, 0, 0, $this->width, $this->height, $bg);

    //     // Noise lines (tipis)
    //     for ($i = 0; $i < $this->noiseLines; $i++) {
    //         $c = imagecolorallocatealpha($im, 50 + rand(0,50), 50 + rand(0,50), 50 + rand(0,50), 90);
    //         imageline(
    //             $im,
    //             rand(0,$this->width), rand(0,$this->height),
    //             rand(0,$this->width), rand(0,$this->height),
    //             $c
    //         );
    //     }

    //     // Noise dots
    //     for ($i = 0; $i < $this->noiseDots; $i++) {
    //         $c = imagecolorallocatealpha($im, 60 + rand(0,120), 60 + rand(0,120), 60 + rand(0,120), 100);
    //         imagesetpixel($im, rand(0,$this->width-1), rand(0,$this->height-1), $c);
    //     }

    //     // Text (GD font built-in)
    //     $tc = imagecolorallocate($im, ...$this->textColor);
    //     $font = 5; // largest built-in
    //     $charW = imagefontwidth($font);
    //     $charH = imagefontheight($font);

    //     $totalW = $charW * strlen($code) + (strlen($code)-1)*4;
    //     $x = (int)(($this->width - $totalW)/2);
    //     $y = (int)(($this->height - $charH)/2);

    //     for ($i=0; $i<strlen($code); $i++) {
    //         imagestring($im, $font, $x, $y + rand(-2,2), $code[$i], $tc);
    //         $x += $charW + 4;
    //     }

    //     // 3) Output PNG
    //     $response = Yii::$app->response;
    //     $response->format = Response::FORMAT_RAW;
    //     $response->headers->set('Content-Type', 'image/png');
    //     $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    //     $response->headers->set('Pragma', 'no-cache');
    //     $response->headers->set('Expires', '0');

    //     ob_start();
    //     imagepng($im);
    //     $data = ob_get_clean();
    //     imagedestroy($im);
    //     return $data;
    // }

    public function run()
    {
        $request = Yii::$app->request;
        $session = Yii::$app->session;

        // ambil dari session kalau sudah ada
        $stored = $session->get(self::SESSION_KEY);
        $needRefresh = (int)$request->get('refresh', 0) === 1;

        if ($stored !== null && !$needRefresh) {
            // sudah ada di session & tidak minta refresh → pakai kode lama
            $code = self::decrypt_str_aes128cbc($stored);
        } else {
            // generate kode baru
            $code = $this->generateCode();
            $code_enc = self::encrypt_aes128cbc($code);
            $session->set(self::SESSION_KEY, $code_enc);
        }

        // 2) Build image
        $im = imagecreatetruecolor($this->width, $this->height);

        $bg  = imagecolorallocate($im, ...$this->bgColor);
        imagefilledrectangle($im, 0, 0, $this->width, $this->height, $bg);

        // Noise lines
        for ($i = 0; $i < $this->noiseLines; $i++) {
            $c = imagecolorallocatealpha($im, 50 + rand(0,50), 50 + rand(0,50), 50 + rand(0,50), 90);
            imageline(
                $im,
                rand(0,$this->width), rand(0,$this->height),
                rand(0,$this->width), rand(0,$this->height),
                $c
            );
        }

        // Noise dots
        for ($i = 0; $i < $this->noiseDots; $i++) {
            $c = imagecolorallocatealpha($im, 60 + rand(0,120), 60 + rand(0,120), 60 + rand(0,120), 100);
            imagesetpixel($im, rand(0,$this->width-1), rand(0,$this->height-1), $c);
        }

        // Text
        $tc = imagecolorallocate($im, ...$this->textColor);
        $font = 5;
        $charW = imagefontwidth($font);
        $charH = imagefontheight($font);

        $totalW = $charW * strlen($code) + (strlen($code)-1)*4;
        $x = (int)(($this->width - $totalW)/2);
        $y = (int)(($this->height - $charH)/2);

        for ($i=0; $i<strlen($code); $i++) {
            imagestring($im, $font, $x, $y + rand(-2,2), $code[$i], $tc);
            $x += $charW + 4;
        }

        // Output PNG
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'image/png');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        ob_start();
        imagepng($im);
        $data = ob_get_clean();
        imagedestroy($im);
        return $data;
    }


    protected function generateCode(): string
    {
        $chars = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
        $len = random_int($this->minLength, $this->maxLength);
        $s = '';
        for ($i=0; $i<$len; $i++) {
            $s .= $chars[random_int(0, strlen($chars)-1)];
        }
        return $s;
    }

    // Helper untuk validasi di Model
    public static function validateInput($input, $caseInsensitive = true): bool
    {
        $sess = Yii::$app->session->get(self::SESSION_KEY);
        // sekali pakai
        Yii::$app->session->remove(self::SESSION_KEY);
        if ($sess === null) return false;
        
       
        $sess = self::decrypt_str_aes128cbc($sess);
        if ($caseInsensitive) {
            return strtolower((string)$input) === strtolower((string)$sess);
        }
        return (string)$input === (string)$sess;
    }

    protected static function encrypt_aes128cbc($data, $key="K0Q0T2kgHFP6Kr64GDER", $iv="QI0Ce9yBtr"){

        $key1 = substr(sha1($key), 0,16);
        $iv1 = substr(sha1($iv), 0,16);
        $hasil = openssl_encrypt($data, "AES-256-CBC", $key1, true, $iv1);

        return base64_encode($hasil);
    }

    protected static function decrypt_str_aes128cbc($data, $key="K0Q0T2kgHFP6Kr64GDER", $iv="QI0Ce9yBtr"){

        $key1 = substr(sha1($key), 0,16);
        $iv1 = substr(sha1($iv), 0,16);
        
        $encrypted_data = base64_decode($data);	
        //echo "HASIL ".$encrypted_data;
        $hasil = openssl_decrypt($encrypted_data, "AES-256-CBC", $key1, OPENSSL_RAW_DATA, $iv1);
        
        return $hasil;
        //return json_decode($hasil);
        //return base64_decode($hasil);
    }
}
