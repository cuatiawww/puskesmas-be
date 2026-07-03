<?php
// Tampilkan error langsung ke layar menggunakan raw PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    @header("HTTP/1.1 200 OK");
    echo "<div style='padding: 20px; background: #fff3cd; color: #856404; border: 1px solid #ffeeba; font-family: monospace;'>";
    echo "<h2>[RAW PHP ERROR]</h2>";
    echo "<strong>Pesan:</strong> $message<br>";
    echo "<strong>File:</strong> $file (Baris: $line)<br>";
    echo "</div>";
    exit;
});

set_exception_handler(function($exception) {
    @header("HTTP/1.1 200 OK");
    echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; font-family: monospace;'>";
    echo "<h2>[RAW PHP EXCEPTION]</h2>";
    echo "<strong>Pesan:</strong> " . $exception->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $exception->getFile() . " (Baris: " . $exception->getLine() . ")<br>";
    echo "<h3>Stack Trace:</h3><pre>" . $exception->getTraceAsString() . "</pre>";
    echo "</div>";
    exit;
});

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'prod');

//error_reporting('E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED & ~E_UNDEFINED');


require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/_conf/web.php';

$application = new yii\web\Application($config);
if (YII_DEBUG) {
    Yii::$app->errorHandler->unregister();
}
$application->run();
