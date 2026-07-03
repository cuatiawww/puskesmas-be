<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use app\models\User;
use app\services\WilayahService;
use app\models\UserRegistration;

/**
 * ApiController consolidates all AJAX and frontend endpoints for the Next.js application.
 */
class ApiController extends BaseController
{
    /**
     * Override actions() untuk mendaftarkan action dengan nama yang mengandung hyphen.
     * Yii2 mendukung hyphen di action ID tapi hanya jika di-register via actions() map.
     */
    public function actions()
    {
        return [
            // Tidak ada inline actions khusus; semua dihandle oleh method action*
        ];
    }

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $response = Yii::$app->response;
        $origin = Yii::$app->request->headers->get('Origin') ?? $_SERVER['HTTP_ORIGIN'] ?? '';
        if (!empty($origin)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        } else {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response->headers->set('Access-Control-Max-Age', '86400');

        if (Yii::$app->request->isOptions) {
            $response->statusCode = 204;
            $response->send();
            Yii::$app->end();
        }

        return parent::beforeAction($action);
    }

    protected function csrfExemptActions(): array
    {
        return [
            'change-password', 
            'update-profile',
            'login', 
            'register', 
            'forgot-password-request', 
            'forgot-password-verify', 
            'regions',
            'search-region',
            'verify-register-otp',
            'resend-register-otp',
            'captcha',
            'bencana-stats',
            'wilayah-geojson',
            'dashboard-stats',
            'dashboard-charts',
            'top-diseases',
            'system-settings',
        ];
    }

    public function isActionPublic(string $actionId): bool
    {
        return in_array($actionId, [
            'login', 'register', 'forgot-password-request', 'forgot-password-verify', 'regions', 'search-region',
            'verify-register-otp', 'resend-register-otp', 'captcha', 'change-password', 'update-profile',
            'bencana-stats',
            'bencanastats',
            'wilayah-geojson',
            'wilayahgeojson',
            'searchregion',
            'dashboard-stats',
            'dashboardstats',
            'dashboard-charts',
            'dashboardcharts',
            'top-diseases',
            'topdiseases',
            'system-settings',
            'systemsettings',
        ], true);
    }

    public function actionSystemSettings()
    {
        $settings = \app\models\SystemSetting::find()->all();
        $data = [];
        foreach ($settings as $setting) {
            $value = $setting->value;
            if ($setting->type === 'image' && !empty($value)) {
                if (!preg_match('~^(https?:)?//~i', $value)) {
                    $baseUrl = rtrim(Yii::$app->params['base_url'] ?? '', '/');
                    $value = $baseUrl . '/' . ltrim($value, '/');
                }
                if (strpos($value, '//') === 0) {
                    $protocol = Yii::$app->request->isSecureConnection ? 'https:' : 'http:';
                    $value = $protocol . $value;
                }
            }
            $data[$setting->key] = $value;
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * API Login endpoint
     * POST: username, password, captcha_key, captcha_value
     */
    public function actionLogin()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        $req = Yii::$app->request;
        if (!$req->isPost) {
            $response->statusCode = 405;
            return ['success' => false, 'message' => 'Metode request tidak diizinkan. Gunakan POST.'];
        }

        $bodyParams = $req->getBodyParams();
        $rawJson = json_decode((string) $req->getRawBody(), true);
        $rawJson = is_array($rawJson) ? $rawJson : [];

        $username = $req->post('username') ?? ($bodyParams['username'] ?? ($rawJson['username'] ?? null));
        $password = $req->post('password') ?? ($bodyParams['password'] ?? ($rawJson['password'] ?? null));
        $captchaKey = $req->post('captcha_key') ?? ($bodyParams['captcha_key'] ?? ($rawJson['captcha_key'] ?? null));
        $captchaValue = $req->post('captcha_value') ?? ($bodyParams['captcha_value'] ?? ($rawJson['captcha_value'] ?? null));

        if (empty($username) || empty($password)) {
            $response->statusCode = 400;
            return ['success' => false, 'message' => 'Username dan password wajib diisi.'];
        }

        $isDev = YII_ENV === 'dev' || YII_DEBUG;
        if (!$isDev) {
            if (empty($captchaKey) || empty($captchaValue)) {
                $response->statusCode = 400;
                return ['success' => false, 'message' => 'Captcha wajib diisi.'];
            }

            if (!$this->validateCaptcha($captchaKey, $captchaValue)) {
                $response->statusCode = 400;
                return ['success' => false, 'message' => 'Jawaban Captcha salah atau sudah kadaluarsa. Silakan coba lagi.'];
            }
        }

        $user = User::findByUsername($username);
        if (!$user) {
            $response->statusCode = 401;
            return ['success' => false, 'message' => 'Username atau password salah.'];
        }

        $isActive = true;
        if ($user->hasAttribute('is_active')) {
            $isActive = (bool) $user->getAttribute('is_active');
        } elseif ($user->hasAttribute('status')) {
            $isActive = (int) $user->getAttribute('status') === 1;
        }

        if (!$isActive) {
            $response->statusCode = 403;
            return ['success' => false, 'message' => 'Akun Anda belum aktif atau ditangguhkan.'];
        }

        if (!$user->validatePassword($password)) {
            $response->statusCode = 401;
            return ['success' => false, 'message' => 'Username atau password salah.'];
        }

        $userId = (int) $user->getId();
        $userEmail = (string) $this->getUserAttribute($user, 'email', '');
        $userFullName = (string) $this->getUserAttribute($user, 'nama_lengkap', '');
        $levelUserId = $user->getIdUserLevel();
        $levelName = $this->getUserLevelName($user);
        $wilayahScope = $this->buildUserWilayahScope($user);

        $secret = $_ENV['JWT_SECRET'] ?? 'kemkes_puskesmas_jwt_secret_key_2026';
        $payload = [
            'iss' => 'puskesmas-backend',
            'iat' => time(),
            'exp' => time() + (3600 * 24), // Expire in 24 hours
            'sub' => $userId,
            'username' => (string) $this->getUserAttribute($user, 'username', ''),
            'email' => $userEmail,
            'level_user_id' => $levelUserId,
            'level_name' => $levelName,
            'wilayah_scope' => $wilayahScope,
        ];

        $token = $this->generateJwt($payload, $secret);

        $regDetails = null;
        $registration = UserRegistration::findOne(['user_id' => $userId]);
        if ($registration) {
            $wilayah = new WilayahService();
            $provName = $wilayah->findProvinsiName($registration->provinsi_id ? (string)$registration->provinsi_id : null);
            $kabName = $wilayah->findKabupatenName($registration->kabupaten_id ? (string)$registration->kabupaten_id : null);
            
            $regDetails = [
                'kategori_akses' => $registration->kategori_akses,
                'nama_institusi' => $registration->nama_institusi,
                'pekerjaan_posisi' => $registration->pekerjaan_posisi,
                'tujuan_akses' => $registration->tujuan_akses,
                'tujuan_akses_lainnya' => $registration->tujuan_akses_lainnya,
                'alamat_user' => $registration->alamat_user,
                'provinsi_id' => $registration->provinsi_id,
                'provinsi_name' => $provName,
                'kabupaten_id' => $registration->kabupaten_id,
                'kabupaten_name' => $kabName,
            ];
        }

        return [
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id_user' => $userId,
                'username' => (string) $this->getUserAttribute($user, 'username', ''),
                'email' => $userEmail,
                'nama_lengkap' => $userFullName,
                'no_telpon' => (string) $this->getUserAttribute($user, 'no_telpon', ''),
                'level_user_id' => $levelUserId,
                'level_name' => $levelName,
                'wilayah_scope' => $wilayahScope,
                'registration_details' => $regDetails,
            ]
        ];
    }



