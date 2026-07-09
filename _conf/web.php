<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$db_user = require __DIR__ . '/db_user.php';

$s3Endpoint = trim((string)($_ENV['FS_S3_ENDPOINT'] ?? getenv('FS_S3_ENDPOINT') ?: ''));
$s3Key = trim((string)($_ENV['FS_S3_KEY'] ?? getenv('FS_S3_KEY') ?: ''));
$s3Secret = trim((string)($_ENV['FS_S3_SECRET'] ?? getenv('FS_S3_SECRET') ?: ''));
$s3Bucket = trim((string)($_ENV['FS_S3_BUCKET'] ?? getenv('FS_S3_BUCKET') ?: ''));
$s3Region = trim((string)($_ENV['FS_S3_REGION'] ?? getenv('FS_S3_REGION') ?: 'us-east-1'));
$s3UsePathStyle = strtolower(trim((string)($_ENV['FS_S3_PATH_STYLE'] ?? getenv('FS_S3_PATH_STYLE') ?: '1')));
$s3UsePathStyle = in_array($s3UsePathStyle, ['1', 'true', 'yes', 'on'], true);
$s3EnabledRaw = strtolower(trim((string)($_ENV['FS_S3_ENABLED'] ?? getenv('FS_S3_ENABLED') ?: '')));
$s3Enabled = in_array($s3EnabledRaw, ['1', 'true', 'yes', 'on'], true);
$fsEnabled = $s3Enabled && $s3Endpoint !== '' && $s3Key !== '' && $s3Secret !== '' && $s3Bucket !== '';

$redisHost = trim((string)($_ENV['REDIS_HOST'] ?? getenv('REDIS_HOST') ?: '127.0.0.1'));
$redisPort = (int)($_ENV['REDIS_PORT'] ?? getenv('REDIS_PORT') ?: 6379);
$redisDb = (int)($_ENV['REDIS_DB'] ?? getenv('REDIS_DB') ?: 0);
$redisEnabledRaw = strtolower(trim((string)($_ENV['REDIS_ENABLED'] ?? getenv('REDIS_ENABLED') ?: '')));
$redisEnabled = in_array($redisEnabledRaw, ['1', 'true', 'yes', 'on'], true);
$redisSessionName = trim((string)($_ENV['REDIS_SESSION_NAME'] ?? getenv('REDIS_SESSION_NAME') ?: 'PUSKESMASSESSID'));
$redisSessionPrefix = trim((string)($_ENV['REDIS_SESSION_PREFIX'] ?? getenv('REDIS_SESSION_PREFIX') ?: 'session:puskesmas:'));
$redisCachePrefix = trim((string)($_ENV['REDIS_CACHE_PREFIX'] ?? getenv('REDIS_CACHE_PREFIX') ?: 'cache:puskesmas:'));

$params['storage'] = [
    'driver' => $fsEnabled ? 's3' : 'local',
    's3Enabled' => $fsEnabled,
    'bucket' => $s3Bucket,
    'endpoint' => $s3Endpoint,
];

$params['redis'] = [
    'enabled' => $redisEnabled,
    'host' => $redisHost,
    'port' => $redisPort,
    'database' => $redisDb,
];

