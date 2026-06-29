<?php

namespace app\models;

use yii\base\Model;

class VerifyEmailForm extends Model
{
    public $otp;

    public function rules()
    {
        return [
            ['otp', 'required'],
            ['otp', 'match', 'pattern' => '/^\d{4}$/', 'message' => 'OTP harus 4 digit angka.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'otp' => 'Kode OTP',
        ];
    }
}
