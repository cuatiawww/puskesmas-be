<?php
header('Content-Type: text/plain');

function print_file_tail($path, $lines_count = 50) {
    echo "=== TAIL OF $path ===\n";
    if (!is_file($path)) {
        echo "File does not exist.\n\n";
        return;
    }
    if (!is_readable($path)) {
        echo "File is not readable.\n\n";
        return;
    }
    $lines = file($path);
    $last_lines = array_slice($lines, -$lines_count);
    echo implode("", $last_lines);
    echo "\n===================================\n\n";
}

// Check Nginx and PHP log paths
print_file_tail('/var/log/nginx/error.log', 100);
print_file_tail('/var/log/php/error.log', 100);
print_file_tail('/var/log/php-fpm.log', 100);
print_file_tail('/var/log/supervisord/supervisord.log', 100);

echo "=== Files in /var/log ===\n";
foreach (glob('/var/log/*') as $file) {
    echo $file . (is_dir($file) ? '/' : '') . "\n";
}
echo "=== Files in /var/log/nginx ===\n";
foreach (glob('/var/log/nginx/*') as $file) {
    echo $file . "\n";
}
