<?php
namespace Service;

use Predis\Client;
use Exception;

/**
 * Класс ConsistentHashingRedis реализует консистентное хеширование для распределения ключей между несколькими узлами Redis.
 */
class ConsistentHashingRedis
{
    /**
     * Массив клиентов Redis.
     *
     * @var Client[]
     */
    private $clients = [];

    /**
     * Размер виртуальных узлов для каждого физического узла Redis.
     *
     * @var int
     */
    private $virtualNodes;

    /**
     * Хеш-кольцо, где ключи — хеши виртуальных узлов, а значения — соответствующие клиенты Redis.
     *
     * @var array
     */
    private $hashRing = [];

    /**
     * Массив отсортированных хешей для быстрого поиска.
     *
     * @var array
     */
    private $sortedHashes = [];

    /**
     * Конструктор класса ConsistentHashingRedis.
     *
     * @param array $redisNodes Массив конфигураций Redis-нод. Каждый элемент должен быть массивом с параметрами подключения Predis.
     * @param int $virtualNodes Количество виртуальных узлов для каждого физического узла Redis.
     * @throws Exception Если ни одна нода не подключена.
     */
    public function __construct(array $redisNodes, int $virtualNodes = 100)
    {
        if (empty($redisNodes)) {
            throw new Exception("No Redis nodes provided for ConsistentHashingRedis.");
        }

        $this->virtualNodes = $virtualNodes;

        foreach ($redisNodes as $nodeConfig) {
            try {
                $client = new Client($nodeConfig);
                $this->clients[] = $client;
                $this->addNodeToHashRing($client);
            } catch (Exception $e) {
                // Логирование ошибки подключения к ноде
                error_log("Failed to connect to Redis node: " . json_encode($nodeConfig) . " Error: " . $e->getMessage());
            }
        }

        if (empty($this->clients)) {
            throw new Exception("No Redis nodes are available after attempting to connect.");
        }

        // Сортируем хеш-кольцо для быстрого поиска
        sort($this->sortedHashes, SORT_STRING);
    }

    /**
     * Добавляет физический узел Redis в хеш-кольцо с учётом виртуальных узлов.
     *
     * @param Client $client Экземпляр клиента Predis.
     * @return void
     */
    private function addNodeToHashRing(Client $client): void
    {
        for ($i = 0; $i < $this->virtualNodes; $i++) {
            // Создаём уникальный идентификатор для виртуального узла
            $virtualNodeId = $client->getConnection()->getParameters()->host . ':' . $client->getConnection()->getParameters()->port . '#VN' . $i;
            // Вычисляем хеш виртуального узла
            $hash = $this->hash($virtualNodeId);
            // Добавляем в хеш-кольцо
            $this->hashRing[$hash] = $client;
            $this->sortedHashes[] = $hash;
        }
    }

    /**
     * Вычисляет хеш для заданной строки.
     *
     * @param string $key Строка для хеширования.
     * @return string Хеш в шестнадцатеричном представлении.
     */
    private function hash(string $key): string
    {
        // Используем CRC32 для хеширования и преобразуем в беззнаковое число
        return sprintf('%08x', crc32($key));
    }

    /**
     * Находит клиента Redis для заданного ключа с использованием консистентного хеширования.
     *
     * @param string $key Ключ для поиска.
     * @return Client Экземпляр клиента Redis.
     */
    public function getClient(string $key): Client
    {
        $hash = $this->hash($key);

        // Бинарный поиск для нахождения первого хеша >= текущего хеша ключа
        $index = $this->binarySearch($hash);

        // Если индекс равен длине массива, оборачиваемся к первому узлу
        if ($index === count($this->sortedHashes)) {
            $index = 0;
        }

        $selectedHash = $this->sortedHashes[$index];
        return $this->hashRing[$selectedHash];
    }

