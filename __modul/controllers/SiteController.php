<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\RegisterMasyarakatForm;
use app\models\UserRegistration;
use app\models\VerifyEmailForm;
use app\services\RegistrationEmailService;
use app\services\WilayahService;
use app\models\ContactForm;
use yii\web\NotFoundHttpException;


class SiteController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['get'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => \app\components\CaptchaCustom::class,
                // opsional override:
                'minLength' => 5, 'maxLength' => 5, 'width' => 140, 'height' => 48,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->getIsGuest()) {
            $return_url = empty(Yii::$app->request->get('return')) ? \yii\helpers\Url::to(['/site/login']) : Yii::$app->request->get('return');
            return $this->redirect($return_url);
        } else {
            return $this->redirect(['/beranda/index']);
        }
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->getIsGuest()) {
            return $this->goHome();
        }
        //print_r($request);
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $model->catat_log('Login', "");
            return $this->goBack();
        }

        
        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionRegister()
    {
        $frontendUrl = Yii::$app->params['frontend_url'] ?? 'http://localhost:3000';
        return $this->redirect(rtrim($frontendUrl, '/') . '/register');
    }

    public function actionRegisterSuccess()
    {
        if (!Yii::$app->session->getFlash('registerSuccess')) {
            return $this->redirect(['register']);
        }

        return $this->render('register-success');
    }

    public function actionVerifyEmail($id)
    {
        $registration = $this->findRegistration($id);

        if ($registration->status !== UserRegistration::STATUS_EMAIL_PENDING) {
            return $this->redirect(['waiting-approval', 'id' => $registration->id]);
        }

        $model = new VerifyEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($registration->validateOtp($model->otp)) {
                $registration->markEmailVerified();
                return $this->redirect(['waiting-approval', 'id' => $registration->id]);
            }

            $model->addError('otp', 'OTP salah atau sudah kadaluarsa.');
        }

        return $this->render('verify-email', [
            'model' => $model,
            'registration' => $registration,
        ]);
    }

    public function actionResendOtp($id)
    {
        $registration = $this->findRegistration($id);

        if (!Yii::$app->request->isPost) {
            return $this->redirect(['verify-email', 'id' => $registration->id]);
        }

        if ($registration->status !== UserRegistration::STATUS_EMAIL_PENDING) {
            return $this->redirect(['waiting-approval', 'id' => $registration->id]);
        }

        $lastSent = $registration->otp_sent_at ? strtotime((string) $registration->otp_sent_at) : 0;
        if ($lastSent && (time() - $lastSent) < 60) {
            Yii::$app->session->setFlash('warning', 'OTP baru dapat dikirim ulang setelah 1 menit.');
            return $this->redirect(['verify-email', 'id' => $registration->id]);
        }

        $otp = $registration->generateOtp();
        $registration->otp_resend_count = (int) $registration->otp_resend_count + 1;
        $registration->save(false);

        if (RegistrationEmailService::sendOtp($registration, $otp)) {
            Yii::$app->session->setFlash('success', 'OTP baru berhasil dikirim ke email Anda.');
        } else {
            Yii::$app->session->setFlash('warning', 'OTP baru sudah dibuat, tetapi email gagal dikirim. Periksa konfigurasi mailer.');
        }

        return $this->redirect(['verify-email', 'id' => $registration->id]);
    }

    public function actionWaitingApproval($id)
    {
        $registration = $this->findRegistration($id);

        return $this->render('waiting-approval', [
            'registration' => $registration,
        ]);
    }

    public function actionRegisterKabupaten($province_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (empty($province_id)) {
            return ['success' => false, 'data' => [], 'message' => 'Provinsi belum dipilih'];
        }

        try {
            $data = (new WilayahService())->getKabupatenOptions((string)$province_id);

            return ['success' => true, 'data' => $data];
        } catch (\Throwable $e) {
            Yii::error('Gagal memuat kab/kota register: ' . $e->getMessage(), __METHOD__);
            return ['success' => false, 'data' => [], 'message' => 'Gagal memuat Kab/Kota'];
        }
    }

    public function actionLogout()
    {
        $model = new LoginForm();
        $model->catat_log('Logout', "");
        Yii::$app->user->logout();
        
        $returnUrl = Yii::$app->request->get('return');
        if (!empty($returnUrl)) {
            return $this->redirect($returnUrl);
        }

        $frontendUrl = Yii::$app->params['frontend_url'] ?? '';
        if (!empty($frontendUrl)) {
            return $this->redirect(rtrim($frontendUrl, '/') . '/login?logout=1');
        }

        return $this->goHome();
    }

        /**
         * Menampilkan halaman akses level user
         *
         * @return string
         */
        public function actionAksesLevelUser()
        {
            return $this->render('akses-level-user');
        }

        /**
         * Menampilkan halaman profil petugas (non-AJAX)
         *
         * @return string
         */
        public function actionProfil()
        {
            if (Yii::$app->user->isGuest) {
                return $this->redirect(['site/login']);
            }

            $identity = Yii::$app->user->identity;
            $user = [
                'id' => $identity->id ?? null,
                'username' => $identity->username ?? '',
                'nama_lengkap' => $identity->nama_lengkap ?? '',
                'email' => $identity->email ?? '',
                'no_telpon' => $identity->no_telpon ?? '',
                'level_user_nama' => isset($identity->levelUser) ? ($identity->levelUser->nama_level ?? '') : '',
                'foto_profil' => $identity->foto_profil ?? null,
            ];

            return $this->render('@app/views/data_user_akses/profil', ['user' => $user]);
        }

        /**
         * Menampilkan halaman konfigurasi navigasi (non-AJAX)
         * Simple view-only page (no controller/model required for navigation listing)
         *
         * @return string
         */
        public function actionKonfigurasiNavigasi()
        {
            if (Yii::$app->user->isGuest) {
                return $this->redirect(['site/login']);
            }

            // MenuService will build the menu structure based on data and hak_akses
            $menu = \app\components\MenuService::getMenu();
            return $this->render('konfigurasi-navigasi', ['menu' => $menu]);
        }

        /**
         * AJAX: Get area by check-in number
         * @return Response JSON
         */
        public function actionGetArea()
        {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $checkin_no = Yii::$app->request->post('checkin_no');

            if (empty($checkin_no)) {
                return ['success' => false, 'message' => 'Check-in number is required'];
            }

            try {
                $areas = \app\models\DataLokasi::getLokasiByNomor($checkin_no);

                return [
                    'success' => true,
                    'data' => $areas
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ];
            }
        }

        /**
         * AJAX: Get sektor by lokasi
         * @return Response JSON
         */
        public function actionGetSektor()
        {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $lokasi_id = Yii::$app->request->post('area_id');

            if (empty($lokasi_id)) {
                return ['success' => false, 'message' => 'Lokasi ID is required'];
            }

            $sektors = \app\models\DataSektor::getSektorByLokasi($lokasi_id);

            return [
                'success' => true,
                'data' => $sektors
            ];
        }

        /**
         * AJAX: Submit check-in
         * @return Response JSON
         */
        public function actionSubmitCheckin()
        {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if (Yii::$app->user->isGuest) {
                return ['success' => false, 'message' => 'User not logged in'];
            }

            $model = new \app\models\TransCheckin();
            $model->username = Yii::$app->user->identity->username;
            $model->checkin_no = Yii::$app->request->post('checkin_no');
            $model->lokasi_checkin = Yii::$app->request->post('lokasi_checkin');
            $model->sektor_checkin = Yii::$app->request->post('sektor_checkin');
            $model->tgl_masehi_check = Yii::$app->request->post('tgl_masehi_check');
            $model->waktu_masehi = Yii::$app->request->post('waktu_masehi');
            $model->satuan_waktu = Yii::$app->request->post('satuan_waktu');

            // Set real time
            $model->tgl_real_masehi = date('Y-m-d');
            $model->waktu_real_masehi = date('H:i:s');

            if ($model->save()) {
                return [
                    'success' => true,
                    'message' => 'Check-in berhasil disimpan',
                    'data' => [
                        'id_checkin' => $model->id_checkin,
                        'created_at' => $model->created_at
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan check-in',
                    'errors' => $model->errors
                ];
            }
        }

        protected function getRegisterProvinsiList(): array
        {
            $list = ['' => 'Pilih Provinsi'];

            try {
                foreach ((new WilayahService())->getProvinsiOptions() as $row) {
                    $list[(string)$row['code']] = $row['name'];
                }
            } catch (\Throwable $e) {
                Yii::error('Gagal memuat provinsi register: ' . $e->getMessage(), __METHOD__);
            }

            return $list;
        }

        protected function getRegisterKabupatenList($provTblWilayahId): array
        {
            $list = ['' => $provTblWilayahId ? 'Pilih Kab/Kota' : 'Pilih Provinsi Dahulu'];

            if (empty($provTblWilayahId)) {
                return $list;
            }

            try {
                foreach ((new WilayahService())->getKabupatenOptions((string)$provTblWilayahId) as $row) {
                    $list[(string)$row['code']] = $row['name'];
                }
            } catch (\Throwable $e) {
                Yii::error('Gagal memuat kab/kota register: ' . $e->getMessage(), __METHOD__);
            }

            return $list;
        }

        protected function findRegistration($id): UserRegistration
        {
            $model = UserRegistration::findOne((int) $id);
            if (!$model) {
                throw new NotFoundHttpException('Data pendaftaran tidak ditemukan.');
            }

            return $model;
        }

    public function isActionPublic(string $actionId): bool
    {
        if ($actionId === 'sso-login') {
            return true;
        }
        return parent::isActionPublic($actionId);
    }

    /**
     * SSO Login dari Frontend (Next.js) ke Backend (Yii2).
     */
    public function actionSsoLogin($token = null)
    {
        if (empty($token)) {
            Yii::$app->session['error'] = 1;
            Yii::$app->session['error_message'] = 'Token SSO tidak ditemukan.';
            return $this->redirect(['site/login']);
        }

        // Decode JWT
        $secret = $_ENV['JWT_SECRET'] ?? 'kemkes_puskesmas_jwt_secret_key_2026';
        $payload = $this->decodeJwt($token, $secret);

        if (!$payload) {
            $fallbackSecret = Yii::$app->request->cookieValidationKey ?: "kemkes!@#$%^&*()_api";
            $payload = $this->decodeJwt($token, $fallbackSecret);
        }
        if (!$payload) {
            $payload = $this->decodeJwt($token, "kemkes!@#$%^&*()");
        }

        if ($payload && isset($payload['sub'])) {
            $user = \app\models\User::findOne((int)$payload['sub']);
            if ($user) {
                // Log user in
                if (Yii::$app->user->login($user, 3600 * 24)) {
                    return $this->redirect(['/beranda/index']);
                }
            }
        }

        Yii::$app->session['error'] = 1;
        Yii::$app->session['error_message'] = 'Sesi SSO tidak valid atau pengguna tidak ditemukan.';
        return $this->redirect(['site/login']);
    }

    private function decodeJwt(string $token, string $secret): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        list($headerB64, $payloadB64, $signatureB64) = $parts;

        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(hash_hmac('sha256', $headerB64 . "." . $payloadB64, $secret, true)));
        if ($signatureB64 !== $expectedSignature) {
            return null;
        }

        $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $payloadB64));
        return json_decode($payloadJson, true);
    }

} 
