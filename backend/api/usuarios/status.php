<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/request.php';
require_once __DIR__ . '/../../helpers/auth.php';

$currentUser = requireRole(['ADMIN_SISTEMA']);

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    jsonResponse(405, ['ok' => false, 'message' => 'Método no permitido']);
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    jsonResponse(400, ['ok' => false, 'message' => 'ID de usuario no válido']);
}

$data = readJson();

if (!array_key_exists('activo', $data)) {
    jsonResponse(422, [
        'ok' => false,
        'message' => 'Debe indicar el estado del usuario',
        'errors' => ['activo' => 'El campo activo es obligatorio']
    ]);
}

if (!in_array($data['activo'], [0, 1], true)) {
    jsonResponse(422, [
        'ok' => false,
        'message' => 'Estado de usuario no válido',
        'errors' => ['activo' => 'El estado debe ser 0 o 1']
    ]);
}

$activo = (int)$data['activo'];

try {
    $pdo = getPDO();

    $stmtUsuario = $pdo->prepare('SELECT id, usuario, nombre, email, rol, activo FROM usuarios WHERE id = :id LIMIT 1');
    $stmtUsuario->execute([':id' => $id]);
    $usuario = $stmtUsuario->fetch();

    if (!$usuario) {
        jsonResponse(404, ['ok' => false, 'message' => 'El usuario no existe']);
    }

    if ((int)$currentUser['id'] === (int)$id && $activo === 0) {
        jsonResponse(409, ['ok' => false, 'message' => 'No puede inactivar su propia cuenta']);
    }

    if ((int)$usuario['activo'] === $activo) {
        jsonResponse(200, [
            'ok' => true,
            'message' => $activo === 1 ? 'El usuario ya se encuentra activo' : 'El usuario ya se encuentra inactivo',
            'data' => [
                'id' => (int)$usuario['id'],
                'activo' => $activo
            ]
        ]);
    }

    if ($usuario['rol'] === 'ADMIN_SISTEMA' && $activo === 0) {
        $stmtAdmins = $pdo->prepare('SELECT COUNT(*) AS total FROM usuarios WHERE rol = :rol AND activo = 1 AND id <> :id');
        $stmtAdmins->execute([
            ':rol' => 'ADMIN_SISTEMA',
            ':id' => $id
        ]);
        $resultado = $stmtAdmins->fetch();
        $totalAdmins = (int)$resultado['total'];

        if ($totalAdmins === 0) {
            jsonResponse(409, [
                'ok' => false,
                'message' => 'No es posible inactivar al último administrador activo del sistema'
            ]);
        }
    }

    $stmt = $pdo->prepare('UPDATE usuarios SET activo = :activo WHERE id = :id');
    $stmt->execute([
        ':activo' => $activo,
        ':id' => $id
    ]);

    $stmtResultado = $pdo->prepare('SELECT id, usuario, nombre, email, rol, activo, created_at, updated_at FROM usuarios WHERE id = :id LIMIT 1');
    $stmtResultado->execute([':id' => $id]);
    $usuarioActualizado = $stmtResultado->fetch();

    jsonResponse(200, [
        'ok' => true,
        'message' => $activo === 1 ? 'Usuario activado correctamente' : 'Usuario inactivado correctamente',
        'data' => $usuarioActualizado
    ]);
} catch (PDOException $e) {
    jsonResponse(500, ['ok' => false, 'message' => 'Error interno al modificar el estado del usuario']);
}