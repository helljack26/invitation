<?php

namespace Model;

use PDO;
use Service\CacheService;
use Exception;

/**
 * Класс WarehouseModel отвечает за управление категориями и их характеристиками с использованием кеширования Redis.
 */
class WarehouseModel
{
    protected PDO $conn;
    protected CacheService $cacheService;

    public function __construct(PDO $conn, CacheService $cacheService)
    {
        $this->conn = $conn;
        $this->cacheService = $cacheService;

        if (!$cacheService) {
            throw new Exception("CacheService instance is not provided in WarehouseModel.");
        }
    }

    /**
     * Получение списка складов компании.
     */
    public function getWarehousesByCompany(int $companyId): array
    {
        $cacheKey = "company:{$companyId}:warehouses";

        try {
            // Попытка получить список складов из Redis
            $warehouseIds = $this->cacheService->lRange($cacheKey, 0, -1);

            if (empty($warehouseIds)) {
                // Если данных нет в Redis, загружаем их из MySQL
                $warehouses = $this->loadWarehousesToCache($companyId);
                return $warehouses;
            }

            $warehouses = [];
            foreach ($warehouseIds as $id) {
                $warehouseCacheKey = "warehouse:id:{$id}";

                // Попытка получить данные склада из Redis
                $data = $this->cacheService->jsonGet($warehouseCacheKey, '.');

                if ($data) {
                    $decoded = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $warehouses[] = $decoded;
                    } else {
                        // Логирование ошибки декодирования JSON
                    }
                } else {
                    // Если данные отсутствуют в Redis, восстанавливаем из MySQL
                    $query = "SELECT * FROM Warehouses WHERE id = :warehouse_id AND company_id = :company_id";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(':warehouse_id', $id, PDO::PARAM_INT);
                    $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
                    $stmt->execute();

                    $warehouse = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($warehouse) {
                        // Сохраняем восстановленные данные в Redis
                        $this->cacheService->jsonSet($warehouseCacheKey, '.', $warehouse);

                        $warehouses[] = $warehouse;
                    } else {
                        // Логирование отсутствия склада
                    }
                }
            }

            return $warehouses;
        } catch (\Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            throw new \Exception("Не удалось получить склады: " . $e->getMessage());
        }
    }

    /**
     * Получение склада по ID.
     */
    public function getWarehouseById(int $companyId, int $warehouseId): ?array
    {
        $cacheKey = "warehouse:id:{$warehouseId}";

        // Попытка получить данные из Redis
        $cachedWarehouse = $this->cacheService->jsonGet($cacheKey, '.');
        if ($cachedWarehouse) {
            return json_decode($cachedWarehouse, true);
        }

        // Если данных нет в Redis, обращаемся к MySQL
        $query = "SELECT * FROM Warehouses WHERE id = :warehouse_id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':warehouse_id', $warehouseId, PDO::PARAM_INT);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        $warehouse = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($warehouse) {
            // Сохранение в Redis
            $this->cacheService->jsonSet($cacheKey, '.', $warehouse);

            return $warehouse;
        }

        return null;
    }


    /**
     * Загружает все склады компании из MySQL и сохраняет их в Redis.
     *
     * @param int $companyId
     * @return array Загруженные склады
     * @throws \Exception При ошибках сохранения в Redis или MySQL
     */
    private function loadWarehousesToCache(int $companyId): array
    {
        $cacheKey = "company:{$companyId}:warehouses";

        // Запрос к MySQL для получения всех складов компании
        $query = "SELECT * FROM Warehouses WHERE company_id = :company_id ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        $warehouses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$warehouses) {
            // Если в базе данных нет складов, возвращаем пустой массив
            return [];
        }

        // Начало транзакции MySQL для обеспечения консистентности данных
        $this->conn->beginTransaction();

        try {
            foreach ($warehouses as $warehouse) {
                $warehouseId = $warehouse['id'];

                // Добавление ID склада в список складов компании в Redis
                $pushResult = $this->cacheService->rPush($cacheKey, (string)$warehouseId);
                if ($pushResult === false) {
                    throw new \Exception("Не удалось добавить склад с ID {$warehouseId} в список складов компании в Redis.");
                }

                // Формирование данных склада
                $warehouseData = [
                    'id' => $warehouseId,
                    'company_id' => $companyId,
                    'name' => $warehouse['name'],
                    'main_production' => $warehouse['main_production'],
                    'country' => $warehouse['country'],
                    'postal_address' => $warehouse['postal_address'],
                    'photo' => $warehouse['photo'],
                    'number' => $warehouse['number'],
                    'comment' => $warehouse['comment'],
                    'manager' => $warehouse['manager'],
                    'production' => $warehouse['production'],
                    'inactive' => $warehouse['inactive'],
                    'email' => $warehouse['email'],
                    'phone' => $warehouse['phone'],
                    'created_at' => $warehouse['created_at'],
                    'updated_at' => $warehouse['updated_at']
                ];

                // Сохранение данных склада в RedisJSON
                $warehouseCacheKey = "warehouse:id:{$warehouseId}";
                $jsonSetResult = $this->cacheService->jsonSet($warehouseCacheKey, '.', $warehouseData);
                if (!$jsonSetResult) {
                    throw new \Exception("Не удалось сохранить склад с ID {$warehouseId} в Redis по ключу {$warehouseCacheKey}.");
                }
            }

            // Фиксация транзакции MySQL после успешного сохранения всех складов в Redis
            $this->conn->commit();

            // Возвращаем список ID складов
            return array_column($warehouses, 'id');
        } catch (\Exception $e) {
            // Откат транзакции MySQL в случае ошибки
            $this->conn->rollBack();

            // Логирование ошибки
            error_log("Ошибка при загрузке складов в кеш Redis: " . $e->getMessage());

            throw $e; // Перебрасываем исключение дальше
        }
    }
}