    /**
     * API Change Password endpoint
     */
    public function actionChangePassword()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request;

        if (!$req->isPost) {
            return ['success' => false, 'message' => 'Invalid request method'];
        }

        $tokenUser = $this->getUserFromToken();
        $userId = $tokenUser ? $tokenUser->id : ($req->post('user_id') ?? Yii::$app->user->id ?? null);
        $old = $req->post('old_password');
        $new = $req->post('new_password');

        if (empty($userId) || $old === null || $new === null) {
            return ['success' => false, 'message' => 'Parameter tidak lengkap'];
        }

        if (strlen($new) < 6) {
            return ['success' => false, 'message' => 'Password baru minimal 6 karakter'];
        }

        $user = User::findOne($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User tidak ditemukan'];
        }

        if (!$user->validatePassword($old)) {
            return ['success' => false, 'message' => 'Password lama salah'];
        }

        $user->password = Yii::$app->getSecurity()->generatePasswordHash($new);

        if ($user->save(false)) {
            if (!Yii::$app->user->isGuest && (int)Yii::$app->user->id === (int)$user->getId()) {
                Yii::$app->user->logout(false);
            }
            return ['success' => true, 'message' => 'Password berhasil diubah'];
        }

        return ['success' => false, 'message' => 'Gagal menyimpan password baru'];
    }

    /**
     * API Update Profile endpoint
     * POST: username, nama_lengkap, email, no_telpon
     */
    public function actionUpdateProfile()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request;

        if (!$req->isPost) {
            return ['success' => false, 'message' => 'Invalid request method'];
        }

        $user = $this->getUserFromToken();
        if (!$user) {
            return ['success' => false, 'message' => 'Silakan login terlebih dahulu'];
        }

        $bodyParams = $req->getBodyParams();
        $rawJson = json_decode((string) $req->getRawBody(), true);
        $rawJson = is_array($rawJson) ? $rawJson : [];

        $username = $req->post('username') ?? ($bodyParams['username'] ?? ($rawJson['username'] ?? null));
        $namaLengkap = $req->post('nama_lengkap') ?? ($bodyParams['nama_lengkap'] ?? ($rawJson['nama_lengkap'] ?? null));
        $email = $req->post('email') ?? ($bodyParams['email'] ?? ($rawJson['email'] ?? null));
        $noTelpon = $req->post('no_telpon') ?? ($bodyParams['no_telpon'] ?? ($rawJson['no_telpon'] ?? null));

        if (empty($username) || empty($namaLengkap) || empty($email)) {
            return ['success' => false, 'message' => 'Username, Nama Lengkap, dan Email wajib diisi.'];
        }

        // Validate username uniqueness (excluding current user)
        $existingUser = User::find()->where(['username' => $username])->andWhere(['!=', 'id', $user->id])->one();
        if ($existingUser) {
            return ['success' => false, 'message' => 'Username sudah digunakan oleh akun lain.'];
        }

        // Validate email uniqueness (excluding current user)
        $existingEmail = User::find()->where(['email' => $email])->andWhere(['!=', 'id', $user->id])->one();
        if ($existingEmail) {
            return ['success' => false, 'message' => 'Email sudah digunakan oleh akun lain.'];
        }

        $user->username = $username;
        $user->nama_lengkap = $namaLengkap;
        $user->email = $email;
        $user->no_telpon = $noTelpon;

        if ($user->save(false)) {
            // Re-generate token with updated user information
            $userId = (int) $user->getId();
            $levelUserId = $user->getIdUserLevel();
            $levelName = $this->getUserLevelName($user);
            $wilayahScope = $this->buildUserWilayahScope($user);

            $secret = $_ENV['JWT_SECRET'] ?? 'kemkes_puskesmas_jwt_secret_key_2026';
            $payload = [
                'iss' => 'puskesmas-backend',
                'iat' => time(),
                'exp' => time() + (3600 * 24), // Expire in 24 hours
                'sub' => $userId,
                'username' => $user->username,
                'email' => $user->email,
                'level_user_id' => $levelUserId,
                'level_name' => $levelName,
                'wilayah_scope' => $wilayahScope,
            ];

            $token = $this->generateJwt($payload, $secret);

            $regDetails = null;
            $registration = UserRegistration::findOne(['user_id' => $userId]);
            if ($registration) {
                $wilayah = new WilayahService();
                $provName = $wilayah->findProvinsiName($registration->provinsi_id ? (string)$registration->provinsi_id : null);
                $kabName = $wilayah->findKabupatenName($registration->kabupaten_id ? (string)$registration->kabupaten_id : null);
                
                $regDetails = [
                    'kategori_akses' => $registration->kategori_akses,
                    'nama_institusi' => $registration->nama_institusi,
                    'pekerjaan_posisi' => $registration->pekerjaan_posisi,
                    'tujuan_akses' => $registration->tujuan_akses,
                    'tujuan_akses_lainnya' => $registration->tujuan_akses_lainnya,
                    'alamat_user' => $registration->alamat_user,
                    'provinsi_id' => $registration->provinsi_id,
                    'provinsi_name' => $provName,
                    'kabupaten_id' => $registration->kabupaten_id,
                    'kabupaten_name' => $kabName,
                ];
            }

            return [
                'success' => true,
                'message' => 'Profil berhasil diperbarui.',
                'token' => $token,
                'user' => [
                    'id_user' => $userId,
                    'username' => $user->username,
                    'email' => $user->email,
                    'nama_lengkap' => $user->nama_lengkap,
                    'no_telpon' => $user->no_telpon,
                    'level_user_id' => $levelUserId,
                    'level_name' => $levelName,
                    'wilayah_scope' => $wilayahScope,
                    'registration_details' => $regDetails,
                ]
            ];
        }

        return ['success' => false, 'message' => 'Gagal memperbarui profil.'];
    }

    private function getUserFromToken(): ?User
    {
        $authHeader = Yii::$app->request->headers->get('Authorization');
        if (empty($authHeader)) {
            $authHeader = Yii::$app->request->get('token');
            if (empty($authHeader)) {
                return null;
            }
        } elseif (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $authHeader = $matches[1];
        }

        $secret = $_ENV['JWT_SECRET'] ?? 'kemkes_puskesmas_jwt_secret_key_2026';
        $payload = $this->decodeJwt($authHeader, $secret);
        if (!$payload) {
            $fallbackSecret = Yii::$app->request->cookieValidationKey ?: "kemkes!@#$%^&*()_api";
            $payload = $this->decodeJwt($authHeader, $fallbackSecret);
        }
        if (!$payload) {
            $payload = $this->decodeJwt($authHeader, "kemkes!@#$%^&*()");
        }

        if ($payload && isset($payload['sub'])) {
            return User::findOne((int)$payload['sub']);
        }

        return null;
    }

    /**
     * API Register endpoint
     */
    public function actionRegister()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        $req = Yii::$app->request;
        if (!$req->isPost) {
            $response->statusCode = 405;
            return ['success' => false, 'message' => 'Metode request tidak diizinkan. Gunakan POST.'];
        }

        $bodyParams = $req->getBodyParams();
        $rawJson = json_decode((string) $req->getRawBody(), true) ?? [];
        $data = array_merge($bodyParams, $rawJson);

        $captchaKey = $data['captcha_key'] ?? null;
        $captchaValue = $data['captcha_value'] ?? null;

        $isDev = YII_ENV === 'dev' || YII_DEBUG;
        if (!$isDev) {
            if (empty($captchaKey) || empty($captchaValue)) {
                $response->statusCode = 400;
                return ['success' => false, 'message' => 'Captcha wajib diisi.'];
            }

            if (!$this->validateCaptcha($captchaKey, $captchaValue)) {
                $response->statusCode = 400;
                return ['success' => false, 'message' => 'Jawaban Captcha salah atau sudah kadaluarsa. Silakan coba lagi.'];
            }
        }

        $model = new \app\models\RegisterMasyarakatForm();
        $model->scenario = \app\models\RegisterMasyarakatForm::SCENARIO_API;

        if ($model->load($data, '')) {
            $registration = $model->save();
            if ($registration) {
                return [
                    'success' => true,
                    'registration_id' => (int) $registration->id,
                    'message' => 'Pendaftaran berhasil! Kode verifikasi OTP telah dikirim ke email Anda.',
                ];
            }
        }

        $response->statusCode = 422;
        $errors = [];
        foreach ($model->getErrors() as $attribute => $messages) {
            $errors[$attribute] = implode(' ', $messages);
        }

        return [
            'success' => false,
            'message' => 'Validasi gagal.',
            'errors' => $errors,
        ];
    }

    /**
     * API Forgot Password Request endpoint
     */
    public function actionForgotPasswordRequest()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        $req = Yii::$app->request;
        if (!$req->isPost) {
            $response->statusCode = 405;
            return ['success' => false, 'message' => 'Metode request tidak diizinkan. Gunakan POST.'];
        }

        $bodyParams = $req->getBodyParams();
        $rawJson = json_decode((string) $req->getRawBody(), true) ?? [];
        $data = array_merge($bodyParams, $rawJson);

        $model = new \app\models\ForgotPasswordForm();
        if ($model->load($data, '') && $model->validate()) {
            if ($model->sendPasswordResetOtp()) {
                return [
                    'success' => true,
                    'message' => 'Kode OTP telah dikirim ke email Anda. Silakan periksa kotak masuk.',
                ];
            } else {
                $response->statusCode = 500;
                return [
                    'success' => false,
                    'message' => 'Gagal mengirim kode OTP. Silakan coba beberapa saat lagi.',
                ];
            }
        }

        $response->statusCode = 422;
        $errors = [];
        foreach ($model->getErrors() as $attribute => $messages) {
            $errors[$attribute] = implode(' ', $messages);
        }
        return [
            'success' => false,
            'message' => 'Validasi gagal.',
            'errors' => $errors,
        ];
    }

    /**
     * API Forgot Password Reset / Verify endpoint
     */
    public function actionForgotPasswordVerify()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        $req = Yii::$app->request;
        if (!$req->isPost) {
            $response->statusCode = 405;
            return ['success' => false, 'message' => 'Metode request tidak diizinkan. Gunakan POST.'];
        }

        $bodyParams = $req->getBodyParams();
        $rawJson = json_decode((string) $req->getRawBody(), true) ?? [];
        $data = array_merge($bodyParams, $rawJson);

        $model = new \app\models\ResetPasswordForm();
        if ($model->load($data, '') && $model->validate()) {
            if ($model->resetPassword()) {
                return [
                    'success' => true,
                    'message' => 'Password berhasil diubah. Silakan login kembali.',
                ];
            } else {
                $response->statusCode = 500;
                return [
                    'success' => false,
                    'message' => 'Gagal mengubah password. Silakan coba lagi.',
                ];
            }
        }

        $response->statusCode = 422;
        $errors = [];
        foreach ($model->getErrors() as $attribute => $messages) {
            $errors[$attribute] = implode(' ', $messages);
        }
        return [
            'success' => false,
            'message' => 'Validasi gagal.',
            'errors' => $errors,
        ];
    }

    /**
     * API Regions endpoint
     */
    public function actionRegions()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        $provinceId = (string)Yii::$app->request->get('province_id', '');
        $kabupatenId = (string)Yii::$app->request->get('kabupaten_id', '');
        $kecamatanId = (string)Yii::$app->request->get('kecamatan_id', '');

        try {
            $wilayah = new WilayahService();

            if ($kecamatanId !== '') {
                $data = $wilayah->getDesaOptions($kecamatanId);
            } elseif ($kabupatenId !== '') {
                $data = $wilayah->getKecamatanOptions($kabupatenId);
            } elseif ($provinceId !== '') {
                $data = $wilayah->getKabupatenOptions($provinceId);
            } else {
                $data = $wilayah->getProvinsiOptions();
            }

            return [
                'success' => true,
                'data' => $data,
            ];
        } catch (\Throwable $e) {
            $response->statusCode = 500;
            return [
                'success' => false,
                'message' => 'Gagal memuat data wilayah: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * API Search Region endpoint: searches province, kabupaten, kecamatan, and desa
     */
    public function actionSearchRegion()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        $q = Yii::$app->request->get('q', '');
        if (strlen($q) < 2) {
            return ['success' => true, 'data' => []];
        }

        $q = strtoupper(trim($q));
        $db = Yii::$app->db;

        // Check user scope to restrict suggestions if level is provincial or kabupaten
        $scope = $this->getRequestWilayahScope();
        $limitProv = null;
        $limitKab = null;

        if ($scope && isset($scope['mode'])) {
            if ($scope['mode'] === 'provinsi' && !empty($scope['provinsi']['label'])) {
                $limitProv = $db->createCommand("SELECT code FROM public.wilayah_provinsi WHERE UPPER(name) = :name", [
                    ':name' => strtoupper($scope['provinsi']['label'])
                ])->queryScalar();
            } elseif ($scope['mode'] === 'kabupaten' && !empty($scope['kabupaten']['label'])) {
                $limitKab = $db->createCommand("SELECT code FROM public.wilayah_kabupaten WHERE UPPER(name) = :name", [
                    ':name' => strtoupper($scope['kabupaten']['label'])
                ])->queryScalar();
            }
        }

        $suggestions = [];

        // 1. Search Provinces (only if not restricted to specific province or kab)
        if ($limitProv === null && $limitKab === null) {
            $provs = $db->createCommand("
                SELECT code, name FROM public.wilayah_provinsi 
                WHERE UPPER(name) LIKE :q 
                ORDER BY name ASC LIMIT 10
            ", [':q' => '%' . $q . '%'])->queryAll();

            foreach ($provs as $p) {
                $suggestions[] = [
                    'type' => 'provinsi',
                    'label' => $p['name'],
                    'province_code' => $p['code'],
                    'province_name' => $p['name'],
                ];
            }
        }

        // 2. Search Kabupaten
        $kabWhere = "WHERE UPPER(k.name) LIKE :q";
        $kabParams = [':q' => '%' . $q . '%'];
        if ($limitProv !== null) {
            $kabWhere .= " AND k.parent_code = :limit_prov";
            $kabParams[':limit_prov'] = $limitProv;
        } elseif ($limitKab !== null) {
            $kabWhere .= " AND k.code = :limit_kab";
            $kabParams[':limit_kab'] = $limitKab;
        }

        $kabs = $db->createCommand("
            SELECT k.code, k.name, p.code as prov_code, p.name as prov_name 
            FROM public.wilayah_kabupaten k
            JOIN public.wilayah_provinsi p ON p.code = k.parent_code
            {$kabWhere}
            ORDER BY k.name ASC LIMIT 15
        ", $kabParams)->queryAll();

        foreach ($kabs as $k) {
            $suggestions[] = [
                'type' => 'kabupaten',
                'label' => $k['name'] . ' (' . $k['prov_name'] . ')',
                'province_code' => $k['prov_code'],
                'province_name' => $k['prov_name'],
                'kabupaten_code' => $k['code'],
                'kabupaten_name' => $k['name'],
            ];
        }

        // 3. Search Kecamatan
        $kecWhere = "WHERE UPPER(kec.name) LIKE :q";
        $kecParams = [':q' => '%' . $q . '%'];
        if ($limitProv !== null) {
            $kecWhere .= " AND p.code = :limit_prov";
            $kecParams[':limit_prov'] = $limitProv;
        } elseif ($limitKab !== null) {
            $kecWhere .= " AND kab.code = :limit_kab";
            $kecParams[':limit_kab'] = $limitKab;
        }

        $kecs = $db->createCommand("
            SELECT kec.code, kec.name, kab.code as kab_code, kab.name as kab_name, p.code as prov_code, p.name as prov_name 
            FROM public.wilayah_kecamatan kec
            JOIN public.wilayah_kabupaten kab ON kab.code = kec.parent_code
            JOIN public.wilayah_provinsi p ON p.code = kab.parent_code
            {$kecWhere}
            ORDER BY kec.name ASC LIMIT 15
        ", $kecParams)->queryAll();

        foreach ($kecs as $kc) {
            $suggestions[] = [
                'type' => 'kecamatan',
                'label' => 'KEC. ' . $kc['name'] . ' (' . $kc['kab_name'] . ', ' . $kc['prov_name'] . ')',
                'province_code' => $kc['prov_code'],
                'province_name' => $kc['prov_name'],
                'kabupaten_code' => $kc['kab_code'],
                'kabupaten_name' => $kc['kab_name'],
                'kecamatan_code' => $kc['code'],
                'kecamatan_name' => $kc['name'],
            ];
        }

        // 4. Search Desa
        $desaWhere = "WHERE UPPER(d.name) LIKE :q";
        $desaParams = [':q' => '%' . $q . '%'];
        if ($limitProv !== null) {
            $desaWhere .= " AND p.code = :limit_prov";
            $desaParams[':limit_prov'] = $limitProv;
        } elseif ($limitKab !== null) {
            $desaWhere .= " AND kab.code = :limit_kab";
            $desaParams[':limit_kab'] = $limitKab;
        }

        $desas = $db->createCommand("
            SELECT d.code, d.name, kec.code as kec_code, kec.name as kec_name, kab.code as kab_code, kab.name as kab_name, p.code as prov_code, p.name as prov_name 
            FROM public.wilayah_desa d
            JOIN public.wilayah_kecamatan kec ON kec.code = d.parent_code
            JOIN public.wilayah_kabupaten kab ON kab.code = kec.parent_code
            JOIN public.wilayah_provinsi p ON p.code = kab.parent_code
            {$desaWhere}
            ORDER BY d.name ASC LIMIT 15
        ", $desaParams)->queryAll();

        foreach ($desas as $ds) {
            $suggestions[] = [
                'type' => 'desa',
                'label' => 'DESA/KEL. ' . $ds['name'] . ' (KEC. ' . $ds['kec_name'] . ', ' . $ds['kab_name'] . ')',
                'province_code' => $ds['prov_code'],
                'province_name' => $ds['prov_name'],
                'kabupaten_code' => $ds['kab_code'],
                'kabupaten_name' => $ds['kab_name'],
                'kecamatan_code' => $ds['kec_code'],
                'kecamatan_name' => $ds['kec_name'],
                'desa_code' => $ds['code'],
                'desa_name' => $ds['name'],
            ];
        }

        $suggestions = array_slice($suggestions, 0, 30);

        return [
            'success' => true,
            'data' => $suggestions
        ];
    }

    /**
     * API Verify OTP Registrasi endpoint
     */
    public function actionVerifyRegisterOtp()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        $req = Yii::$app->request;
        if (!$req->isPost) {
            $response->statusCode = 405;
            return ['success' => false, 'message' => 'Metode request tidak diizinkan. Gunakan POST.'];
        }

        $bodyParams = $req->getBodyParams();
        $rawJson = json_decode((string) $req->getRawBody(), true) ?? [];
        $data = array_merge($bodyParams, $rawJson);

        $registrationId = $data['registration_id'] ?? null;
        $otp = $data['otp'] ?? null;

        if (empty($registrationId) || empty($otp)) {
            $response->statusCode = 400;
            return ['success' => false, 'message' => 'Registration ID dan OTP wajib diisi.'];
        }

        $registration = \app\models\UserRegistration::findOne((int) $registrationId);
        if (!$registration) {
            $response->statusCode = 404;
            return ['success' => false, 'message' => 'Data pendaftaran tidak ditemukan.'];
        }

        if ($registration->status !== \app\models\UserRegistration::STATUS_EMAIL_PENDING) {
            return [
                'success' => true,
                'message' => 'Email Anda sudah terverifikasi sebelumnya.',
            ];
        }

        if ($registration->validateOtp($otp)) {
            if ($registration->markEmailVerified()) {
                return [
                    'success' => true,
                    'message' => 'Email berhasil diverifikasi! Pendaftaran Anda kini menunggu persetujuan Admin Pusat.',
                ];
            } else {
                $response->statusCode = 500;
                return ['success' => false, 'message' => 'Gagal memperbarui status verifikasi.'];
            }
        }

        $response->statusCode = 422;
        return [
            'success' => false,
            'message' => 'Kode OTP salah atau sudah kadaluarsa.',
        ];
    }

    /**
     * API Kirim Ulang OTP Registrasi endpoint
     */
    public function actionResendRegisterOtp()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        $req = Yii::$app->request;
        if (!$req->isPost) {
            $response->statusCode = 405;
            return ['success' => false, 'message' => 'Metode request tidak diizinkan. Gunakan POST.'];
        }

        $bodyParams = $req->getBodyParams();
        $rawJson = json_decode((string) $req->getRawBody(), true) ?? [];
        $data = array_merge($bodyParams, $rawJson);

        $registrationId = $data['registration_id'] ?? null;
        if (empty($registrationId)) {
            $response->statusCode = 400;
            return ['success' => false, 'message' => 'Registration ID wajib diisi.'];
        }

        $registration = \app\models\UserRegistration::findOne((int) $registrationId);
        if (!$registration) {
            $response->statusCode = 404;
            return ['success' => false, 'message' => 'Data pendaftaran tidak ditemukan.'];
        }

        if ($registration->status !== \app\models\UserRegistration::STATUS_EMAIL_PENDING) {
            return [
                'success' => false,
                'message' => 'Pendaftaran Anda sudah diverifikasi atau disetujui.',
            ];
        }

        if ($registration->otp_resend_count >= 10) {
            $response->statusCode = 429;
            return ['success' => false, 'message' => 'Batas kirim ulang OTP (10 kali) sudah tercapai. Hubungi admin.'];
        }

        try {
            $otp = $registration->generateOtp();
            $registration->otp_resend_count = ($registration->otp_resend_count ?? 0) + 1;

            if ($registration->save(false)) {
                if (\app\services\RegistrationEmailService::sendOtp($registration, $otp)) {
                    return [
                        'success' => true,
                        'message' => 'Kode OTP baru berhasil dikirim ke email Anda.',
                    ];
                } else {
                    $response->statusCode = 500;
                    return ['success' => false, 'message' => 'Gagal mengirim email OTP. Silakan coba lagi.'];
                }
            }
        } catch (\Throwable $e) {
            $response->statusCode = 500;
            return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }

        $response->statusCode = 500;
        return ['success' => false, 'message' => 'Gagal memperbarui data pendaftaran.'];
    }

    private function decodeJwt(string $token, string $secret): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        list($headerB64, $payloadB64, $signatureB64) = $parts;

        $expectedSignature = $this->base64UrlEncode(hash_hmac('sha256', $headerB64 . "." . $payloadB64, $secret, true));
        if ($signatureB64 !== $expectedSignature) {
            return null;
        }

        $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $payloadB64));
        $payload = json_decode($payloadJson, true);

        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    private function getRequestWilayahScope(): ?array
    {
        $authHeader = Yii::$app->request->headers->get('Authorization');
        if (empty($authHeader)) {
            $authHeader = Yii::$app->request->get('token');
            if (empty($authHeader)) {
                return null;
            }
        } elseif (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $authHeader = $matches[1];
        }

        $secret = $_ENV['JWT_SECRET'] ?? 'kemkes_puskesmas_jwt_secret_key_2026';
        $payload = $this->decodeJwt($authHeader, $secret);
        if (!$payload) {
            $fallbackSecret = Yii::$app->request->cookieValidationKey ?: "kemkes!@#$%^&*()_api";
            $payload = $this->decodeJwt($authHeader, $fallbackSecret);
        }
        if (!$payload) {
            $payload = $this->decodeJwt($authHeader, "kemkes!@#$%^&*()");
        }

        return ($payload && isset($payload['wilayah_scope'])) ? $payload['wilayah_scope'] : null;
    }



    private function cleanRegionName(string $name): string
    {
        $name = strtoupper(trim($name));
        $name = preg_replace('/^(KAB\.|KABUPATEN|KOTA)\s+/i', '', $name);
        return trim($name);
    }

    /**
     * API endpoint for fetching disaster statistics and markers.
     * ALWAYS returns JSON — never redirects, even on DB error.
     */
    public function actionBencanaStats()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        try {
            return $this->doBencanaStats();
        } catch (\Throwable $e) {
            Yii::warning('[bencana-stats] Fatal error: ' . $e->getMessage(), __METHOD__);
            $response->statusCode = 500;
            return [
                'success'       => true,
                'summary'       => ['total_bencana'=>0,'total_meninggal'=>0,'total_luka'=>0,'total_hilang'=>0,'total_pengungsi'=>0,'total_terdampak'=>0],
                'jenis_bencana' => [],
                'wilayah'       => [],
                'markers'       => [],
            ];
        }
    }


    private function doBencanaStats(): array
    {
        $db = Yii::$app->db;
        $where = [];
        $params = [];

        $scope = $this->getRequestWilayahScope();
        $reqProvince = Yii::$app->request->get('province');
        $reqKabupaten = Yii::$app->request->get('kabupaten');

        if (!empty($reqProvince)) {
            $provCleaned = $this->cleanRegionName($reqProvince);
            $where[] = "(UPPER(t.prov_single) = :prov_name OR UPPER(t.prov_single) LIKE :prov_like)";
            $params[':prov_name'] = $provCleaned;
            $params[':prov_like'] = '%' . $provCleaned . '%';

            if (!empty($reqKabupaten)) {
                $kabCleaned = $this->cleanRegionName($reqKabupaten);
                $where[] = "(UPPER(t.kab_single) = :kab_name OR UPPER(t.kab_single) LIKE :kab_like OR UPPER(t.kab_multi) = :kab_name OR UPPER(t.kab_multi) LIKE :kab_like)";
                $params[':kab_name'] = $kabCleaned;
                $params[':kab_like'] = '%' . $kabCleaned . '%';
            }
        } elseif ($scope && isset($scope['mode'])) {
            if ($scope['mode'] === 'provinsi' && !empty($scope['provinsi']['label'])) {
                $provCleaned = $this->cleanRegionName($scope['provinsi']['label']);
                $where[] = "(UPPER(t.prov_single) = :prov_name OR UPPER(t.prov_single) LIKE :prov_like)";
                $params[':prov_name'] = $provCleaned;
                $params[':prov_like'] = '%' . $provCleaned . '%';
            } elseif ($scope['mode'] === 'kabupaten' && !empty($scope['kabupaten']['label'])) {
                if (!empty($scope['provinsi']['label'])) {
                    $provCleaned = $this->cleanRegionName($scope['provinsi']['label']);
                    $where[] = "(UPPER(t.prov_single) = :prov_name OR UPPER(t.prov_single) LIKE :prov_like)";
                    $params[':prov_name'] = $provCleaned;
                    $params[':prov_like'] = '%' . $provCleaned . '%';
                }
                $kabCleaned = $this->cleanRegionName($scope['kabupaten']['label']);
                $where[] = "(UPPER(t.kab_single) = :kab_name OR UPPER(t.kab_single) LIKE :kab_like OR UPPER(t.kab_multi) = :kab_name OR UPPER(t.kab_multi) LIKE :kab_like)";
                $params[':kab_name'] = $kabCleaned;
                $params[':kab_like'] = '%' . $kabCleaned . '%';
            }
        }

        $whereStr = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

        try {
            $stats = $db->createCommand("
                SELECT
                    COUNT(t.kode_trans) as total_bencana,
                    SUM(CASE WHEN (
                        COALESCE(t.jml_meninggal, 0) > 0 OR 
                        COALESCE(t.jml_lkbrt, 0) > 0 OR 
                        COALESCE(t.jml_hilang, 0) > 0 OR 
                        COALESCE(t.jml_pengungsi, 0) > 0 OR
                        EXISTS (SELECT 1 FROM public.laporan_kejadian_faskes_rusak fr WHERE fr.id_laporan = t.kode_trans) OR
                        EXISTS (SELECT 1 FROM public.laporan_kejadian_faskes_terdampak ft WHERE ft.id_laporan = t.kode_trans) OR
                        h.status_siaga IS NOT NULL
                    ) THEN 1 ELSE 0 END) as total_krisis,
                    SUM(COALESCE(t.jml_meninggal, 0)) as total_meninggal,
                    SUM(COALESCE(t.jml_lkbrt, 0) + COALESCE(t.jml_lkringan, 0)) as total_luka,
                    SUM(COALESCE(t.jml_hilang, 0)) as total_hilang,
                    SUM(COALESCE(t.jml_pengungsi, 0)) as total_pengungsi,
                    SUM(COALESCE(t.jml_pdk_terdampak, 0)) as total_terdampak
                FROM public.transaksi_laporan t
                LEFT JOIN public.laporan_kejadian_header h ON h.id_laporan = t.kode_trans
                " . $whereStr, $params)->queryOne();

            $jenisBencanaList = $db->createCommand("
                SELECT
                    COALESCE(j.nama_bencana, CAST(j.jenis_bencana AS VARCHAR), CAST(t.kd_jenis_bencana AS VARCHAR)) as nama,
                    COUNT(t.kode_trans) as jumlah
                FROM public.transaksi_laporan t
                LEFT JOIN public.master_dt_bencana j ON CAST(j.id_master_dt_bencana AS VARCHAR) = t.kd_jenis_bencana
                " . $whereStr . "
                GROUP BY COALESCE(j.nama_bencana, CAST(j.jenis_bencana AS VARCHAR), CAST(t.kd_jenis_bencana AS VARCHAR))
                ORDER BY jumlah DESC
            ", $params)->queryAll();

            $groupColumn = "t.prov_single";
            if (!empty($reqProvince)) {
                if (!empty($reqKabupaten)) {
                    $groupColumn = "COALESCE(NULLIF(t.kecamatan, ''), 'Kecamatan Lainnya')";
                } else {
                    $groupColumn = "COALESCE(NULLIF(t.kab_single, ''), NULLIF(t.kab_multi, ''), 'Kab/Kota Lainnya')";
                }
            } elseif ($scope && isset($scope['mode'])) {
                if ($scope['mode'] === 'provinsi' && !empty($scope['provinsi']['label'])) {
                    $groupColumn = "COALESCE(NULLIF(t.kab_single, ''), NULLIF(t.kab_multi, ''), 'Kab/Kota Lainnya')";
                } elseif ($scope['mode'] === 'kabupaten' && !empty($scope['kabupaten']['label'])) {
                    $groupColumn = "COALESCE(NULLIF(t.kecamatan, ''), 'Kecamatan Lainnya')";
                }
            }

            $wilayahList = $db->createCommand("
                SELECT
                    {$groupColumn} as nama,
                    COUNT(t.kode_trans) as jumlah
                FROM public.transaksi_laporan t
                " . $whereStr . "
                GROUP BY {$groupColumn}
                ORDER BY jumlah DESC
            ", $params)->queryAll();

            $wilayahList = array_values(array_filter($wilayahList, function($item) {
                return !empty($item['nama']);
            }));

            $markersWhere = " WHERE (NULLIF(l.latitude, '') IS NOT NULL OR k.centerlat IS NOT NULL OR p.centerlat IS NOT NULL)";
            if (!empty($where)) {
                $markersWhere .= " AND " . implode(" AND ", $where);
            }

            $markers = $db->createCommand("
                SELECT
                    t.kode_trans,
                    t.tgl_kejadian,
                    COALESCE(j.nama_bencana, CAST(j.jenis_bencana AS VARCHAR), CAST(t.kd_jenis_bencana AS VARCHAR)) as jenis_bencana,
                    j.jenis_bencana as kategori_bencana,
                    j.icon as icon_file,
                    COALESCE(NULLIF(l.latitude, ''), NULLIF(k.centerlat::text, ''), p.centerlat::text) as latitude,
                    COALESCE(NULLIF(l.longitude, ''), NULLIF(k.centerlong::text, ''), p.centerlong::text) as longitude,
                    t.prov_single as provinsi,
                    COALESCE(NULLIF(t.kab_single, ''), NULLIF(t.kab_multi, '')) as kabupaten,
                    COALESCE(NULLIF(l.kecamatan, ''), t.kecamatan) as kecamatan,
                    COALESCE(NULLIF(l.nama_desa, ''), 'Desa Lainnya') as nama_desa,
                    COALESCE(NULLIF(l.topografi, ''), '-') as topografi,
                    (COALESCE(t.jml_meninggal, 0) + COALESCE(t.jml_hilang, 0) + COALESCE(t.jml_lkbrt, 0) + COALESCE(t.jml_lkringan, 0)) as total_korban,
                    CASE WHEN (
                        COALESCE(t.jml_meninggal, 0) > 0 OR 
                        COALESCE(t.jml_lkbrt, 0) > 0 OR 
                        COALESCE(t.jml_hilang, 0) > 0 OR 
                        COALESCE(t.jml_pengungsi, 0) > 0 OR
                        EXISTS (SELECT 1 FROM public.laporan_kejadian_faskes_rusak fr WHERE fr.id_laporan = t.kode_trans) OR
                        EXISTS (SELECT 1 FROM public.laporan_kejadian_faskes_terdampak ft WHERE ft.id_laporan = t.kode_trans) OR
                        h.status_siaga IS NOT NULL
                    ) THEN 1 ELSE 0 END as is_krisis
                FROM public.transaksi_laporan t
                LEFT JOIN public.master_dt_bencana j ON CAST(j.id_master_dt_bencana AS VARCHAR) = t.kd_jenis_bencana
                LEFT JOIN public.laporan_kejadian_lokasi l ON l.id_laporan = t.kode_trans
                LEFT JOIN public.laporan_kejadian_header h ON h.id_laporan = t.kode_trans
                LEFT JOIN public.peta_kab2023 k ON UPPER(k.nama_kab) = UPPER(COALESCE(NULLIF(t.kab_single, ''), NULLIF(t.kab_multi, '')))
                LEFT JOIN public.peta_provinsi2023 p ON UPPER(p.provinsi) = UPPER(t.prov_single)
                " . $markersWhere, $params)->queryAll();

            foreach ($markers as &$m) {
                $m['lat'] = (float)$m['latitude'];
                $m['lng'] = (float)$m['longitude'];
                $m['is_krisis'] = (int)($m['is_krisis'] ?? 0);
            }
            unset($m);
        } catch (\Throwable $e) {
            Yii::warning('Error querying bencana stats: ' . $e->getMessage(), __METHOD__);
            $stats = [];
            $jenisBencanaList = [];
            $wilayahList = [];
            $markers = [];
        }

        if (empty($stats) || empty($stats['total_bencana'])) {
            $stats = [
                'total_bencana' => 0,
                'total_krisis' => 0,
                'total_meninggal' => 0,
                'total_luka' => 0,
                'total_hilang' => 0,
                'total_pengungsi' => 0,
                'total_terdampak' => 0,
            ];
            $jenisBencanaList = [];
            $wilayahList = [];
            $markers = [];
        }

        return [
            'success'       => true,
            'summary'       => [
                'total_bencana'   => (int)($stats['total_bencana'] ?? 0),
                'total_krisis'    => (int)($stats['total_krisis'] ?? 0),
                'total_meninggal' => (int)($stats['total_meninggal'] ?? 0),
                'total_luka'      => (int)($stats['total_luka'] ?? 0),
                'total_hilang'    => (int)($stats['total_hilang'] ?? 0),
                'total_pengungsi' => (int)($stats['total_pengungsi'] ?? 0),
                'total_terdampak' => (int)($stats['total_terdampak'] ?? 0),
            ],
            'jenis_bencana' => $jenisBencanaList,
            'wilayah'       => $wilayahList,
            'markers'       => $markers,
        ];
    }

    public function actionWilayahGeojson()
    {
        $response = Yii::$app->response;

        try {
            $level = strtolower((string)Yii::$app->request->get('level', 'provinsi'));
            $tableName = $level === 'kabupaten' ? 'peta_kab2023' : 'peta_provinsi2023';
            $province = Yii::$app->request->get('province');

            // Try caching to speed up loads and reduce CPU/DB usage
            $cacheKey = 'wilayah_geojson_' . $level . '_' . ($province ? md5(strtoupper(trim($province))) : 'all');
            $cache = Yii::$app->cache;
            if ($cache !== null) {
                $cachedJson = $cache->get($cacheKey);
                if ($cachedJson !== false && is_string($cachedJson)) {
                    $response->format = Response::FORMAT_RAW;
                    $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
                    return $cachedJson;
                }
            }

            $db = Yii::$app->db;
            $geomColumn = $this->detectGeometryColumn($db, $tableName);
            if ($geomColumn === null) {
                $response->format = Response::FORMAT_JSON;
                $response->statusCode = 404;
                return [
                    'success' => false,
                    'message' => 'Kolom geometri tidak ditemukan pada tabel ' . $tableName,
                ];
            }

            $whereClause = "WHERE t.\"{$geomColumn}\" IS NOT NULL";
            $params = [];
            if ($level === 'kabupaten' && !empty($province)) {
                $cleanProv = strtoupper(trim($province));
                $cleanProv = preg_replace('/^(PROVINSI|PROV|PRO|DAERAHISTIMEWA|DI)\s+/i', '', $cleanProv);
                $whereClause .= " AND (UPPER(t.provinsi) = :prov OR UPPER(t.provinsi) LIKE :prov_like)";
                $params[':prov'] = $cleanProv;
                $params[':prov_like'] = '%' . $cleanProv . '%';
            }

            $featureCollection = $db->createCommand("
                SELECT json_build_object(
                    'type', 'FeatureCollection',
                    'features', COALESCE(json_agg(
                        json_build_object(
                            'type', 'Feature',
                            'geometry', ST_AsGeoJSON(t.\"{$geomColumn}\")::json,
                            'properties', to_jsonb(t) - '{$geomColumn}'
                        )
                    ), '[]'::json)
                ) AS geojson
                FROM public.\"{$tableName}\" t
                " . $whereClause, $params)->queryScalar();

            $geojsonStr = is_string($featureCollection) ? $featureCollection : json_encode($featureCollection ?: []);

            $result = '{"success":true,"level":"' . $level . '","table":"' . $tableName . '","geojson":' . $geojsonStr . '}';

            if ($cache !== null) {
                // Cache for 30 days since map boundaries rarely change
                $cache->set($cacheKey, $result, 86400 * 30);
            }

            $response->format = Response::FORMAT_RAW;
            $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
            return $result;
        } catch (\Throwable $e) {
            Yii::warning('Error querying wilayah geojson: ' . $e->getMessage(), __METHOD__);
            $response->format = Response::FORMAT_JSON;
            $response->statusCode = 500;
            return [
                'success' => false,
                'message' => 'Gagal mengambil geojson wilayah: ' . $e->getMessage(),
            ];
        }
    }

    private function detectGeometryColumn($db, string $tableName): ?string
    {
        $geomColumn = $db->createCommand("
            SELECT column_name
            FROM information_schema.columns
            WHERE table_schema = 'public'
              AND table_name = :table
              AND udt_name = 'geometry'
            LIMIT 1
        ", [':table' => $tableName])->queryScalar();

        if (!empty($geomColumn)) {
            return (string)$geomColumn;
        }

        foreach (['geom', 'geometry', 'wkb_geometry', 'shape'] as $candidate) {
            $exists = $db->createCommand("
                SELECT 1
                FROM information_schema.columns
                WHERE table_schema = 'public'
                  AND table_name = :table
                  AND column_name = :column
                LIMIT 1
            ", [':table' => $tableName, ':column' => $candidate])->queryScalar();

            if ($exists) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * API Captcha endpoint
     */
    public function actionCaptcha()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');

        if (Yii::$app->request->isOptions) {
            $response->statusCode = 200;
            return [];
        }

        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz';
        $code = '';
        $length = 5;
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        $expiry = time() + 300; // 5 minutes validity
        $data = "$code|$expiry";

        $cipher = "aes-256-cbc";
        $secret = Yii::$app->request->cookieValidationKey;
        if (empty($secret)) {
            $secret = "puskesmas-secret-key-fallback-123456";
        }
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($data, $cipher, $secret, 0, $iv);
        $key = base64_encode($iv . $ciphertext);

        if (function_exists('imagecreatetruecolor')) {
            $width = 150;
            $height = 50;
            $image = imagecreatetruecolor($width, $height);

            $bgColor = imagecolorallocate($image, 245, 247, 247);
            imagefill($image, 0, 0, $bgColor);

            for ($i = 0; $i < 5; $i++) {
                $lineColor = imagecolorallocate($image, rand(180, 220), rand(180, 220), rand(180, 220));
                imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
            }

            for ($i = 0; $i < 150; $i++) {
                $dotColor = imagecolorallocate($image, rand(160, 210), rand(160, 210), rand(160, 210));
                imagesetpixel($image, rand(0, $width), rand(0, $height), $dotColor);
            }

            $charWidth = (int)($width / $length);
            for ($i = 0; $i < $length; $i++) {
                $charColor = imagecolorallocate($image, rand(10, 80), rand(10, 80), rand(10, 100));
                $fontSize = rand(4, 5);
                $x = ($i * $charWidth) + rand(5, 10);
                $y = rand(10, 25);
                imagechar($image, $fontSize, $x, $y, $code[$i], $charColor);
            }

            for ($i = 0; $i < 3; $i++) {
                $lineColor = imagecolorallocate($image, rand(140, 190), rand(140, 190), rand(140, 190));
                imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
            }

            ob_start();
            imagepng($image);
            $imageData = ob_get_clean();
            imagedestroy($image);
            $base64Image = 'data:image/png;base64,' . base64_encode($imageData);

            return [
                'success' => true,
                'captcha_key' => $key,
                'captcha_image' => $base64Image,
                'is_text' => false,
            ];
        }

        return [
            'success' => true,
            'captcha_key' => $key,
            'captcha_question' => $code,
            'is_text' => true,
        ];
    }

    private function validateCaptcha(?string $key, ?string $value): bool
    {
        if (YII_ENV === 'dev' || YII_DEBUG) {
            return true;
        }
        if (empty($key) || empty($value)) {
            return false;
        }
        try {
            $cipher = "aes-256-cbc";
            $secret = Yii::$app->request->cookieValidationKey;
            if (empty($secret)) {
                $secret = "puskesmas-secret-key-fallback-123456";
            }
            $raw = base64_decode($key);
            $ivlen = openssl_cipher_iv_length($cipher);
            if (strlen($raw) <= $ivlen) {
                return false;
            }
            $iv = substr($raw, 0, $ivlen);
            $ciphertext = substr($raw, $ivlen);
            $data = openssl_decrypt($ciphertext, $cipher, $secret, 0, $iv);
            if ($data === false) {
                return false;
            }
            $parts = explode('|', $data);
            if (count($parts) !== 2) {
                return false;
            }
            list($expectedAnswer, $expiry) = $parts;
            if (time() > (int)$expiry) {
                return false;
            }
            return strcasecmp(trim($value), trim($expectedAnswer)) === 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function actionDashboardStats()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        
        $db = Yii::$app->db;
        
        // 1. Total Puskesmas
        $totalPuskesmas = (int)$db->createCommand("SELECT COUNT(*) FROM public.puskesmas_profile WHERE status_aktif = true")->queryScalar();
        
        // 2. Total Penduduk & Beban Kerja
        $totalPenduduk = (int)$db->createCommand("SELECT SUM(jumlah_penduduk) FROM public.puskesmas_profile WHERE status_aktif = true")->queryScalar();
        $bebanKerja = $totalPuskesmas > 0 ? (int)($totalPenduduk / $totalPuskesmas) : 0;
        
        // 3. Ketersediaan Dokter & Nakes (Latest snapshots per Puskesmas)
        $latestKinerjaQuery = "
            SELECT DISTINCT ON (puskesmas_id) * 
            FROM public.puskesmas_kinerja 
            ORDER BY puskesmas_id, tahun DESC, periode_nilai DESC
        ";
        
        $stats = $db->createCommand("
            WITH latest_kinerja AS ($latestKinerjaQuery)
            SELECT 
                SUM(CASE WHEN dokter_tersedia = true THEN 1 ELSE 0 END) as dokter_lengkap,
                SUM(CASE WHEN nakes_9_jenis = true THEN 1 ELSE 0 END) as nakes_9_lengkap,
                SUM(CASE WHEN persen_alkes >= 60.0 THEN 1 ELSE 0 END) as alkes_60_plus,
                SUM(CASE WHEN persen_spa >= 60.0 THEN 1 ELSE 0 END) as spa_60_plus,
                SUM(CASE WHEN jumlah_obat_esensial >= 40 THEN 1 ELSE 0 END) as obat_40_plus
            FROM latest_kinerja
        ")->queryOne();
        
        // 4. Kategori Ranap vs Non Ranap
        $ranapStats = $db->createCommand("
            SELECT status_pelayanan, COUNT(*) as jumlah
            FROM public.puskesmas_profile
            WHERE status_aktif = true
            GROUP BY status_pelayanan
        ")->queryAll();
        
        // 5. Kategori Wilayah & Jenis
        $wilayahStats = $db->createCommand("
            SELECT kategori_wilayah, COUNT(*) as jumlah
            FROM public.puskesmas_profile
            WHERE status_aktif = true
            GROUP BY kategori_wilayah
        ")->queryAll();
        
        // 6. Jumlah Kecamatan & Kecamatan Tanpa Puskesmas
        $totalKecamatan = (int)$db->createCommand("SELECT COUNT(*) FROM public.wilayah_kecamatan")->queryScalar();
        $kecamatanDenganPuskesmas = (int)$db->createCommand("SELECT COUNT(DISTINCT kecamatan_id) FROM public.puskesmas_profile WHERE status_aktif = true")->queryScalar();
        $kecamatanTanpaPuskesmas = max(0, $totalKecamatan - $kecamatanDenganPuskesmas);
        
        // 7. Tabel Provinsi Data (DLI & Tata Kelola)
        $provinsiList = $db->createCommand("
            WITH latest_kinerja AS ($latestKinerjaQuery)
            SELECT 
                p.code as provinsi_code,
                p.name as provinsi_name,
                COUNT(prof.id) as jumlah_puskesmas,
                SUM(CASE WHEN k.status_blud = true THEN 1 ELSE 0 END) as blud,
                SUM(CASE WHEN k.status_ilp = true THEN 1 ELSE 0 END) as ilp,
                SUM(CASE WHEN k.skor_pkp_total >= 80.0 THEN 1 ELSE 0 END) as pkp_baik,
                SUM(CASE WHEN k.nakes_9_jenis = true THEN 1 ELSE 0 END) as nakes_9,
                SUM(CASE WHEN k.persen_alkes >= 60.0 THEN 1 ELSE 0 END) as alkes_60
            FROM public.wilayah_provinsi p
            LEFT JOIN public.puskesmas_profile prof ON prof.provinsi_id = p.code AND prof.status_aktif = true
            LEFT JOIN latest_kinerja k ON k.puskesmas_id = prof.id
            GROUP BY p.code, p.name
            ORDER BY p.name ASC
        ")->queryAll();

        return [
            'success' => true,
            'summary' => [
                'total_puskesmas' => $totalPuskesmas,
                'total_penduduk' => $totalPenduduk,
                'beban_kerja_rasio' => $bebanKerja,
                'dokter_lengkap_count' => (int)($stats['dokter_lengkap'] ?? 0),
                'nakes_9_lengkap_count' => (int)($stats['nakes_9_lengkap'] ?? 0),
                'alkes_60_plus_count' => (int)($stats['alkes_60_plus'] ?? 0),
                'spa_60_plus_count' => (int)($stats['spa_60_plus'] ?? 0),
                'obat_40_plus_count' => (int)($stats['obat_40_plus'] ?? 0),
                'total_kecamatan' => $totalKecamatan,
                'kecamatan_tanpa_puskesmas' => $kecamatanTanpaPuskesmas,
            ],
            'kategori_ranap' => $ranapStats,
            'kategori_wilayah' => $wilayahStats,
            'provinsi_data' => $provinsiList,
        ];
    }

    public function actionDashboardCharts()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        
        $db = Yii::$app->db;
        $latestKinerjaQuery = "
            SELECT DISTINCT ON (puskesmas_id) * 
            FROM public.puskesmas_kinerja 
            ORDER BY puskesmas_id, tahun DESC, periode_nilai DESC
        ";
        
        // 1. Pie BLUD
        $blud = $db->createCommand("
            WITH latest_kinerja AS ($latestKinerjaQuery)
            SELECT 
                SUM(CASE WHEN status_blud = true THEN 1 ELSE 0 END) as blud,
                SUM(CASE WHEN status_blud = false OR status_blud IS NULL THEN 1 ELSE 0 END) as non_blud
            FROM latest_kinerja
        ")->queryOne();
        
        // 2. Pie ILP
        $ilp = $db->createCommand("
            WITH latest_kinerja AS ($latestKinerjaQuery)
            SELECT 
                SUM(CASE WHEN status_ilp = true THEN 1 ELSE 0 END) as ilp,
                SUM(CASE WHEN status_ilp = false OR status_ilp IS NULL THEN 1 ELSE 0 END) as non_ilp
            FROM latest_kinerja
        ")->queryOne();
        
        // 3. Pie PKP
        $pkp = $db->createCommand("
            WITH latest_kinerja AS ($latestKinerjaQuery)
            SELECT 
                SUM(CASE WHEN skor_pkp_total >= 80.0 THEN 1 ELSE 0 END) as baik,
                SUM(CASE WHEN skor_pkp_total >= 60.0 AND skor_pkp_total < 80.0 THEN 1 ELSE 0 END) as cukup,
                SUM(CASE WHEN skor_pkp_total < 60.0 OR skor_pkp_total IS NULL THEN 1 ELSE 0 END) as kurang
            FROM latest_kinerja
        ")->queryOne();
        
        // 4. Realisasi BOK & Insentif UKM (aggregate by year)
        $pembiayaan = $db->createCommand("
            SELECT 
                tahun,
                SUM(COALESCE(alokasi_bok, 0)) as alokasi_bok,
                SUM(COALESCE(realisasi_bok, 0)) as realisasi_bok,
                SUM(COALESCE(realisasi_insentif_ukm, 0)) as realisasi_insentif_ukm
            FROM public.puskesmas_kinerja
            GROUP BY tahun
            ORDER BY tahun ASC
        ")->queryAll();
        
        return [
            'success' => true,
            'blud' => [
                'blud' => (int)($blud['blud'] ?? 0),
                'non_blud' => (int)($blud['non_blud'] ?? 0),
            ],
            'ilp' => [
                'ilp' => (int)($ilp['ilp'] ?? 0),
                'non_ilp' => (int)($ilp['non_ilp'] ?? 0),
            ],
            'pkp' => [
                'baik' => (int)($pkp['baik'] ?? 0),
                'cukup' => (int)($pkp['cukup'] ?? 0),
                'kurang' => (int)($pkp['kurang'] ?? 0),
            ],
            'pembiayaan' => $pembiayaan,
        ];
    }

    public function actionTopDiseases()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        
        $db = Yii::$app->db;
        $data = $db->createCommand("
            SELECT nama_penyakit, SUM(jumlah_kasus) as total_kasus
            FROM public.puskesmas_penyakit
            GROUP BY nama_penyakit
            ORDER BY total_kasus DESC
            LIMIT 10
        ")->queryAll();
        
        foreach ($data as &$row) {
            $row['total_kasus'] = (int)$row['total_kasus'];
        }
        unset($row);
        
        return [
            'success' => true,
            'data' => $data,
        ];
    }
}
