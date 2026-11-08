<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../helpers/response.php';

if (isset($_SESSION['user'])) {
    jsonResponse(200, [
        'ok' => true,
        'authenticated' => true,
        'user' => $_SESSION['user'],
        'data' => $_SESSION['user']
    ]);
}

jsonResponse(200, [
    'ok' => false,
    'authenticated' => false,
    'user' => null,
    'data' => null
]);
