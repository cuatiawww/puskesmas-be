<?php
// DEBUG FILE - HAPUS SETELAH SELESAI
// Akses: https://puskesmas-be.mediaciptainformasi.co.id/debug-server.php

$results = [];

// 1. Cek PHP version & config
$results['php_version'] = PHP_VERSION;
$results['upload_max_filesize'] = ini_get('upload_max_filesize');
$results['post_max_size'] = ini_get('post_max_size');
$results['max_file_uploads'] = ini_get('max_file_uploads');

// 2. Cek webroot
$webroot = realpath(__DIR__);
$results['webroot'] = $webroot;

// 3. Cek apakah uploads/ ada
$uploadsDir = $webroot . '/uploads';
$results['uploads_exists'] = is_dir($uploadsDir);
$results['uploads_writable'] = is_dir($uploadsDir) && is_writable($uploadsDir);

// 4. Coba buat direktori uploads/system-setting/
$targetDir = $webroot . '/uploads/system-setting';
if (!is_dir($targetDir)) {
    $mkdirResult = @mkdir($targetDir, 0775, true);
    $results['mkdir_result'] = $mkdirResult ? 'SUKSES' : 'GAGAL';
    $results['mkdir_error'] = error_get_last();
} else {
    $results['mkdir_result'] = 'DIR SUDAH ADA';
}
$results['target_dir_exists'] = is_dir($targetDir);
$results['target_dir_writable'] = is_dir($targetDir) && is_writable($targetDir);

// 5. Coba tulis file test
if (is_dir($targetDir)) {
    $testFile = $targetDir . '/test_write_' . time() . '.txt';
    $writeResult = @file_put_contents($testFile, 'test');
    $results['write_test'] = ($writeResult !== false) ? 'SUKSES (bytes: ' . $writeResult . ')' : 'GAGAL';
    $results['write_error'] = error_get_last();
    if ($writeResult !== false) {
        @unlink($testFile);
        $results['write_cleanup'] = 'file test dihapus';
    }
}

// 6. Cek /tmp
$tmpDir = sys_get_temp_dir();
$results['tmp_dir'] = $tmpDir;
$results['tmp_writable'] = is_writable($tmpDir);

// 7. Cek user PHP
$results['php_user'] = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] ?? 'unknown' : (function_exists('get_current_user') ? get_current_user() : 'unknown');

// 8. Cek app log
$logFile = __DIR__ . '/runtime/logs/app.log';
$results['log_exists'] = file_exists($logFile);
$results['log_size'] = file_exists($logFile) ? filesize($logFile) . ' bytes' : 'N/A';

// 9. Baca 50 baris terakhir log kalau ada
$lastLog = '';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    $lastLog = implode('', $lastLines);
}

// Output
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Debug Server</title>';
echo '<style>body{font-family:monospace;background:#111;color:#0f0;padding:20px}';
echo '.ok{color:#0f0}.err{color:#f55}.warn{color:#fa0}';
echo 'table{border-collapse:collapse;width:100%}td,th{border:1px solid #333;padding:8px;text-align:left}';
echo 'pre{background:#000;padding:15px;overflow:auto;max-height:400px}';
echo '</style></head><body>';
echo '<h2>🔍 Server Debug Info</h2>';
echo '<table>';
foreach ($results as $key => $val) {
    $display = is_array($val) ? json_encode($val) : (string)$val;
    $class = '';
    if (str_contains($display, 'GAGAL') || str_contains($display, 'false')) $class = 'err';
    elseif (str_contains($display, 'SUKSES') || str_contains($display, 'true') || str_contains($display, 'ADA')) $class = 'ok';
    echo "<tr><td><b>$key</b></td><td class='$class'>$display</td></tr>";
}
echo '</table>';

if ($lastLog) {
    echo '<h3>📋 50 Baris Terakhir Log Yii:</h3>';
    echo '<pre>' . htmlspecialchars($lastLog) . '</pre>';
} else {
    echo '<p class="warn">⚠️ Log file tidak ditemukan atau kosong</p>';
}

echo '<br><p style="color:#555">Hapus file ini setelah selesai debugging!</p>';
echo '</body></html>';
