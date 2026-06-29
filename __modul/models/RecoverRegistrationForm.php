<?php

namespace app\models;

use Yii;
use yii\base\Model;

class RecoverRegistrationForm extends Model
{
    public $email;

    public function rules()
    {
        return [
            ['email', 'required', 'message' => 'Email harus diisi.'],
            ['email', 'email', 'message' => 'Format email tidak valid.'],
            ['email', 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => 'Email',
        ];
    }

    /**
     * Cari registrasi berdasarkan email yang belum verified
     */
    public function findPendingRegistration(): ?UserRegistration
    {
        if (!$this->validate()) {
            return null;
        }

        $email = trim($this->email);
        return UserRegistration::find()
            ->where(['email' => $email])
            ->andWhere(['status' => UserRegistration::STATUS_EMAIL_PENDING])
            ->one();
    }
}