$config = [
    'id' => 'basic',    
    'bootstrap' => ['log', 'activityLogger'],
    'basePath' => dirname(__DIR__) . '/__modul',
    'runtimePath' => dirname(__DIR__) . '/runtime',
    'vendorPath' => dirname(__DIR__) . '/vendor',
    'language' => 'id',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@asset_luar'   => dirname(dirname(__DIR__)).'',
    ],
    'timeZone' => 'Asia/Jakarta',
    'components' => [
        'fs' => [
            'class' => \diecoding\flysystem\AwsS3Component::class,
            'endpoint' => $s3Endpoint,
            'key' => $s3Key,
            'secret' => $s3Secret,
            'bucket' => $s3Bucket,
            'region' => $s3Region,
            'usePathStyleEndpoint' => $s3UsePathStyle,
        ],
        
        'assetManager' => [ 'bundles' => 
            
            [ 'yii\web\JqueryAsset' => 
                [ 'jsOptions' => 
                    [ 'position' => \yii\web\View::POS_HEAD ],
                ],
               
            ],

           
            
    
        ],

        
        // 'formatter' => [
        //     'timeZone' => 'Asia/Jakarta',
        // ],
        
        // 'reCaptcha' => [
		// 	'class' => 'himiklab\yii2\recaptcha\ReCaptchaConfig',
		// 	'siteKeyV2' => $_ENV['RECAPTCHA2_SITEKEY'],
		// 	'secretV2' => $_ENV['RECAPTCHA2_SECRET'],			
		// ],
        // // 'reCaptcha3' => [
        // //     'class'      => 'kekaadrenalin\recaptcha3\ReCaptcha',
        // //     'site_key' => $_ENV['siteKeyV3'],
        // //     'secret_key' => $_ENV['secretV3'],
        // // ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'kemkes!@#$%^&*()',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'activityLogger' => [
            'class' => 'app\components\ActivityLogger',
        ],
        'cache' => $redisEnabled ? [
            'class' => 'yii\redis\Cache',
            'redis' => 'redis',
            'keyPrefix' => $redisCachePrefix,
        ] : [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => $redisHost,
            'port' => $redisPort,
            'database' => $redisDb,
        ],
        'session' => $redisEnabled ? [
            'class' => 'yii\redis\Session',
            'redis' => 'redis',
            'keyPrefix' => $redisSessionPrefix,
            'cookieParams' => [
                'lifetime' => 3600 * 4,
                'path' => '/',
            ],
            'timeout' => 3600 * 4,
            'useCookies' => true,
            'name' => $redisSessionName,
        ] : [
            'class' => 'yii\web\Session',
            'cookieParams' => ['lifetime' => 3600 * 4],
            'timeout' => 3600*4, //session expire
            'useCookies' => true,
            // Use project-local session directory to avoid system tmp permission issues on Windows
            //'savePath' => dirname(__DIR__) . '/runtime_sessions',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\swiftmailer\Mailer::class,
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
                'port' => $_ENV['MAIL_PORT'] ?? 587,
                'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
                'username' => $_ENV['MAIL_USERNAME'] ?? '',
                'password' => $_ENV['MAIL_PASSWORD'] ?? '',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@runtime/logs/app.log',
                ],
            ],
        ],
        'db' => $db,
        'db_user' => $db_user,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // ── Explicit rules untuk ApiController ─────────────────────────────────
                // Harus SEBELUM custom UrlRule agar tidak bergantung pada query string.
                // UrlRule::parseRequest hanya aktif jika ada query string → tanpa ini,
                // /api/bencana-stats tidak akan dirouting dengan benar di production.
                'api/login'                     => 'api/login',
                'api/register'                  => 'api/register',
                'api/captcha'                   => 'api/captcha',
                'api/regions'                   => 'api/regions',
                // bencana-stats: mapped langsung ke action Yii2 (camelCase → bencana-stats)
                'api/bencana-stats'             => 'api/bencana-stats',
                'api/forgot-password-request'   => 'api/forgot-password-request',
                'api/forgot-password-verify'    => 'api/forgot-password-verify',
                'api/verify-register-otp'       => 'api/verify-register-otp',
                'api/resend-register-otp'       => 'api/resend-register-otp',
                'api/change-password'           => 'api/change-password',
                'api/profile'                   => 'api/profile',
                'api/update-profile'            => 'api/update-profile',
                // Wildcard fallback untuk semua api/* lainnya
                'api/<action:[\w-]+>'           => 'api/<action>',

                'file-upload/upload-file'        => 'file-upload/upload-file',
                'file-upload/render'             => 'file-upload/render',
                'file-upload/lihat-pdf'          => 'file-upload/lihat-pdf',
                'system-setting/index'           => 'system-setting/index',
                'system-setting/serve-image'     => 'system-setting/serve-image',

                // ── Custom DB-based UrlRule (untuk routing halaman lainnya) ─────────────
                ['class' => 'app\\models\\UrlRule', 'connectionID' => 'db'],
            ],

        ],
    ],
