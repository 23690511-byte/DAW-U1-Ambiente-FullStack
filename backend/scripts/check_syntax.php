<?php
declare(strict_types=1);

$dirs = [
    '/var/www/html/config',
    '/var/www/html/helpers',
    '/var/www/html/api',
    '/var/www/html/api/usuarios',
    '/var/www/html/api/servicios'
];

$allOk = true;
foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $files = glob($dir . '/*.php');
    foreach ($files as $file) {
        $output = [];
        $returnVar = 0;
        exec('php -l ' . escapeshellarg($file), $output, $returnVar);
        echo implode(PHP_EOL, $output) . PHP_EOL;
        if ($returnVar !== 0) {
            $allOk = false;
        }
    }
}

if ($allOk) {
    echo "=== ALL PHP FILES SYNTAX CHECK PASSED ===" . PHP_EOL;
} else {
    echo "=== ERRORS DETECTED ===" . PHP_EOL;
    exit(1);
}
