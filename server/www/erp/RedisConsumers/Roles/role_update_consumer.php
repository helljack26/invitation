<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Model\Database;
use Service\RedisStream\RoleUpdateConsumer;

$db = new Database();
$redis = $db->getredis();
$pdo = $db->getConnection();

$consumer = new RoleUpdateConsumer($redis, $pdo);
$consumer->consume();
