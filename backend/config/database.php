<?php
declare(strict_types=1);

function getPDO(): PDO
{
    $host = getenv('DB_HOST') ?: 'db';
    $database = getenv('DB_NAME') ?: (getenv('MYSQL_DATABASE') ?: 'appdb');
    $user = getenv('DB_USER') ?: (getenv('MYSQL_USER') ?: 'appuser');
    $password = getenv('DB_PASSWORD') ?: (getenv('MYSQL_PASSWORD') ?: 'appsecret');

    if ($database === '' || $user === '') {
        throw new RuntimeException(
            'Configuración de base de datos incompleta.'
        );
    }

    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    return new PDO($dsn, $user, $password, $options);
}
