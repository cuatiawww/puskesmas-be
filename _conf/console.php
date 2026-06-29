<?php

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

$params['storage'] = [
    'driver' => $fsEnabled ? 's3' : 'local',
    's3Enabled' => $fsEnabled,
    'bucket' => $s3Bucket,
    'endpoint' => $s3Endpoint,
];

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__) . '/__modul',
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'runtimePath' => dirname(__DIR__) . '/runtime',
    'vendorPath' => dirname(__DIR__) . '/vendor',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
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
        'cache' => $redisEnabled ? [
            'class' => 'yii\redis\Cache',
            'redis' => 'redis',
            'keyPrefix' => 'cache:sipkk:console:',
        ] : [
            'class' => 'yii\caching\FileCache',
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => $redisHost,
            'port' => $redisPort,
            'database' => $redisDb,
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'db_user' => $db_user,
    ],
    'params' => $params,
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => '@app/migrations',
        ],
        /*
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
        */
    ],
];

if (!$fsEnabled) {
    unset($config['components']['fs']);
}

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
