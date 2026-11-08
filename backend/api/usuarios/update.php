<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/request.php';
require_once __DIR__ . '/../../helpers/auth.php';

$currentUser = requireRole(['ADMIN_SISTEMA']);

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    jsonResponse(405, ['ok' => false, 'message' => 'Método no permitido']);
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    jsonResponse(400, ['ok' => false, 'message' => 'ID de usuario no válido']);
}

$data = readJson();

$usuario = trim((string)($data['usuario'] ?? ''));
$nombre = trim((string)($data['nombre'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$password = (string)($data['password'] ?? '');
$rol = (string)($data['rol'] ?? '');
$activo = (int)($data['activo'] ?? 1);

$rolesValidos = ['ADMIN_SISTEMA', 'ADMIN_SERVICIOS', 'CLIENTE'];

$errors = [];

if ($usuario === '') {
    $errors['usuario'] = 'El usuario es obligatorio';
}
if (strlen($usuario) > 50) {
    $errors['usuario'] = 'El usuario no puede superar 50 caracteres';
}
if ($nombre === '') {
    $errors['nombre'] = 'El nombre es obligatorio';
}
if (strlen($nombre) > 120) {
    $errors['nombre'] = 'El nombre no puede superar 120 caracteres';
}
if ($email === '') {
    $errors['email'] = 'El correo electrónico es obligatorio';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'El correo electrónico no es válido';
}
if (!in_array($rol, $rolesValidos, true)) {
    $errors['rol'] = 'El rol seleccionado no es válido';
}
if (!in_array($activo, [0, 1], true)) {
    $errors['activo'] = 'El estado debe ser activo o inactivo';
}

if ($password !== '' && strlen($password) < 8) {
    $errors['password'] = 'La contraseña debe contener al menos 8 caracteres';
}

if (!empty($errors)) {
    jsonResponse(422, [
        'ok' => false,
        'message' => 'Existen datos incorrectos',
        'errors' => $errors
    ]);
}

try {
    $pdo = getPDO();

    $stmtActual = $pdo->prepare('SELECT id, usuario, nombre, email, rol, activo, password_hash FROM usuarios WHERE id = :id LIMIT 1');
    $stmtActual->execute([':id' => $id]);
    $usuarioActual = $stmtActual->fetch();

    if (!$usuarioActual) {
        jsonResponse(404, ['ok' => false, 'message' => 'El usuario no existe']);
    }

    $stmtDuplicado = $pdo->prepare('SELECT id FROM usuarios WHERE (usuario = :usuario OR email = :email) AND id <> :id LIMIT 1');
    $stmtDuplicado->execute([
        ':usuario' => $usuario,
        ':email' => $email,
        ':id' => $id
    ]);

    if ($stmtDuplicado->fetch()) {
        jsonResponse(409, ['ok' => false, 'message' => 'El usuario o correo electrónico ya se encuentra registrado']);
    }

    if ((int)$currentUser['id'] === (int)$id && $activo === 0) {
        jsonResponse(409, ['ok' => false, 'message' => 'No puede inactivar su propia cuenta']);
    }

    if ($password !== '') {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE usuarios SET
            usuario = :usuario,
            nombre = :nombre,
            email = :email,
            password_hash = :password_hash,
            rol = :rol,
            activo = :activo
            WHERE id = :id');
        $stmt->execute([
            ':usuario' => $usuario,
            ':nombre' => $nombre,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':rol' => $rol,
            ':activo' => $activo,
            ':id' => $id
        ]);
    } else {
        $stmt = $pdo->prepare('UPDATE usuarios SET
            usuario = :usuario,
            nombre = :nombre,
            email = :email,
            rol = :rol,
            activo = :activo
            WHERE id = :id');
        $stmt->execute([
            ':usuario' => $usuario,
            ':nombre' => $nombre,
            ':email' => $email,
            ':rol' => $rol,
            ':activo' => $activo,
            ':id' => $id
        ]);
    }

    $stmtResultado = $pdo->prepare('SELECT id, usuario, nombre, email, rol, activo, created_at, updated_at FROM usuarios WHERE id = :id LIMIT 1');
    $stmtResultado->execute([':id' => $id]);
    $usuarioActualizado = $stmtResultado->fetch();

    jsonResponse(200, [
        'ok' => true,
        'message' => 'Usuario actualizado correctamente',
        'data' => $usuarioActualizado
    ]);
} catch (PDOException $e) {
    jsonResponse(500, ['ok' => false, 'message' => 'Error interno al actualizar el usuario']);
}