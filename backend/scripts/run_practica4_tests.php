<?php
declare(strict_types=1);

function makeRequest(string $method, string $url, ?array $body = null, ?string $cookieJar = null): array
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = ['Accept: application/json'];
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($cookieJar !== null) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode((string)$response, true);
    return ['status' => $status, 'data' => $data, 'raw' => $response];
}

$baseUrl = 'http://localhost';
$testsPassed = 0;
$testsFailed = 0;

function assertTest(string $name, bool $condition, string $detail = '') {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "[PASS] $name\n";
        $testsPassed++;
    } else {
        echo "[FAIL] $name: $detail\n";
        $testsFailed++;
    }
}

echo "=== INICIANDO PRUEBAS DE PRACTICA 4 ===\n\n";

// 1. Cliente inactivo intenta login -> Rechazado (HTTP 401)
$res = makeRequest('POST', "$baseUrl/api/login.php", [
    'usuario' => 'cliente_inactivo',
    'password' => 'password123'
]);
assertTest("Cliente inactivo intenta login", $res['status'] === 401 && ($res['data']['ok'] ?? true) === false, "Status {$res['status']}");

// 2. Cliente login exitoso
$clienteCookie = tempnam(sys_get_temp_dir(), 'ck_cli_');
$res = makeRequest('POST', "$baseUrl/api/login.php", [
    'usuario' => 'cliente',
    'password' => 'password123'
], $clienteCookie);
assertTest("Cliente login", $res['status'] === 200 && ($res['data']['ok'] ?? false) === true, "Status {$res['status']}");

// 3. Cliente intenta API usuarios -> HTTP 403
$res = makeRequest('GET', "$baseUrl/api/usuarios/index.php", null, $clienteCookie);
assertTest("Cliente intenta API usuarios", $res['status'] === 403 && ($res['data']['ok'] ?? true) === false, "Status {$res['status']}");

// 4. Cliente registra servicio -> HTTP 403
$res = makeRequest('POST', "$baseUrl/api/servicios/store.php", [
    'nombre' => 'Servicio Hax',
    'descripcion' => 'Prueba indebida',
    'precio' => 100,
    'duracion_minutos' => 30,
    'activo' => 1
], $clienteCookie);
assertTest("Cliente registra servicio", $res['status'] === 403, "Status {$res['status']}");

// 5. Servicio inactivo aparece al cliente -> No
$res = makeRequest('GET', "$baseUrl/api/servicios/index.php", null, $clienteCookie);
$servicios = $res['data']['data'] ?? [];
$tieneInactivos = false;
foreach ($servicios as $s) {
    if ((int)$s['activo'] === 0) {
        $tieneInactivos = true;
        break;
    }
}
assertTest("Servicio inactivo aparece al cliente (No)", $res['status'] === 200 && !$tieneInactivos && count($servicios) > 0, "Tiene inactivos: " . var_export($tieneInactivos, true));

// 6. Admin Servicios login
$serviciosCookie = tempnam(sys_get_temp_dir(), 'ck_srv_');
$res = makeRequest('POST', "$baseUrl/api/login.php", [
    'usuario' => 'servicios',
    'password' => 'password123'
], $serviciosCookie);
assertTest("Admin Servicios login", $res['status'] === 200, "Status {$res['status']}");

// 7. Admin Servicios entra a Usuarios -> HTTP 403 (Bloqueado)
$res = makeRequest('GET', "$baseUrl/api/usuarios/index.php", null, $serviciosCookie);
assertTest("Admin Servicios entra a Usuarios (Bloqueado)", $res['status'] === 403, "Status {$res['status']}");

// 8. Servicio inactivo aparece al administrador -> Sí
$res = makeRequest('GET', "$baseUrl/api/servicios/index.php", null, $serviciosCookie);
$serviciosAdmin = $res['data']['data'] ?? [];
$adminTieneInactivos = false;
foreach ($serviciosAdmin as $s) {
    if ((int)$s['activo'] === 0) {
        $adminTieneInactivos = true;
        break;
    }
}
assertTest("Servicio inactivo aparece al administrador (Sí)", $res['status'] === 200 && $adminTieneInactivos, "Admin servicios count: " . count($serviciosAdmin));