    /**
     * Выполняет бинарный поиск для нахождения индекса первого элемента >= заданного хеша.
     *
     * @param string $hash Хеш для поиска.
     * @return int Индекс в отсортированном массиве хешей.
     */
    private function binarySearch(string $hash): int
    {
        $low = 0;
        $high = count($this->sortedHashes) - 1;

        while ($low <= $high) {
            $mid = intdiv($low + $high, 2);
            if ($this->sortedHashes[$mid] === $hash) {
                return $mid;
            } elseif ($this->sortedHashes[$mid] < $hash) {
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }

        return $low;
    }

    /**
     * Получение клиентов Redis.
     *
     * @return Client[] Массив клиентов Redis.
     */
    public function getClients(): array
    {
        return $this->clients;
    }

    /**
     * Получение значения по ключу.
     *
     * @param string $key Ключ для получения значения.
     * @return string|false Значение или false, если ключ не существует.
     */
    public function get(string $key)
    {
        $client = $this->getClient($key);
        return $client->get($key);
    }

    /**
     * Установка значения по ключу.
     *
     * @param string $key Ключ для установки значения.
     * @param string $value Значение для установки.
     * @param int|null $ttl Время жизни ключа в секундах. Если null, ключ не истекает.
     * @return bool Результат операции.
     */
    public function set(string $key, string $value, int $ttl = null): bool
    {
        $client = $this->getClient($key);
        if ($ttl !== null) {
            return $client->setex($key, $ttl, $value) === 'OK';
        }
        return $client->set($key, $value) === 'OK';
    }

    /**
     * Удаление ключа из Redis.
     *
     * @param string $key Ключ для удаления.
     * @return int Количество удалённых ключей (0 или 1).
     */
    public function delete(string $key): int
    {
        $client = $this->getClient($key);
        return $client->del([$key]);
    }

    /**
     * Получение значений по массиву ключей (mget).
     *
     * @param array $keys Массив ключей для получения.
     * @return array Массив значений.
     */
    public function mget(array $keys): array
    {
        // Группируем ключи по клиентам Redis
        $groups = [];
        foreach ($keys as $key) {
            $client = $this->getClient($key);
            $clientId = spl_object_hash($client);
            if (!isset($groups[$clientId])) {
                $groups[$clientId] = [
                    'client' => $client,
                    'keys' => []
                ];
            }
            $groups[$clientId]['keys'][] = $key;
        }

        $results = [];
        foreach ($groups as $group) {
            $client = $group['client'];
            $groupKeys = $group['keys'];
            $groupResults = $client->mget($groupKeys);
            foreach ($groupResults as $value) {
                $results[] = $value;
            }
        }

        return $results;
    }

    /**
     * Проверка наличия ключа в Redis.
     *
     * @param string $key Ключ для проверки.
     * @return bool true, если ключ существует, иначе false.
     */
    public function exists(string $key): bool
    {
        $client = $this->getClient($key);
        return $client->exists($key) > 0;
    }

    /**
     * Добавление данных в Redis Stream.
     *
     * @param string $streamName Название потока.
     * @param array $data Данные для добавления в поток.
     * @return string ID записи в потоке.
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function xAdd(string $streamName, array $data): string
    {
        echo $streamName;
        print_r($data);

        $client = $this->getClient($streamName);
        
        // Преобразуем ассоциативный массив в список аргументов: field1, value1, field2, value2, ...
        $args = [$streamName, '*'];
        foreach ($data as $field => $value) {
            $args[] = $field;
            $args[] = $value;
        }

        // Используем executeRaw для вызова XADD с корректными аргументами
        $streamId = $client->executeRaw(array_merge(['XADD'], $args));

        if (!$streamId) {
            throw new Exception("Failed to write to Redis Stream: $streamName");
        }
        return $streamId;
    }


    /**
     * Получение записей из Redis Stream по диапазону ID.
     *
     * @param string $streamName Название потока.
     * @param string $startId Начальный ID.
     * @param string $endId Конечный ID.
     * @param int $count Количество записей для получения.
     * @return array Массив записей из потока.
     */
    public function xRange(string $streamName, string $startId = '0', string $endId = '+', int $count = 100): array
    {
        $client = $this->getClient($streamName);
        return $client->xrange($streamName, $startId, $endId, $count);
    }

    /**
     * Получение записей из Redis Stream с блокировкой.
     *
     * @param string $streamName Название потока.
     * @param string $startId Начальный ID.
     * @param int $count Количество записей для получения.
     * @param int $block Время блокировки в миллисекундах.
     * @return array Массив записей из потока.
     */
    public function xRead(string $streamName, string $startId = '0', int $count = 100, int $block = 0): array
    {
        $client = $this->getClient($streamName);
        return $client->xread([$streamName => $startId], $count, $block);
    }

    /**
     * Увеличение значения ключа на 1.
     *
     * @param string $key Ключ для увеличения.
     * @return int Новое значение ключа.
     */
    public function incr(string $key): int
    {
        $client = $this->getClient($key);
        return $client->incr($key);
    }

    /**
     * Выполнение операций в рамках Pipeline.
     *
     * @param callable $callback Функция, принимающая Pipeline и Client.
     * @return array Массив результатов операций в Pipeline.
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function pipeline(callable $callback): array
    {
        $results = [];
        foreach ($this->clients as $client) {
            $clientResults = $client->pipeline(function($pipe) use ($callback, $client) {
                $callback($pipe, $client);
            });
            $results[] = $clientResults;
        }
        return $results;
    }
    /**
     * Получение диапазона элементов из списка Redis.
     *
     * @param string $key Ключ списка.
     * @param int $start Начальный индекс.
     * @param int $end Конечный индекс.
     * @return array Массив элементов из списка.
     */
    public function lrange(string $key, int $start, int $end): array
    {
        $client = $this->getClient($key);
        return $client->lrange($key, $start, $end);
    }

    /**
     * Удаление элементов из списка Redis.
     *
     * @param string $key Ключ списка.
     * @param int $count Количество элементов для удаления:
     *     - > 0: удалить $count элементов с начала списка.
     *     - < 0: удалить $count элементов с конца списка.
     *     - 0: удалить все элементы, равные $value.
     * @param string $value Значение, которое нужно удалить.
     * @return int Количество удалённых элементов.
     */
    public function lRem(string $key, int $count, string $value): int
    {
        $client = $this->getClient($key);
        return $client->lrem($key, $count, $value);
    }
    
    /**
     * Добавление элементов в конец списка Redis.
     *
     * @param string $key Ключ списка.
     * @param string|array $values Значение или массив значений для добавления.
     * @return int Длина списка после добавления.
     */
    public function rPush(string $key, $values): int
    {
        $client = $this->getClient($key);

        // Приводим одиночное значение к массиву, если оно не является массивом
        if (!is_array($values)) {
            $values = [$values];
        }

        // Добавляем элементы в конец списка
        return $client->rpush($key, $values);
    }

    /**
     * Выполнение транзакции Redis (Multi/Exec) и получение результатов.
     *
     * @return array Массив результатов операций в транзакции.
     * @throws Exception Если транзакция завершилась неудачно.
     */
    public function exec(): array
    {
        // Начинаем транзакцию
        $pipeline = $this->getClient('')->multi();

        try {
            // Выполняем команды в транзакции
            $results = $pipeline->exec();

            if ($results === false) {
                throw new Exception("Redis transaction failed.");
            }

            return $results;
        } catch (Exception $e) {
            // Отменяем транзакцию в случае ошибки
            $pipeline->discard();
            throw $e;
        }
    }
    /**
     * Удаление ключа из Redis.
     *
     * @param string $key Ключ для удаления.
     * @return int Количество удалённых ключей (0 или 1).
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function del(string $key): int
    {
        $client = $this->getClient($key);

        try {
            return $client->del($key);
        } catch (Exception $e) {
            throw new Exception("Failed to delete key '{$key}': " . $e->getMessage());
        }
    }

    /**
     * Получение длины списка Redis.
     *
     * @param string $key Ключ списка.
     * @return int Длина списка.
     */
    public function lLen(string $key): int
    {
        $client = $this->getClient($key);
        return $client->llen($key);
    }

    /**
     * Запуск транзакции Redis (Multi/Exec).
     *
     * @param callable $callback Функция, принимающая Pipeline и Client.
     * @return array Массив результатов транзакции.
     */
    public function multi(callable $callback): array
    {
        $results = [];
        foreach ($this->clients as $client) {
            $clientResults = $client->multi(function($pipe) use ($callback, $client) {
                $callback($pipe, $client);
            });
            $results[] = $clientResults;
        }
        return $results;
    }
}
