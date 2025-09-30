<?php
header("Access-Control-Allow-Origin: https://maria-dima-wedding.com.ua");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, X-Requested-With, Authorization");
header("Access-Control-Allow-Credentials: true");

// Загрузка автозагрузчика классов и начальной загрузки приложения.
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Routes.php';
require 'vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
