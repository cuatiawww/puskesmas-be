<?php

// URL dashboard Next.js (frontend).
// Hardcoded untuk sekarang — ganti dengan env variable saat production siap.
define('DASHBOARD_URL', 'https://dashboard-eoc.vercel.app');

return [
    'adminEmail'   => 'admin@example.com',
    'senderEmail'  => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com',
    'senderName'   => $_ENV['MAIL_FROM_NAME'] ?? 'Puskesmas Notification',
    'base_url'     => '//' . $_SERVER['HTTP_HOST'] . '/puskesmas',
    'frontend_url' => DASHBOARD_URL,
];

?>
