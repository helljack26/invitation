<?php

namespace Model;

use PDO;
use PDOException;
use Exception;
use Service\ConsistentHashingRedis;
use Service\CacheService;
use Service\FileService\ImageService;
use Service\FileService\FileServerManager;
use Service\FileService\EncryptionService;

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;
    protected $cacheService;
    protected $consistentHashingRedis;
    protected $fileServerManager;
    protected $imageService;
    protected $EncryptionService;
    
    public function __construct()
    {
        // Подключение к MySQL
        $this->host = getenv('DB_HOST');
        $this->db_name = getenv('DB_DATABASE');
        $this->username = getenv('DB_USERNAME');
        $this->password = getenv('DB_PASSWORD');
        $this->getConnection();

        // Настройки для Redis-нод
        $redisServers = [
            ['host' => getenv('REDIS_NODE1_HOST') ?: 'redis-node1', 'port' => 6379],
            ['host' => getenv('REDIS_NODE2_HOST') ?: 'redis-node2', 'port' => 6379],
            ['host' => getenv('REDIS_NODE3_HOST') ?: 'redis-node3', 'port' => 6379],
        ];

        // Инициализируем ConsistentHashingRedis
        $this->consistentHashingRedis = new ConsistentHashingRedis($redisServers);

        // Инициализируем CacheService
        $this->cacheService = new CacheService($this->consistentHashingRedis);

        // Инициализируем FileServerManager
        $this->fileServerManager = new FileServerManager($this->conn, $this->EncryptionService);

        // Инициализируем ImageService с FileServerManager
        $this->imageService = new ImageService($this->conn, $this->fileServerManager, $this->cacheService);

        if (!$this->imageService) {
            throw new Exception("ImageService could not be initialized.");
        }
    }

    /**
     * Получение подключения к базе данных.
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        $this->conn = null;
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Дополнительные настройки PDO, если необходимо
        } catch (PDOException $exception) {
            // Логирование ошибки подключения
            error_log("Connection error: " . $exception->getMessage());
            throw new Exception("Не удалось подключиться к базе данных.");
        }
        return $this->conn;
    }

    /**
     * Получение экземпляра CacheService.
     *
     * @return CacheService
     */
    public function getCacheService(): CacheService
    {
        return $this->cacheService;
    }

    /**
     * Получение экземпляра ImageService.
     *
     * @return ImageService
     * @throws Exception Если ImageService не инициализирован.
     */
    public function getImageService(): ImageService
    {
        if (!$this->imageService) {
            throw new Exception("ImageService instance is not initialized.");
        }
        return $this->imageService;
    }

    /**
     * Получение экземпляра FileServerManager.
     *
     * @return FileServerManager
     * @throws Exception Если FileServerManager не инициализирован.
     */
    public function getFileServerManager(): FileServerManager
    {
        if (!$this->fileServerManager) {
            throw new Exception("FileServerManager instance is not initialized.");
        }
        return $this->fileServerManager;
    }

    /**
     * Выполнение подготовленного запроса.
     *
     * @param string $query
     * @param array $params
     * @return PDOStatement
     */
    protected function executeQuery(string $query, array $params = [])
    {
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            // PDO параметры начинаются с двоеточия
            $param = strpos($key, ':') === 0 ? $key : ':' . $key;
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        return $stmt;
    }
}
