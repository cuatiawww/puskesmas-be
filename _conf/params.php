<?php

// URL dashboard Next.js (frontend) secara otomatis berdasarkan host aktif.
$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = ($host === 'localhost' || strpos($host, '127.0.0.1') !== false || strpos($host, '192.168.') !== false);
define('DASHBOARD_URL', $isLocal ? 'http://localhost:3000' : 'https://puskes-kappa.vercel.app');

$baseUrl = '';
if (isset($_SERVER['SCRIPT_NAME']) && PHP_SAPI !== 'cli') {
    $baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if ($baseUrl === '/' || $baseUrl === '\\' || $baseUrl === '.') {
        $baseUrl = '';
    }
}

return [
    'adminEmail'   => 'admin@example.com',
    'senderEmail'  => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com',
    'senderName'   => $_ENV['MAIL_FROM_NAME'] ?? 'Puskesmas Notification',
    'base_url'     => $baseUrl,
    'frontend_url' => DASHBOARD_URL,
];

?>
