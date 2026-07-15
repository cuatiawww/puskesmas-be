<?php
namespace app\controllers;

use Yii;
use app\models\HakAkses;
use app\models\SubModul;
use app\models\User;
use app\services\WilayahService;
use yii\helpers\HtmlPurifier;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;

class BaseController extends Controller
{
    protected $allowHtmlFields = [
        'isi',
        'LoginForm.password',
        'Content.isi_id',
        'ContentCategory.isi_category'
    ];

    protected function csrfExemptActions(): array
    {
        return [];
    }

    public function isActionPublic(string $actionId): bool
    {
        return false;
    }

    protected function shouldSanitizeRequest(string $actionId): bool
    {
        return true;
    }

    protected function shouldLogRequestDebug(): bool
    {
        return YII_DEBUG && (bool) (Yii::$app->params['debug_request_logging'] ?? false);
    }


    public function beforeAction($action)
    {
        $this->enableCsrfValidation = !in_array($action->id, $this->csrfExemptActions(), true);

        if ($this->shouldSanitizeRequest($action->id)) {
            $_GET = $this->sanitizeArray($_GET);
            $_POST = $this->sanitizeArray($_POST);

            Yii::$app->request->setQueryParams($_GET);
            Yii::$app->request->setBodyParams($_POST);
        }

        if ($this->shouldLogRequestDebug()) {
            try {
                $req = Yii::$app->request;
                $sessionCookie = $req->cookies->getValue(session_name(), '');
                Yii::info(sprintf(
                    "REQ %s | controller=%s | action=%s | user_id=%s | isGuest=%s | sessionId=%s | session_cookie=%s | host=%s",
                    $req->getUrl(),
                    $this->id,
                    $action->id,
                    Yii::$app->user->id ?? 'null',
                    Yii::$app->user->isGuest ? '1' : '0',
                    Yii::$app->session->getId(),
                    $sessionCookie,
                    $_SERVER['HTTP_HOST'] ?? ''
                ), __METHOD__);
            } catch (\Throwable $e) {
                // ignore logging errors
            }
        }

        if (!$this->checkRoutePermission($action)) {
            if (Yii::$app->request->isAjax) {
                throw new ForbiddenHttpException('Anda tidak memiliki hak akses untuk aksi ini.');
            }

            Yii::$app->session->setFlash('swal', [
                'icon' => 'error',
                'title' => 'Akses Ditolak',
                'text' => 'Anda tidak memiliki hak akses untuk halaman atau aksi tersebut.',
                'showConfirmButton' => true,
                'confirmButtonColor' => '#229799',
            ]);

            $referrer = Yii::$app->request->getReferrer();
            $requestedUrl = Yii::$app->request->getAbsoluteUrl();

            if (empty($referrer) || $referrer === $requestedUrl) {
                $homeUrl = Yii::$app->user->isGuest ? ['site/login'] : ['beranda/index'];
                $this->redirect($homeUrl)->send();
            } else {
                $this->redirect($referrer)->send();
            }
            return false;
        }

        return parent::beforeAction($action);
    }

    protected function checkRoutePermission($action): bool
    {
        // Bypass check for explicitly public guest actions in SiteController and ForgotPasswordController
        $controllerId = $action->controller->id;
        $actionId = $action->id;

        if ($controllerId === 'site' && in_array($actionId, ['login', 'login-api', 'captcha', 'reCaptcha', 'register', 'register-success', 'register-kabupaten', 'verify-email', 'resend-otp', 'waiting-approval', 'error'], true)) {
            return true;
        }

        if ($controllerId === 'forgot-password' && in_array($actionId, ['request', 'verify'], true)) {
            return true;
        }
          if ($controllerId === 'auth' || $controllerId === 'api') {
        return true; // semua action di AuthController dan ApiController bebas akses
    }

        if (
            $controllerId === 'auth' && in_array($actionId, [
                'captcha-api',
                'login-api',
                'register-api',
                'captcha-api',
                'forgot-password-request-api',
                'forgot-password-verify-api',
                'regions-api',
                'verify-register-otp-api',
                'resend-register-otp-api',
            ], true)
        ) {
            return true;
        }
        

        if (Yii::$app->user->isGuest) {
            return $this->findSubModulForRoute($action->controller->id, $action->id) === null;
        }

        if ($this->isCurrentUserSuperAdmin()) {
            return true;
        }

        $subModul = $this->findSubModulForRoute($action->controller->id, $action->id);
        if ($subModul === null) {
            return true;
        }

        $levelUserId = $this->currentUserLevelId();
        if ($levelUserId === null) {
            return false;
        }

        $hakAkses = HakAkses::find()
            ->where([
                'level_user_id' => $levelUserId,
                'sub_modul_id' => $subModul->id,
            ])
            ->one();

        if (!$hakAkses) {
            return false;
        }

        $permission = $this->permissionForAction($action->id);
        return (bool) ($hakAkses->{$permission} ?? false);
    }

