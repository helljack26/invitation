<?php
namespace Service;

use Exception;
use Predis\Client;

/**
 * Класс CacheService отвечает за взаимодействие с Redis через ConsistentHashingRedis.
 */
class CacheService
{
    /**
     * Экземпляр ConsistentHashingRedis.
     *
     * @var ConsistentHashingRedis
     */
    private $redis;

    /**
     * Конструктор класса CacheService.
     *
     * @param ConsistentHashingRedis $redis Экземпляр ConsistentHashingRedis.
     * @throws Exception Если экземпляр ConsistentHashingRedis не предоставлен.
     */
    public function __construct(ConsistentHashingRedis $redis)
    {
        if (!$redis) {
            throw new Exception('ConsistentHashingRedis instance is required for CacheService.');
        }
        $this->redis = $redis;
    }

    /**
     * Получение клиента Redis для заданного ключа.
     *
     * @param string $key Ключ для получения клиента.
     * @return Client Экземпляр клиента Redis.
     */
    public function getClient(string $key): Client
    {
        return $this->redis->getClient($key);
    }

    /**
     * Получение значения по ключу.
     *
     * @param string $key Ключ для получения значения.
     * @return string|null Значение или null, если ключ не существует.
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function get(string $key): ?string
    {
        $result = $this->redis->get($key);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * Установка значения по ключу.
     *
     * @param string $key Ключ для установки значения.
     * @param string $value Значение для установки.
     * @param int|null $ttl Время жизни ключа в секундах. Если null, ключ не истекает.
     * @return bool Результат операции.
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function set(string $key, string $value, int $ttl = null): bool
    {
        if ($ttl !== null) {
            return $this->redis->set($key, $value, $ttl);
        }
        return $this->redis->set($key, $value);
    }

    /**
     * Удаление ключа из кэша.
     *
     * @param string $key Ключ для удаления.
     * @return int Количество удалённых ключей.
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function del(string $key): int
    {
        return $this->redis->del($key);
    }

    /**
     * Получение значений по массиву ключей (mget).
     *
     * @param array $keys Массив ключей для получения.
     * @return array|false Массив значений или false в случае ошибки.
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function mget(array $keys)
    {
        return $this->redis->mget($keys);
    }

    /**
     * Проверка наличия ключа.
     *
     * @param string $key Ключ для проверки.
     * @return bool true, если ключ существует, иначе false.
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function exists(string $key): bool
    {
        return $this->redis->exists($key);
    }

    /**
     * Добавление данных в Redis Stream.
     *
     * @param string $streamName Название потока.
     * @param array $data Данные для добавления в поток.
     * @return string ID записи в потоке.
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function addToStream(string $streamName, array $data): string
    {
        return $this->redis->xAdd($streamName, $data);
    }

    /**
     * Получение списка элементов из Redis List.
     *
     * @param string $key Ключ списка.
     * @param int $start Начальная позиция.
     * @param int $stop Конечная позиция.
     * @return array Массив элементов списка.
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function lRange(string $key, int $start, int $stop): array
    {
        return $this->redis->lRange($key, $start, $stop);
    }

    /**
     * Получение длины Redis List.
     *
     * @param string $key Ключ списка.
     * @return int Длина списка.
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function lLen(string $key): int
    {
        return $this->redis->lLen($key);
    }

    /**
     * Добавление элемента в конец Redis List.
     *
     * @param string $key Ключ списка.
     * @param mixed ...$values Значения для добавления.
     * @return int Новая длина списка.
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function rPush(string $key, ...$values): int
    {
        return $this->redis->rPush($key, ...$values);
    }

    /**
     * Удаление элементов из Redis List.
     *
     * @param string $key Ключ списка.
     * @param mixed $value Значение для удаления.
     * @param int $count Количество удалений.
     * @return int Количество удалённых элементов.
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function lRem(string $key, $value, int $count = 0): int
    {
        return $this->redis->lRem($key, $value, $count);
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
        return $this->redis->pipeline($callback);
    }

    /**
     * Выполнение Pipeline и получение результатов.
     *
     * @return array Массив результатов операций в Pipeline.
     */
    public function exec(): array
    {
        return $this->redis->exec();
    }

    /**
     * Увеличение значения ключа на 1.
     *
     * @param string $key Ключ для увеличения.
     * @return int Новое значение ключа.
     */
    public function incr(string $key): int
    {
        return $this->redis->incr($key);
    }

    /**
     * Запуск транзакции Redis.
     *
     * @param callable $callback Функция, принимающая Pipeline и Client.
     * @return array Массив результатов транзакции.
     */
    public function multi(callable $callback): array
    {
        return $this->redis->multi($callback);
    }
        /**
     * Получение значения по JSON-пути.
     *
     * @param string $key Ключ JSON объекта.
     * @param string $path Путь к необходимому полю (например, '.characteristics').
     * @return string|null Значение по указанному пути или null, если не найдено.
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function jsonGet(string $key, string $path = '.'): ?string
    {
        $client = $this->getClient($key);
        try {
            $result = $client->executeRaw(['JSON.GET', $key, $path]);
            return $result !== false ? $result : null;
        } catch (Exception $e) {
            throw new Exception("Ошибка при выполнении JSON.GET для ключа '{$key}': " . $e->getMessage());
        }
    }

    /**
     * Установка значения по JSON-пути.
     *
     * @param string $key Ключ JSON объекта.
     * @param string $path Путь к полю для установки значения (например, '.characteristics').
     * @param mixed $value Значение для установки.
     * @return bool true, если операция успешна, иначе false.
     * @throws Exception Если операция Redis завершается неудачно.
     */
    public function jsonSet(string $key, string $path, $value): bool
    {
        $client = $this->getClient($key);
        try {
            $valueJson = json_encode($value);
            $result = $client->executeRaw(['JSON.SET', $key, $path, $valueJson]);
            return $result === 'OK';
        } catch (Exception $e) {
            throw new Exception("Ошибка при выполнении JSON.SET для ключа '{$key}': " . $e->getMessage());
        }
    }
}
