<?php

namespace app\controllers;

use Yii;
use app\controllers\BaseController;
use yii\web\NotFoundHttpException;
use app\models\ForgotPasswordForm;
use app\models\ResetPasswordForm;

/**
 * ForgotPasswordController menangani proses lupa password
 */
class ForgotPasswordController extends BaseController
{
    /**
     * Disable CSRF validation untuk public pages
     */

    /**
     * Step 1: Request password reset (input email & kirim OTP)
     */
    public function actionRequest()
    {
        // Jika sudah login, redirect ke dashboard
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/site/index']);
        }

        $model = new ForgotPasswordForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendPasswordResetOtp()) {
                Yii::$app->session->setFlash('success', 'Kode OTP telah dikirim ke email Anda.');
                return $this->redirect(['verify', 'email' => $model->email]);
            } else {
                Yii::$app->session->setFlash('error', 'Gagal mengirim kode OTP. Silakan coba lagi.');
            }
        }

        return $this->render('request', [
            'model' => $model,
        ]);
    }

    /**
     * Step 2: Verify OTP & Reset Password
     */
    public function actionVerify($email = null)
    {
        // Jika sudah login, redirect ke dashboard
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/site/index']);
        }

        // Validasi email dari query string
        if (!$email) {
            throw new NotFoundHttpException('Email parameter diperlukan');
        }

        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new NotFoundHttpException('Format email tidak valid');
        }

        $model = new ResetPasswordForm();
        $model->email = $email;

        if ($model->load(Yii::$app->request->post())) {
            $model->email = $email; // Pastikan email dari GET parameter tetap tersimpan
            if ($model->validate()) {
                if ($model->resetPassword()) {
                    Yii::$app->session->setFlash('success', 
                        'Password berhasil diubah! Silakan login dengan password baru Anda.');
                    return $this->redirect(['/site/login']);
                } else {
                    Yii::$app->session->setFlash('error', 
                        'Gagal mengubah password. Silakan coba lagi.');
                }
            }
        }

        return $this->render('verify', [
            'model' => $model,
            'email' => $email,
        ]);
    }
}
