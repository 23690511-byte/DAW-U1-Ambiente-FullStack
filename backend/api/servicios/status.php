<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/request.php';
require_once __DIR__ . '/../../helpers/auth.php';

requireRole(['ADMIN_SISTEMA', 'ADMIN_SERVICIOS']);

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    jsonResponse(405, ['ok' => false, 'message' => 'Método no permitido']);
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    jsonResponse(400, ['ok' => false, 'message' => 'ID de servicio no válido']);
}

$data = readJson();

if (!array_key_exists('activo', $data)) {
    jsonResponse(422, [
        'ok' => false,
        'message' => 'Debe indicar el estado del servicio',
        'errors' => ['activo' => 'El campo activo es obligatorio']
    ]);
}

$activoRaw = $data['activo'];
if (is_bool($activoRaw)) {
    $activo = $activoRaw ? 1 : 0;
} elseif (in_array($activoRaw, [0, 1, '0', '1'], true)) {
    $activo = (int)$activoRaw;
} else {
    jsonResponse(422, [
        'ok' => false,
        'message' => 'Estado de servicio no válido',
        'errors' => ['activo' => 'El estado debe ser 0 o 1']
    ]);
}

try {
    $pdo = getPDO();

    $stmtServicio = $pdo->prepare('SELECT id, nombre, descripcion, precio, duracion_minutos, activo FROM servicios WHERE id = :id LIMIT 1');
    $stmtServicio->execute([':id' => $id]);
    $servicio = $stmtServicio->fetch();

    if (!$servicio) {
        jsonResponse(404, ['ok' => false, 'message' => 'El servicio no existe']);
    }

    if ((int)$servicio['activo'] === $activo) {
        jsonResponse(200, [
            'ok' => true,
            'message' => $activo === 1 ? 'El servicio ya se encuentra activo' : 'El servicio ya se encuentra inactivo',
            'data' => [
                'id' => (int)$servicio['id'],
                'activo' => $activo
            ]
        ]);
    }

    $stmt = $pdo->prepare('UPDATE servicios SET activo = :activo WHERE id = :id');
    $stmt->execute([
        ':activo' => $activo,
        ':id' => $id
    ]);

    $stmtResultado = $pdo->prepare('SELECT id, nombre, descripcion, precio, duracion_minutos, activo, created_at, updated_at FROM servicios WHERE id = :id LIMIT 1');
    $stmtResultado->execute([':id' => $id]);
    $servicioActualizado = $stmtResultado->fetch();

    jsonResponse(200, [
        'ok' => true,
        'message' => $activo === 1 ? 'Servicio activado correctamente' : 'Servicio inactivado correctamente',
        'data' => $servicioActualizado
    ]);
} catch (PDOException $e) {
    jsonResponse(500, ['ok' => false, 'message' => 'Error interno al modificar el estado del servicio']);
}