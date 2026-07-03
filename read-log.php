<?php
// Baca log Yii dari server - HAPUS SETELAH SELESAI DEBUGGING
$logFile = __DIR__ . '/runtime/logs/app.log';

if (!file_exists($logFile)) {
    echo "Log file tidak ditemukan di: $logFile";
    exit;
}

// Ambil 100 baris terakhir
$lines = file($logFile);
$lastLines = array_slice($lines, -100);

echo "<pre style='background:#111;color:#0f0;padding:20px;font-family:monospace;font-size:12px;overflow:auto;'>";
echo "=== 100 BARIS TERAKHIR LOG YIISERVER ===\n\n";
foreach ($lastLines as $line) {
    echo htmlspecialchars($line);
}
echo "</pre>";
