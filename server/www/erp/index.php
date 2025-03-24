<?php
header("Access-Control-Allow-Origin: http://127.0.0.1:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, X-Requested-With, Authorization");
header("Access-Control-Allow-Credentials: true");

// Загрузка автозагрузчика классов и начальной загрузки приложения.
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Routes.php';
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
