<?php

require_once __DIR__ . '/vendor/autoload.php';

use Predis\Client;

// Подключаем ранее созданный класс ConsistentHashingRedis
class ConsistentHashingRedis
{
    private $nodes = [];
    private $ring = [];
    private $virtualNodes = 100;

    public function __construct(array $servers)
    {
        foreach ($servers as $server) {
            $this->addNode($server);
        }
        ksort($this->ring);
    }

    public function addNode(array $server)
    {
        $nodeId = $server['host'] . ':' . $server['port'];
        for ($i = 0; $i < $this->virtualNodes; $i++) {
            $hash = $this->hash($nodeId . '#' . $i);
            $this->ring[$hash] = $nodeId;
        }
        $this->nodes[$nodeId] = new Client($server);
    }

    public function getNode(string $key): Client
    {
        $hash = $this->hash($key);
        foreach ($this->ring as $nodeHash => $nodeId) {
            if ($hash <= $nodeHash) {
                return $this->nodes[$nodeId];
            }
        }
        return $this->nodes[reset($this->ring)];
    }

    public function set(string $key, string $value)
    {
        $node = $this->getNode($key);
        return $node->set($key, $value);
    }

    public function get(string $key)
    {
        $node = $this->getNode($key);
        return $node->get($key);
    }

    public function delete(string $key)
    {
        $node = $this->getNode($key);
        return $node->del($key);
    }

    private function hash(string $value): int
    {
        return hexdec(substr(hash('sha256', $value), 0, 8));
    }
}

// Конфигурация Redis-нод
$redisServers = [
    ['host' => 'redis-node1', 'port' => 6379],
    ['host' => 'redis-node2', 'port' => 6379],
    ['host' => 'redis-node3', 'port' => 6379],
];

try {
    // Создаем объект ConsistentHashingRedis
    $redisCluster = new ConsistentHashingRedis($redisServers);

    // Тест записи
    $redisCluster->set('test_key', 'test_value');
    echo "Value: " . $redisCluster->get('test_key') . "\n";

    // Удаление ключа
    $redisCluster->delete('test_key');
    echo "Key deleted successfully.\n";

} catch (\Exception $e) {
    echo "Redis operation failed: " . $e->getMessage() . "\n";
}
