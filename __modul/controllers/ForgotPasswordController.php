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
        $frontendUrl = Yii::$app->params['frontend_url'] ?? 'http://localhost:3000';
        return $this->redirect(rtrim($frontendUrl, '/') . '/forgot-password');
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

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->resetPassword()) {
                Yii::$app->session->setFlash('success', 
                    'Password berhasil diubah! Silakan login dengan password baru Anda.');
                return $this->redirect(['/site/login']);
            } else {
                Yii::$app->session->setFlash('error', 
                    'Gagal mengubah password. Silakan coba lagi.');
            }
        }

        return $this->render('verify', [
            'model' => $model,
            'email' => $email,
        ]);
    }
}
