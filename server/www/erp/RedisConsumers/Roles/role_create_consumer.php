<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Model\Database;
use Service\RedisStream\RoleCreateConsumer;

$db = new Database();
$redis = $db->getredis();
$pdo = $db->getConnection();

$consumer = new RoleCreateConsumer($redis, $pdo);
$consumer->consume();