// 9. Admin Servicios registra servicio -> Permitido
$nombreServicioNuevo = 'Servicio Test ' . time();
$res = makeRequest('POST', "$baseUrl/api/servicios/store.php", [
    'nombre' => $nombreServicioNuevo,
    'descripcion' => 'Descripción del servicio de prueba',
    'precio' => 1999.99,
    'duracion_minutos' => 45,
    'activo' => 1
], $serviciosCookie);
assertTest("Admin Servicios registra servicio", $res['status'] === 201 && ($res['data']['ok'] ?? false) === true, "Status {$res['status']}");
$nuevoServicioId = $res['data']['data']['id'] ?? null;

// 10. Admin Sistema login
$adminCookie = tempnam(sys_get_temp_dir(), 'ck_adm_');
$res = makeRequest('POST', "$baseUrl/api/login.php", [
    'usuario' => 'admin',
    'password' => 'password123'
], $adminCookie);
assertTest("Admin Sistema login", $res['status'] === 200, "Status {$res['status']}");

// 11. Admin Sistema consulta usuarios -> Permitido
$res = makeRequest('GET', "$baseUrl/api/usuarios/index.php", null, $adminCookie);
assertTest("Admin Sistema consulta usuarios", $res['status'] === 200 && count($res['data']['data'] ?? []) >= 4, "Status {$res['status']}");

// 12. Email incorrecto -> HTTP 422
$res = makeRequest('POST', "$baseUrl/api/usuarios/store.php", [
    'usuario' => 'testinvalido',
    'nombre' => 'Test Invalido',
    'email' => 'correo-invalido-sin-arroba',
    'password' => 'password123',
    'rol' => 'CLIENTE',
    'activo' => 1
], $adminCookie);
assertTest("Email incorrecto (HTTP 422)", $res['status'] === 422 && isset($res['data']['errors']['email']), "Status {$res['status']}");

// 13. Admin Sistema registra usuario -> Permitido
$usuarioNuevo = 'user_' . time();
$emailNuevo = "user_" . time() . "@demo.local";
$res = makeRequest('POST', "$baseUrl/api/usuarios/store.php", [
    'usuario' => $usuarioNuevo,
    'nombre' => 'Usuario Nuevo Test',
    'email' => $emailNuevo,
    'password' => 'password123',
    'rol' => 'CLIENTE',
    'activo' => 1
], $adminCookie);
assertTest("Admin Sistema registra usuario", $res['status'] === 201 && ($res['data']['ok'] ?? false) === true, "Status {$res['status']}");
$nuevoUsuarioId = $res['data']['data']['id'] ?? null;

// 14. Usuario duplicado -> HTTP 409
$res = makeRequest('POST', "$baseUrl/api/usuarios/store.php", [
    'usuario' => $usuarioNuevo,
    'nombre' => 'Otro Con Mismo Nick',
    'email' => 'otromail@demo.local',
    'password' => 'password123',
    'rol' => 'CLIENTE',
    'activo' => 1
], $adminCookie);
assertTest("Usuario duplicado (HTTP 409)", $res['status'] === 409, "Status {$res['status']}");

// 15. Evitar que administrador se inactive a sí mismo -> HTTP 409
$res = makeRequest('PATCH', "$baseUrl/api/usuarios/status.php?id=1", [
    'activo' => 0
], $adminCookie);
assertTest("Admin no puede inactivar su propia cuenta", $res['status'] === 409, "Status {$res['status']}");

// 16. Inactivar y activar usuario creado
$res = makeRequest('PATCH', "$baseUrl/api/usuarios/status.php?id=$nuevoUsuarioId", [
    'activo' => 0
], $adminCookie);
assertTest("Inactivar usuario", $res['status'] === 200 && (int)$res['data']['data']['activo'] === 0, "Status {$res['status']}");

$res = makeRequest('PATCH', "$baseUrl/api/usuarios/status.php?id=$nuevoUsuarioId", [
    'activo' => 1
], $adminCookie);
assertTest("Activar usuario", $res['status'] === 200 && (int)$res['data']['data']['activo'] === 1, "Status {$res['status']}");

// 17. Inactivar servicio
if ($nuevoServicioId) {
    $res = makeRequest('PATCH', "$baseUrl/api/servicios/status.php?id=$nuevoServicioId", [
        'activo' => 0
    ], $serviciosCookie);
    assertTest("Inactivar servicio", $res['status'] === 200 && (int)$res['data']['data']['activo'] === 0, "Status {$res['status']}");
}

echo "\n=== RESULTADO FINAL: $testsPassed pasadas, $testsFailed falladas ===\n";

if ($testsFailed > 0) {
    exit(1);
}
