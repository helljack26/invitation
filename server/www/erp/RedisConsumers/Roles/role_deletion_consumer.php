<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Model\Database;
use Service\RedisStream\RoleDeletionConsumer;

$db = new Database();
$redis = $db->getredis();
$pdo = $db->getConnection();

$consumer = new RoleDeletionConsumer($redis, $pdo);
$consumer->consume();
