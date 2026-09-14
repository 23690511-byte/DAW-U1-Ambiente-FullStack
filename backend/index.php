<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

echo json_encode([
    "status" => "success",
    "message" => "Backend PHP funcionando correctamente",
    "timestamp" => date("Y-m-d H:i:s")
]);