'as beforeRequest' => [
    'class' => 'yii\filters\AccessControl',
    'rules' => [
        [
            'allow' => true,
            'roles' => ['?', '@'],
            'matchCallback' => function($rule, $action) {
                // Selalu izinkan OPTIONS request (CORS preflight dari browser)
                if (\Yii::$app->request->isOptions) {
                    return true;
                }

                $controller = $action->controller->id;
                $actionId   = $action->id;

                /**
                 * JWT Authentication untuk request dari Next.js/Vercel
                 *
                 * Yii2 menggunakan PHP session untuk auth, tapi request server-to-server
                 * dari Vercel tidak bisa bawa session cookie (beda domain).
                 * Solusi: baca Authorization: Bearer <token>, validasi JWT, login user.
                 */
                $authHeader = Yii::$app->request->headers->get('Authorization', '');
                $rawToken = '';
                if (strncasecmp($authHeader, 'Bearer ', 7) === 0) {
                    $rawToken = trim(substr($authHeader, 7));
                } else {
                    $rawToken = Yii::$app->request->get('token') ?? '';
                }

                if (
                    Yii::$app->user->isGuest
                    && $rawToken !== ''
                ) {
                    try {
                        $parts = explode('.', $rawToken);
                        if (count($parts) === 3) {
                            $b64 = fn(string $s): string => str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($s));
                            $b64d = fn(string $s): string => base64_decode(str_replace(['-', '_'], ['+', '/'], $s));

                            $header  = $b64d($parts[0]);
                            $payload = $b64d($parts[1]);
                            $sigProvided = $parts[2];

                            $secret = $_ENV['JWT_SECRET'] ?? 'kemkes_puskesmas_jwt_secret_key_2026';
                            $expectedSig = $b64(hash_hmac('sha256', $parts[0] . '.' . $parts[1], $secret, true));

                            if (!hash_equals($expectedSig, $sigProvided)) {
                                $fallbackSecret = Yii::$app->request->cookieValidationKey ?: 'kemkes!@#$%^&*()';
                                $expectedSig = $b64(hash_hmac('sha256', $parts[0] . '.' . $parts[1], $fallbackSecret, true));
                            }
                            if (!hash_equals($expectedSig, $sigProvided)) {
                                $expectedSig = $b64(hash_hmac('sha256', $parts[0] . '.' . $parts[1], 'kemkes!@#$%^&*()', true));
                            }

                            if (hash_equals($expectedSig, $sigProvided)) {
                                $data = json_decode($payload, true);
                                $userId = $data['sub'] ?? null;
                                $exp    = $data['exp'] ?? 0;

                                if ($userId && $exp > time()) {
                                    $user = \app\models\User::findIdentity((int) $userId);
                                    if ($user) {
                                        Yii::$app->user->setIdentity($user);
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        Yii::warning('JWT auth gagal: ' . $e->getMessage(), __METHOD__);
                    }
                }

                // SiteController public actions
                if ($controller === 'site' && in_array($actionId, [
                    'login', 'login-api', 'captcha', 're-captcha',
                    'register', 'register-success', 'register-kabupaten',
                    'verify-email', 'resend-otp', 'waiting-approval', 'error',
                ], true)) return true;

                // ForgotPasswordController
                if ($controller === 'forgot-password' && in_array($actionId, [
                    'request', 'verify',
                ], true)) return true;



                // Dynamic check for public actions from the controller
                if (method_exists($action->controller, 'isActionPublic') && $action->controller->isActionPublic($actionId)) {
                    return true;
                }

                if ($controller === 'file-upload' && in_array($actionId, ['render', 'lihat-pdf'], true)) {
                    return true;
                }

                // UserRegistrationController public actions
                if ($controller === 'user-registration' && in_array($actionId, [
                    'register', 'verify-email', 'resend-otp',
                    'waiting-approval', 'recover-registration', 'edit-registration',
                ], true)) return true;

                // AJAX endpoints
                if (in_array($controller, ['formulir-bencana', 'laporan-kejadian']) &&
                    in_array($actionId, ['get-kabupaten', 'get-kecamatan', 'debug-provinsi', 'db-info', 'ping', 'test-ajax'], true)
                ) return true;

                return false;
            },
        ],
        [
            'allow' => true,
            'roles' => ['@'],
        ],
    ],
    'denyCallback' => function () {
        return Yii::$app->response->redirect(['site/login']);
    },
],

    'modules' => [
        'gridview' => ['class' => 'kartik\grid\Module']
    ],
    'params' => $params,
];

if (!$fsEnabled) {
    unset($config['components']['fs']);
}

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

// Ensure the local session folder exists and is writable. This prevents PHP from trying to use system temp folders
// which on some Windows setups (e.g., Laragon) may not permit the web user to write.
$sessionDir = dirname(__DIR__) . '/runtime_sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0755, true);
}
// Best-effort permissions
if (is_dir($sessionDir)) {
    @chmod($sessionDir, 0755);
}

return $config;