    protected function findSubModulForRoute(string $controllerId, string $actionId): ?SubModul
    {
        $requestedRoute = $this->normalizeMenuRoute($controllerId . '/' . $actionId);
        $indexRoute = $this->normalizeMenuRoute($controllerId . '/index');

        foreach ($this->activeSubModulRoutes() as $subModul) {
            $route = $this->normalizeMenuRoute((string) $subModul->route);
            if ($route === $requestedRoute || $route === $indexRoute) {
                return $subModul;
            }
        }

        return null;
    }

    protected function normalizeMenuRoute(string $route): string
    {
        $route = trim($route);
        $route = trim($route, '/');

        if ($route === '' || $route === '#!' || $route === '#') {
            return $route;
        }

        if (substr($route, -4) === '.php') {
            $route = substr($route, 0, -4);
        }

        return trim($route, '/');
    }

    protected function activeSubModulRoutes(): array
    {
        static $routes = null;
        if ($routes === null) {
            $routes = SubModul::find()
                ->where(['is_active' => true])
                ->andWhere(['not', ['route' => null]])
                ->all();
        }

        return $routes;
    }

    protected function permissionForAction(string $actionId): string
    {
        if (in_array($actionId, ['delete'], true) || strpos($actionId, 'delete') === 0) {
            return 'can_delete';
        }

        if (in_array($actionId, ['create', 'register'], true) || strpos($actionId, 'create') === 0) {
            return 'can_create';
        }

        if (
            in_array($actionId, ['update', 'edit', 'save', 'approve', 'reject'], true)
            || strpos($actionId, 'update') === 0
            || strpos($actionId, 'edit') === 0
            || strpos($actionId, 'save') === 0
            || strpos($actionId, 'approve') === 0
            || strpos($actionId, 'reject') === 0
        ) {
            return 'can_update';
        }

        return 'can_view';
    }

    protected function currentUserLevelId(): ?int
    {
        $identity = Yii::$app->user->identity;
        if (!$identity) {
            return null;
        }

        $level = $identity->id_user_level ?? $identity->level_user_id ?? null;
        return $level !== null && $level !== '' ? (int) $level : null;
    }

    protected function isCurrentUserSuperAdmin(): bool
    {
        return $this->currentUserLevelId() === 1;
    }

    public function canView(string $route = null): bool
    {
        return $this->checkPermission('can_view', $route);
    }

    public function canCreate(string $route = null): bool
    {
        return $this->checkPermission('can_create', $route);
    }

    public function canUpdate(string $route = null): bool
    {
        return $this->checkPermission('can_update', $route);
    }

    public function canDelete(string $route = null): bool
    {
        return $this->checkPermission('can_delete', $route);
    }

    protected function checkPermission(string $permission, ?string $route): bool
    {
        if ($this->isCurrentUserSuperAdmin()) {
            return true;
        }

        if (Yii::$app->user->isGuest) {
            return false;
        }

        if ($route === null) {
            $controllerId = $this->id;
            $actionId = $this->action->id ?? 'index';
        } else {
            $parts = explode('/', trim($route, '/'));
            $controllerId = $parts[0] ?? $this->id;
            $actionId = $parts[1] ?? 'index';
        }

        $subModul = $this->findSubModulForRoute($controllerId, $actionId);
        if ($subModul === null) {
            return true;
        }

        $levelUserId = $this->currentUserLevelId();
        if ($levelUserId === null) {
            return false;
        }

        $hakAkses = HakAkses::find()
            ->where([
                'level_user_id' => $levelUserId,
                'sub_modul_id' => $subModul->id,
            ])
            ->one();

        if (!$hakAkses) {
            return false;
        }

        return (bool) ($hakAkses->{$permission} ?? false);
    }

    protected function currentUserWilayahScope(): array
    {
        $identity = Yii::$app->user->identity;
        $scope = [
            'mode' => 'all',
            'master_id' => null,
            'tbl_id' => null,
            'prov_tbl_id' => null,
            'kab_tbl_id' => null,
        ];

        if (!$identity || $this->isCurrentUserSuperAdmin()) {
            return $scope;
        }

        if (!empty($identity->kd_kab)) {
            $scope['mode'] = 'kabupaten';
            $scope['kab_tbl_id'] = (string) $identity->kd_kab;
            $scope['prov_tbl_id'] = !empty($identity->kd_prop) ? (string) $identity->kd_prop : null;
            return $scope;
        }

        if (!empty($identity->kd_prop)) {
            $scope['mode'] = 'provinsi';
            $scope['prov_tbl_id'] = (string) $identity->kd_prop;
            $scope['tbl_id'] = (string) $identity->kd_prop;
        }

        return $scope;
    }

