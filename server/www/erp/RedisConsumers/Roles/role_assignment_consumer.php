<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Model\Database;
use Service\RedisStream\RoleAssignmentConsumer;

$db = new Database();
$redis = $db->getredis();
$pdo = $db->getConnection();

$consumer = new RoleAssignmentConsumer($redis, $pdo);
$consumer->consume();
