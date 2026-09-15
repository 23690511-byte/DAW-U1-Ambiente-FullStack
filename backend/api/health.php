<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $host = getenv('DB_HOST') ?: 'db';
    $db   = getenv('MYSQL_DATABASE') ?: 'appdb';
    $user = getenv('MYSQL_USER') ?: 'appuser';
    $pass = getenv('MYSQL_PASSWORD') ?: 'appsecret';

    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo json_encode([
        "ok" => true,
        "backend" => "PHP funcionando",
        "database" => "MySQL conectado"
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode([
        "ok" => false,
        "backend" => "PHP funcionando",
        "database" => "Error de conexion: " . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}