<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    $users = [
        [
            'usuario' => 'admin',
            'nombre' => 'Administrador del Sistema',
            'email' => 'admin@demo.local',
            'password' => 'password123',
            'rol' => 'ADMIN_SISTEMA',
            'activo' => 1
        ],
        [
            'usuario' => 'servicios',
            'nombre' => 'Administrador de Servicios',
            'email' => 'servicios@demo.local',
            'password' => 'password123',
            'rol' => 'ADMIN_SERVICIOS',
            'activo' => 1
        ],
        [
            'usuario' => 'cliente',
            'nombre' => 'Cliente Demo',
            'email' => 'cliente@demo.local',
            'password' => 'password123',
            'rol' => 'CLIENTE',
            'activo' => 1
        ],
        [
            'usuario' => 'cliente_inactivo',
            'nombre' => 'Cliente Inactivo',
            'email' => 'inactivo@demo.local',
            'password' => 'password123',
            'rol' => 'CLIENTE',
            'activo' => 0
        ]
    ];

    foreach ($users as $u) {
        $check = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = :usuario LIMIT 1');
        $check->execute([':usuario' => $u['usuario']]);
        $hash = password_hash($u['password'], PASSWORD_DEFAULT);

        if ($row = $check->fetch()) {
            $stmt = $pdo->prepare('UPDATE usuarios SET nombre = :nombre, email = :email, password_hash = :hash, rol = :rol, activo = :activo WHERE id = :id');
            $stmt->execute([
                ':nombre' => $u['nombre'],
                ':email' => $u['email'],
                ':hash' => $hash,
                ':rol' => $u['rol'],
                ':activo' => $u['activo'],
                ':id' => $row['id']
            ]);
            echo "Usuario {$u['usuario']} actualizado.\n";
        } else {
            $stmt = $pdo->prepare('INSERT INTO usuarios (usuario, nombre, email, password_hash, rol, activo) VALUES (:usuario, :nombre, :email, :hash, :rol, :activo)');
            $stmt->execute([
                ':usuario' => $u['usuario'],
                ':nombre' => $u['nombre'],
                ':email' => $u['email'],
                ':hash' => $hash,
                ':rol' => $u['rol'],
                ':activo' => $u['activo']
            ]);
            echo "Usuario {$u['usuario']} insertado.\n";
        }
    }

    echo "=== SEEDING COMPLETADO ===\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
