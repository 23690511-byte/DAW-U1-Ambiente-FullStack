<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/request.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, [
        'ok' => false,
        'message' => 'Método no permitido'
    ]);
}

$data = readJson();

$usuario = trim((string)($data['usuario'] ?? ''));
$contrasena = (string)($data['contrasena'] ?? ($data['password'] ?? ''));

if ($usuario === '' || $contrasena === '') {
    jsonResponse(400, [
        'ok' => false,
        'message' => 'Usuario y contraseña son obligatorios'
    ]);
}

try {
    $pdo = getPDO();
    $sql = 'SELECT id, usuario, nombre, email, password_hash, rol, activo
            FROM usuarios
            WHERE usuario = :usuario
            LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':usuario' => $usuario]);
    $user = $stmt->fetch();

    if (!$user || (int)$user['activo'] !== 1 || !password_verify($contrasena, $user['password_hash'])) {
        jsonResponse(401, [
            'ok' => false,
            'message' => 'Usuario o contraseña incorrectos'
        ]);
    }

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'usuario' => $user['usuario'],
        'nombre' => $user['nombre'],
        'email' => $user['email'],
        'rol' => $user['rol']
    ];

    jsonResponse(200, [
        'ok' => true,
        'user' => $_SESSION['user'],
        'data' => $_SESSION['user']
    ]);
} catch (PDOException $e) {
    jsonResponse(500, [
        'ok' => false,
        'message' => 'Error interno del servidor'
    ]);
}
