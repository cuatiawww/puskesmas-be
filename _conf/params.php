<?php

// URL dashboard Next.js (frontend).
define('DASHBOARD_URL', $_ENV['PUSKESMAS_FRONTEND_URL'] ?? 'https://puskes-kappa.vercel.app');

return [
    'adminEmail'   => 'admin@example.com',
    'senderEmail'  => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com',
    'senderName'   => $_ENV['MAIL_FROM_NAME'] ?? 'Puskesmas Notification',
    'base_url'     => '',
    'frontend_url' => DASHBOARD_URL,
];

?>
