<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV')   or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
$dotenv = new Dotenv();
try {
    $dotenv->load(__DIR__.'/.env');
} catch (\Throwable $e) {
    echo "Gagal memuat file .env: " . $e->getMessage() . "<br><br>";
}

// Ambil konfigurasi dari ENV
$mailHost = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
$mailPort = $_ENV['MAIL_PORT'] ?? 587;
$mailEncryption = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
$mailUsername = $_ENV['MAIL_USERNAME'] ?? '';
$mailPassword = $_ENV['MAIL_PASSWORD'] ?? '';
$mailFrom = $_ENV['MAIL_FROM_ADDRESS'] ?? '';
$mailFromName = $_ENV['MAIL_FROM_NAME'] ?? 'Puskesmas Notification';

// Target email penerima
$to = $_GET['to'] ?? 'test@example.com';

echo "<h1>🧪 SMTP Email Connection Test</h1>";
echo "<h3>Konfigurasi dari .env:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> $mailHost</li>";
echo "<li><strong>Port:</strong> $mailPort</li>";
echo "<li><strong>Encryption:</strong> $mailEncryption</li>";
echo "<li><strong>Username:</strong> " . htmlspecialchars($mailUsername, ENT_QUOTES, 'UTF-8') . "</li>";
echo "<li><strong>Password:</strong> " . (empty($mailPassword) ? '(Kosong)' : htmlspecialchars($mailPassword, ENT_QUOTES, 'UTF-8')) . "</li>";
echo "<li><strong>From:</strong> " . htmlspecialchars($mailFrom, ENT_QUOTES, 'UTF-8') . " (" . htmlspecialchars($mailFromName, ENT_QUOTES, 'UTF-8') . ")</li>";
echo "<li><strong>Target Penerima:</strong> $to</li>";
echo "</ul>";

echo "<h3>Mulai Pengujian...</h3>";

try {
    // 1. Buat Swift Smtp Transport
    echo "Menyiapkan Swift_SmtpTransport...<br>";
    $transport = new \Swift_SmtpTransport($mailHost, $mailPort, $mailEncryption);
    $transport->setUsername($mailUsername);
    $transport->setPassword($mailPassword);

    // 2. Buat Mailer
    $mailer = new \Swift_Mailer($transport);

    // 3. Buat Message
    echo "Membuat email test...<br>";
    $message = new \Swift_Message('Test Koneksi SMTP Puskesmas');
    $message->setFrom([$mailFrom => $mailFromName]);
    $message->setTo($to);
    $message->setBody('Halo! Ini adalah email uji coba untuk mengetes apakah koneksi SMTP pada server Anda berjalan dengan baik.', 'text/html');

    // 4. Kirim
    echo "Mencoba mengirim email ke $to...<br>";
    $sent = $mailer->send($message);

    if ($sent) {
        echo "<h2 style='color:green;'>✓ SUKSES! Email berhasil terkirim.</h2>";
    } else {
        echo "<h2 style='color:red;'>✗ GAGAL! Mailer mengembalikan false.</h2>";
    }
} catch (\Throwable $e) {
    echo "<h2 style='color:red;'>✗ ERROR! Terjadi pengecualian (Exception) saat mengirim email:</h2>";
    echo "<pre style='background:#f4f4f4; padding:15px; border:1px solid #ddd; overflow:auto;'>";
    echo "Pesan Error: " . $e->getMessage() . "\n\n";
    echo "File: " . $e->getFile() . " (Baris: " . $e->getLine() . ")\n\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}
