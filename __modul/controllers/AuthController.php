<?php

namespace app\controllers;

use Yii;

/**
 * AuthController — SSO redirect dari backend ke dashboard Next.js.
 *
 * Alur:
 *   1. User login ke backend (Yii2 session)
 *   2. Klik tombol "Lihat Dashboard"
 *   3. actionSso() dipanggil → buat JWT + base64 user → redirect ke /sso di Next.js
 *   4. Next.js /sso page decode token → simpan ke localStorage → redirect ke /
 */
class AuthController extends BaseController
{
    /**
     * SSO redirect ke dashboard Next.js.
     * Hanya bisa diakses user yang sudah login.
     */
    public function actionSso()
    {
        // Cek login
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;

        // Kumpulkan data user
        $userId      = (int) $user->getId();
        $username    = (string) $this->getUserAttribute($user, 'username', '');
        $email       = (string) $this->getUserAttribute($user, 'email', '');
        $namaLengkap = (string) $this->getUserAttribute($user, 'nama_lengkap', '');
        $levelId     = $user->getIdUserLevel();
        $levelName   = $this->getUserLevelName($user);
        $scope       = $this->buildUserWilayahScope($user);

        // Buat JWT token (expire 24 jam)
        $secret  = $_ENV['JWT_SECRET'] ?? 'kemkes_sipkk_jwt_secret_key_2026';
        $payload = [
            'iss'           => 'sipkk-backend',
            'iat'           => time(),
            'exp'           => time() + 86400,
            'sub'           => $userId,
            'username'      => $username,
            'email'         => $email,
            'level_user_id' => $levelId,
            'level_name'    => $levelName,
            'wilayah_scope' => $scope,
        ];
        $token = $this->generateJwt($payload, $secret);

        // Encode data user ke base64 untuk dikirim ke SSO page
        $userPayload = [
            'id_user'       => $userId,
            'username'      => $username,
            'email'         => $email,
            'nama_lengkap'  => $namaLengkap,
            'level_user_id' => $levelId,
            'level_name'    => $levelName,
            'wilayah_scope' => $scope,
        ];
        $userBase64 = base64_encode(json_encode($userPayload, JSON_UNESCAPED_UNICODE));

        // Redirect ke dashboard /sso dengan token & data user
        $frontendUrl = rtrim(Yii::$app->params['frontend_url'] ?? 'https://dashboard-eoc.vercel.app', '/');
        $ssoUrl      = $frontendUrl . '/sso?token=' . urlencode($token) . '&user=' . urlencode($userBase64);

        return $this->redirect($ssoUrl);
    }
}
