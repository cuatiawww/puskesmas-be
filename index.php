<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

defined('YII_DEBUG') or define('YII_DEBUG', getenv('YII_DEBUG') === '1');
defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV') ?: 'prod');

//error_reporting('E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED & ~E_UNDEFINED');


require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/_conf/web.php';

(new yii\web\Application($config))->run();
