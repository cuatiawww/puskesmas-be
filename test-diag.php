<?php
header('Content-Type: application/json');

$results = [];
if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
    $results['user'] = posix_getpwuid(posix_geteuid());
} else {
    $results['user'] = 'posix functions not available';
}
$results['current_dir'] = __DIR__;
$results['uploads_exists'] = is_dir(__DIR__ . '/uploads');
$results['uploads_writable'] = is_writable(__DIR__ . '/uploads');

if (!is_dir(__DIR__ . '/uploads')) {
    $results['mkdir_uploads'] = @mkdir(__DIR__ . '/uploads', 0777, true);
    $results['mkdir_error'] = error_get_last();
} else {
    $results['mkdir_uploads'] = 'already exists';
}

$results['uploads_system_setting_exists'] = is_dir(__DIR__ . '/uploads/system-setting');
if (is_dir(__DIR__ . '/uploads') && !is_dir(__DIR__ . '/uploads/system-setting')) {
    $results['mkdir_system_setting'] = @mkdir(__DIR__ . '/uploads/system-setting', 0777, true);
    $results['mkdir_system_setting_error'] = error_get_last();
}

echo json_encode($results, JSON_PRETTY_PRINT);
