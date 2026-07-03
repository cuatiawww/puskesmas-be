<?php
require __DIR__ . '/vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;

$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    die("File .env tidak ditemukan di server. Pastikan file ini di-deploy di folder yang sama dengan .env");
}

// 1. Baca konfigurasi sebelum diubah
$dotenvBefore = new Dotenv();
$dotenvBefore->load($envPath);
$oldDb = $_ENV['DB_NAME'] ?? 'Belum diatur';

echo "<h3>1. Konfigurasi Database Saat Ini (Sebelum diubah):</h3>";
echo "Host: " . ($_ENV['DB_ADDR'] ?? 'Belum diset') . "<br>";
echo "Port: " . ($_ENV['DB_PORT'] ?? 'Belum diset') . "<br>";
echo "DB Name: <b>" . $oldDb . "</b><br>";
echo "DB User: " . ($_ENV['DB_USER'] ?? 'Belum diset') . "<br><hr>";

// 2. Baca isi file .env asli untuk diubah
$content = file_get_contents($envPath);

// Kita ganti nilai DB_NAME menjadi db_puskesmas
$pattern = '/^DB_NAME\s*=\s*.*/m';
$replacement = 'DB_NAME=db_puskesmas';

if (preg_match($pattern, $content)) {
    $newContent = preg_replace($pattern, $replacement, $content);
} else {
    $newContent = $content . "\nDB_NAME=db_puskesmas";
}

// 3. Simpan kembali ke file .env
if (file_put_contents($envPath, $newContent)) {
    echo "<h3>2. Status Perubahan:</h3>";
    echo "<span style='color: green; font-weight: bold;'>Sukses mengubah DB_NAME menjadi 'db_puskesmas' di server!</span><br><hr>";
    
    // Bersihkan environment cache PHP untuk reload config baru
    putenv("DB_NAME=db_puskesmas");
    $_ENV['DB_NAME'] = 'db_puskesmas';
    
    echo "<h3>3. Konfigurasi Database Baru:</h3>";
    echo "Host: " . ($_ENV['DB_ADDR'] ?? 'Belum diset') . "<br>";
    echo "Port: " . ($_ENV['DB_PORT'] ?? 'Belum diset') . "<br>";
    echo "DB Name: <b>" . ($_ENV['DB_NAME'] ?? 'Belum diset') . "</b><br>";
    echo "DB User: " . ($_ENV['DB_USER'] ?? 'Belum diset') . "<br>";
} else {
    echo "<h3>2. Status Perubahan:</h3>";
    echo "<span style='color: red; font-weight: bold;'>Gagal menulis ke file .env di server. Periksa hak akses file/folder.</span><br>";
}