    protected function currentUserWilayahCodeScope(): array
    {
        $scope = $this->currentUserWilayahScope();
        $codeScope = [
            'mode' => $scope['mode'],
            'prov_code' => null,
            'kab_code' => null,
        ];

        if ($scope['mode'] === 'all') {
            return $codeScope;
        }

        $db = Yii::$app->db;

        if (!empty($scope['prov_tbl_id'])) {
            $provCode = $db->createCommand("SELECT kd_user FROM master_wilayah WHERE id = :id", [':id' => (int) $scope['prov_tbl_id']])->queryScalar();
            if ($provCode !== false && $provCode !== null && $provCode !== '') {
                $codeScope['prov_code'] = (string) $provCode;
            }
        }

        if (!empty($scope['kab_tbl_id'])) {
            $kabCode = $db->createCommand("SELECT kd_user FROM master_wilayah WHERE id = :id", [':id' => (int) $scope['kab_tbl_id']])->queryScalar();
            if ($kabCode !== false && $kabCode !== null && $kabCode !== '') {
                $codeScope['kab_code'] = (string) $kabCode;
            }
        }

        return $codeScope;
    }
    public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'only' => ['captcha-api'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['captcha-api'],
                    'roles' => ['?'],
                ],
            ],
        ],
    ];
}

    protected function isWithinCurrentUserWilayahScope($provinsiId, $kabupatenId = null): bool
    {
        $scope = $this->currentUserWilayahScope();

        if ($scope['mode'] === 'all') {
            return true;
        }

        if ($scope['mode'] === 'provinsi') {
            return !empty($scope['prov_tbl_id']) && (string) $provinsiId === (string) $scope['prov_tbl_id'];
        }

        if ($scope['mode'] === 'kabupaten') {
            return !empty($scope['kab_tbl_id']) && (string) $kabupatenId === (string) $scope['kab_tbl_id'];
        }

        return false;
    }

    // protected function sanitizeArray($data)
    // {
    //     foreach ($data as $k => $v) {
    //         if (is_array($v)) {
    //             $data[$k] = $this->sanitizeArray($v);
    //         } else {
    //             $data[$k] = htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
    //         }
    //     }
    //     return $data;
    // }

    // protected function sanitizeArray($data, $parentKey = '')
    // {
    //     foreach ($data as $k => $v) {
    //         $fullKey = $parentKey ? "{$parentKey}.{$k}" : $k;

    //         if (is_array($v)) {
    //             $data[$k] = $this->sanitizeArray($v, $fullKey);
    //         } else {
    //             $v = trim($v);

    //             if (in_array($k, $this->allowHtmlFields)) {
    //                 // Bersihkan HTML tapi izinkan tag aman
    //                 $v = HtmlPurifier::process($v);
    //             } else {
    //                 // Sanitize XSS umum + escape SQL LIKE wildcard
    //                 $v = htmlspecialchars(strip_tags($v), ENT_QUOTES, 'UTF-8');
    //                 $v = $this->escapeSqlLike($v);
    //             }

    //             $data[$k] = $v;
    //         }
    //     }
    //     return $data;
    // }

    protected function sanitizeArray($data, $parentKey = '')
    {
        foreach ($data as $k => $v) {
            $fullKey = $parentKey ? "{$parentKey}.{$k}" : $k;

            if (is_array($v)) {
                $data[$k] = $this->sanitizeArray($v, $fullKey);
                continue;
            }

            if (!is_string($v)) {
                $data[$k] = $v;
                continue;
            }

            $v = str_replace("\0", '', trim($v));

            if (in_array($fullKey, $this->allowHtmlFields, true)) {
                $data[$k] = HtmlPurifier::process($v, [
                    'HTML.SafeIframe' => true,
                    'URI.SafeIframeRegexp' => '%^(https?:)?//%',
                ]);
                continue;
            }

            // Input security is centralized here without mutating business data too aggressively.
            $data[$k] = preg_replace('/[^\P{C}\t\r\n]+/u', '', $v) ?? $v;
        }

        return $data;
    }

    protected function escapeSqlLike($str)
    {
        return addcslashes($str, '\\%_');
    }

    /* ------------------------- Data Jamaah: moved to DataJamaahController ------------------------- */

    /**
     * Legacy-friendly action so old route '/site/data-jamaah' still works
     */
    public function actionDataJamaah()
    {
        return $this->redirect(['data-jamaah/index']);
    }

    protected function getUserAttribute(User $user, string $attribute, $default = null)
    {
        return $user->hasAttribute($attribute) ? $user->getAttribute($attribute) : $default;
    }

    protected function getUserLevelName(User $user): string
    {
        try {
            if ($user->levelUser) {
                return (string)($user->levelUser->nama_level ?? '');
            }
        } catch (\Throwable $e) {
            Yii::warning('Gagal memuat level user: ' . $e->getMessage(), __METHOD__);
        }

        $levelUserId = $user->getIdUserLevel();
        return ((int)$levelUserId === 1) ? 'Super Administrator' : 'Admin';
    }

    protected function buildUserWilayahScope(User $user): array
    {
        $levelUserId = (int)($user->getIdUserLevel() ?? 0);
        $scope = [
            'mode' => 'all',
            'access_label' => 'Pusat pemantauan nasional fasilitas kesehatan',
            'cakupan' => [
                'value' => 'nasional',
                'label' => 'Nasional',
                'locked' => false,
            ],
            'provinsi' => [
                'id' => null,
                'label' => 'Semua Provinsi',
                'locked' => false,
            ],
            'kabupaten' => [
                'id' => null,
                'label' => 'Semua Kab/Kota',
                'locked' => false,
                'options' => [],
            ],
        ];

        if ($levelUserId === 1) {
            return $scope;
        }

        $kdProp = $this->getUserAttribute($user, 'kd_prop');
        $kdKab = $this->getUserAttribute($user, 'kd_kab');

        $db = Yii::$app->db;
        $bpsProp = null;
        $bpsKab = null;

        if (!empty($kdProp)) {
            $val = $db->createCommand("SELECT kd_user FROM master_wilayah WHERE id = :id", [':id' => (int)$kdProp])->queryScalar();
            $bpsProp = ($val !== false && $val !== null && $val !== '') ? (string)$val : (string)$kdProp;
        }

        if (!empty($kdKab)) {
            $val = $db->createCommand("SELECT kd_user FROM master_wilayah WHERE id = :id", [':id' => (int)$kdKab])->queryScalar();
            $bpsKab = ($val !== false && $val !== null && $val !== '') ? (string)$val : (string)$kdKab;
        }

        if (!empty($bpsKab)) {
            $scopeResult = $this->buildKabupatenScopeFromCodes($bpsKab, !empty($bpsProp) ? $bpsProp : null);
            if ($scopeResult !== null) {
                return $scopeResult;
            }
        }

        if (!empty($bpsProp)) {
            $scopeResult = $this->buildProvinsiScopeFromCode($bpsProp);
            if ($scopeResult !== null) {
                return $scopeResult;
            }
        }

        return $scope;
    }

    protected function buildProvinsiScopeFromCode(string $provinceCode): ?array
    {
        $wilayah = new WilayahService();
        $provinsiName = $wilayah->findProvinsiName($provinceCode);
        if ($provinsiName === null) {
            return null;
        }

        return [
            'mode' => 'provinsi',
            'access_label' => 'Pemantauan wilayah provinsi ' . $provinsiName,
            'cakupan' => [
                'value' => 'provinsi',
                'label' => 'Provinsi',
                'locked' => true,
            ],
            'provinsi' => [
                'id' => $provinceCode,
                'label' => $provinsiName,
                'locked' => true,
            ],
            'kabupaten' => [
                'id' => null,
                'label' => 'Semua Kab/Kota',
                'locked' => false,
                'options' => array_map(static function (array $item): array {
                    return [
                        'id' => $item['code'],
                        'label' => $item['name'],
                    ];
                }, $wilayah->getKabupatenOptions($provinceCode)),
            ],
        ];
    }

    protected function buildKabupatenScopeFromCodes(string $kabupatenCode, ?string $provinceCode = null): ?array
    {
        $wilayah = new WilayahService();
        $kabupatenName = $wilayah->findKabupatenName($kabupatenCode);
        if ($kabupatenName === null) {
            return null;
        }

        $provinsiName = $wilayah->findProvinsiName($provinceCode) ?: 'Provinsi Wilayah';

        return [
            'mode' => 'kabupaten',
            'access_label' => 'Pemantauan wilayah ' . $kabupatenName,
            'cakupan' => [
                'value' => 'kabupaten-kota',
                'label' => 'Kabupaten/Kota',
                'locked' => true,
            ],
            'provinsi' => [
                'id' => $provinceCode,
                'label' => $provinsiName,
                'locked' => true,
            ],
            'kabupaten' => [
                'id' => $kabupatenCode,
                'label' => $kabupatenName,
                'locked' => true,
                'options' => [
                    [
                        'id' => $kabupatenCode,
                        'label' => $kabupatenName,
                    ],
                ],
            ],
        ];
    }

    protected function generateJwt(array $payload, string $secret): string
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    protected function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
