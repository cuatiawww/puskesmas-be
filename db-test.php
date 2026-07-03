<?php
require __DIR__ . '/vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
if (file_exists(__DIR__ . '/.env')) {
    $dotenv->load(__DIR__ . '/.env');
}

echo "=== DATABASE CONFIG SERVER ===<br>";
echo "Host: " . ($_ENV['DB_ADDR'] ?? 'Belum diset') . "<br>";
echo "Port: " . ($_ENV['DB_PORT'] ?? 'Belum diset') . "<br>";
echo "DB Name: " . ($_ENV['DB_NAME'] ?? 'Belum diset') . "<br>";
echo "DB User: " . ($_ENV['DB_USER'] ?? 'Belum diset') . "<br>";
echo "DB Pass: " . ($_ENV['DB_PASS'] ?? 'Belum diset') . "<br>";
