<?php
header('Content-Type: text/plain');
$logFile = __DIR__ . '/runtime/logs/app.log';
if (is_file($logFile)) {
    $lines = file($logFile);
    $last_lines = array_slice($lines, -150);
    echo implode("", $last_lines);
} else {
    echo "Log file not found at " . $logFile;
}
