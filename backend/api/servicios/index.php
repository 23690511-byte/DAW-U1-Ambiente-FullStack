<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(405, ['ok' => false, 'message' => 'Método no permitido']);
}

$filtroActivo = null;
if (isset($_GET['activo']) && $_GET['activo'] !== '') {
    $valorActivo = filter_var($_GET['activo'], FILTER_VALIDATE_INT);
    if (!in_array($valorActivo, [0, 1], true)) {
        jsonResponse(400, ['ok' => false, 'message' => 'El filtro activo debe ser 0 o 1']);
    }
    $filtroActivo = $valorActivo;
}

try {
    $pdo = getPDO();
    $sql = 'SELECT id, nombre, descripcion, precio, duracion_minutos, activo, created_at, updated_at FROM servicios';
    $params = [];

    if ($user['rol'] === 'CLIENTE') {
        $sql .= ' WHERE activo = :activo';
        $params[':activo'] = 1;
    } elseif (in_array($user['rol'], ['ADMIN_SISTEMA', 'ADMIN_SERVICIOS'], true)) {
        if ($filtroActivo !== null) {
            $sql .= ' WHERE activo = :activo';
            $params[':activo'] = $filtroActivo;
        }
    } else {
        jsonResponse(403, ['ok' => false, 'message' => 'El rol del usuario no tiene permiso para consultar servicios']);
    }

    $sql .= ' ORDER BY nombre ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $servicios = $stmt->fetchAll();

    jsonResponse(200, [
        'ok' => true,
        'data' => $servicios,
        'meta' => [
            'total' => count($servicios),
            'rol' => $user['rol'],
            'filtroActivo' => $user['rol'] === 'CLIENTE' ? 1 : $filtroActivo
        ]
    ]);
} catch (PDOException $e) {
    jsonResponse(500, ['ok' => false, 'message' => 'Error interno al consultar los servicios']);
}