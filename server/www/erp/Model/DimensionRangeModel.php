<?php

namespace Model;

use PDO;
use Service\CacheService;
use Exception;

/**
 * Класс DimensionRangeModel отвечает за управление размерными рядами и их значениями.
 */
class DimensionRangeModel
{
    protected PDO $conn;
    protected CacheService $cacheService;

    public function __construct(PDO $conn, CacheService $cacheService)
    {
        $this->conn = $conn;
        $this->cacheService = $cacheService;

        if (!$cacheService) {
            throw new Exception("CacheService instance is not provided in DimensionRangeModel.");
        }
    }

    /**
     * Создание нового размерного ряда.
     *
     * @param int $categoryCharacteristicId
     * @param string $name
     * @param string $description
     * @return array
     * @throws Exception
     */
    public function createDimensionRange(int $categoryCharacteristicId, string $name, string $description = '', array $options): array
    {

        var_dump($options);
        if (empty($name)) {
            throw new \InvalidArgumentException("Название размерного ряда обязательно.");
        }

        $createdAt = date('Y-m-d H:i:s');

        $optionsJson = !empty($options) ? json_encode($options, JSON_UNESCAPED_UNICODE) : [];


        $query = "INSERT INTO DimensionRanges (name, category_characteristic_id, description, options, created_at, updated_at)
                  VALUES (:name, :category_characteristic_id, :description, :options, :created_at, :updated_at)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':category_characteristic_id', $categoryCharacteristicId, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':options', $optionsJson, PDO::PARAM_STR);

        $stmt->bindParam(':created_at', $createdAt, PDO::PARAM_STR);
        $stmt->bindParam(':updated_at', $createdAt, PDO::PARAM_STR);

        try {
            // Начало транзакции
            $this->conn->beginTransaction();

            if (!$stmt->execute()) {
                throw new \Exception("Не удалось выполнить запрос к базе данных.");
            }

            $dimensionRangeId = (int)$this->conn->lastInsertId();
            $dimensionRangeData = [
                'id' => $dimensionRangeId,
                'name' => $name,
                'category_characteristic_id' => $categoryCharacteristicId,
                'description' => $description,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'options' => $options
            ];

            // Сохранение в Redis
            $cacheKey = "dimension_range:id:{$dimensionRangeId}";
            $result = $this->cacheService->jsonSet($cacheKey, '.', $dimensionRangeData);
            if (!$result) {
                throw new \Exception("Не удалось сохранить размерный ряд в Redis.");
            }

            // Добавление ID размерного ряда в список размерных рядов характеристики
            $characteristicDimensionRangesKey = "category_characteristic:{$categoryCharacteristicId}:DimensionRanges";
            $result = $this->cacheService->rPush($characteristicDimensionRangesKey, (string)$dimensionRangeId);
            if ($result === false) {
                throw new \Exception("Не удалось добавить размерный ряд в список характеристики в Redis.");
            }

            // Добавление события в стрим для асинхронной обработки
            $this->cacheService->addToStream('dimension_range_creation_stream', $dimensionRangeData);

            // Фиксация транзакции
            $this->conn->commit();

            return $dimensionRangeData;
        } catch (\Exception $e) {
            // Откат транзакции
            $this->conn->rollBack();

            // Логирование ошибки
            error_log("Ошибка при создании размерного ряда: " . $e->getMessage());

            throw new \Exception("Не удалось создать размерный ряд: " . $e->getMessage());
        }
    }

    /**
     * Добавление значения в размерный ряд.
     *
     * @param int $dimensionRangeId
     * @param string $value
     * @param int $sortOrder
     * @return array
     * @throws Exception
     */
    public function addDimensionRangeValue(int $dimensionRangeId, string $value, int $sortOrder = 0): array
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("Значение размерного ряда обязательно.");
        }

        $createdAt = date('Y-m-d H:i:s');

        $query = "INSERT INTO DimensionRangeValues (dimension_range_id, value, sort_order, created_at, updated_at)
                  VALUES (:dimension_range_id, :value, :sort_order, :created_at, :updated_at)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dimension_range_id', $dimensionRangeId, PDO::PARAM_INT);
        $stmt->bindParam(':value', $value, PDO::PARAM_STR);
        $stmt->bindParam(':sort_order', $sortOrder, PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $createdAt, PDO::PARAM_STR);
        $stmt->bindParam(':updated_at', $createdAt, PDO::PARAM_STR);

        try {
            // Начало транзакции
            $this->conn->beginTransaction();

            if (!$stmt->execute()) {
                throw new \Exception("Не удалось добавить значение размерного ряда.");
            }

            $valueId = (int)$this->conn->lastInsertId();
            $dimensionRangeValueData = [
                'id' => $valueId,
                'dimension_range_id' => $dimensionRangeId,
                'value' => $value,
                'sort_order' => $sortOrder,
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ];

            // Сохранение в Redis
            $cacheKey = "dimension_range_value:id:{$valueId}";
            $result = $this->cacheService->jsonSet($cacheKey, '.', $dimensionRangeValueData);
            if (!$result) {
                throw new \Exception("Не удалось сохранить значение размерного ряда в Redis.");
            }

            // Добавление ID значения в список размерного ряда
            $dimensionRangeValuesKey = "dimension_range:{$dimensionRangeId}:values";
            $result = $this->cacheService->rPush($dimensionRangeValuesKey, (string)$valueId);
            if ($result === false) {
                throw new \Exception("Не удалось добавить значение размерного ряда в Redis.");
            }

            // Добавление события в стрим для асинхронной обработки
            $this->cacheService->addToStream('dimension_range_value_creation_stream', $dimensionRangeValueData);

            // Фиксация транзакции
            $this->conn->commit();

            return $dimensionRangeValueData;
        } catch (\Exception $e) {
            // Откат транзакции
            $this->conn->rollBack();

            // Логирование ошибки
            error_log("Ошибка при добавлении значения размерного ряда: " . $e->getMessage());

            throw new \Exception("Не удалось добавить значение размерного ряда: " . $e->getMessage());
        }
    }

    /**
     * Получение размерных рядов для характеристики.
     *
     * @param int $categoryCharacteristicId
     * @return array
     */
    /**
     * Получение размерных рядов для характеристики.
     *
     * @param int $categoryCharacteristicId
     * @return array
     */
    public function getDimensionRangesByCharacteristic(int $categoryCharacteristicId): array
    {
        $cacheKey = "category_characteristic:{$categoryCharacteristicId}:dimension_ranges";

        // Попытка получить список размерных рядов из Redis
        $dimensionRangeIds = $this->cacheService->lRange($cacheKey, 0, -1);

        if (empty($dimensionRangeIds)) {
            // Если данных нет в Redis, загружаем из MySQL и кешируем
            $query = "SELECT id, name, category_characteristic_id, description, created_at, updated_at, options FROM DimensionRanges WHERE category_characteristic_id = :category_characteristic_id ORDER BY id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':category_characteristic_id', $categoryCharacteristicId, PDO::PARAM_INT);
            $stmt->execute();

            $dimensionRanges = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];

            foreach ($dimensionRanges as $range) {
                $this->cacheService->rPush($cacheKey, (string)$range['id']);
                // $values = $this->getDimensionRangeValues($range['id']);

                $range['values'] = json_decode($range['options'], true); // Присваиваем значения

                $rangeCacheKey = "dimension_range:id:{$range['id']}";
                $this->cacheService->jsonSet($rangeCacheKey, '.', $range, ['EX' => 3600]); // Установка времени жизни кеша

                $result[] = $range; // Добавляем в результирующий массив
            }

            return $result;
        }

        // Получаем данные размерных рядов из Redis
        $dimensionRanges = [];
        foreach ($dimensionRangeIds as $id) {
            $cacheKeyIndividual = "dimension_range:id:{$id}";
            $cachedData = $this->cacheService->jsonGet($cacheKeyIndividual, '.');
            if ($cachedData) {
                $decoded = json_decode($cachedData, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $dimensionRanges[] = $decoded;
                }
            }
        }

        return $dimensionRanges;
    }



    /**
     * Получение значений размерного ряда.
     *
     * @param int $dimensionRangeId
     * @return array
     */
    public function getDimensionRangeValues(int $dimensionRangeId): array
    {
        $cacheKey = "dimension_range:{$dimensionRangeId}:values";

        // Попытка получить список значений из Redis
        $valueIds = $this->cacheService->lRange($cacheKey, 0, -1);
        error_log("Полученные valueIds для dimensionRangeId {$dimensionRangeId}: " . json_encode($valueIds));

        if (empty($valueIds)) {
            // Если данных нет в Redis, загружаем из MySQL и кешируем
            $query = "SELECT id, value, sort_order, created_at, updated_at FROM DimensionRangeValues WHERE dimension_range_id = :dimension_range_id ORDER BY sort_order ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':dimension_range_id', $dimensionRangeId, PDO::PARAM_INT);
            $stmt->execute();

            $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Полученные значения из базы для dimensionRangeId {$dimensionRangeId}: " . json_encode($values));

            foreach ($values as $value) {
                $this->cacheService->rPush($cacheKey, (string)$value['id']);
                $valueCacheKey = "dimension_range_value:id:{$value['id']}";
                $valueData = [
                    'id' => $value['id'],
                    'dimension_range_id' => $dimensionRangeId,
                    'value' => $value['value'],
                    'sort_order' => $value['sort_order'],
                    'created_at' => $value['created_at'],
                    'updated_at' => $value['updated_at']
                ];
                $this->cacheService->jsonSet($valueCacheKey, '.', $valueData, ['EX' => 3600]);
                error_log("Сохранено значение в Redis: " . json_encode($valueData));
            }

            return array_column($values, 'value');
        }

        // Получаем значения из Redis
        $values = [];
        foreach ($valueIds as $id) {
            $cacheKeyIndividual = "dimension_range_value:id:{$id}";
            $cachedData = $this->cacheService->jsonGet($cacheKeyIndividual, '.');
            error_log("Полученные cachedData для key {$cacheKeyIndividual}: " . $cachedData);

            if ($cachedData) {
                $decoded = json_decode($cachedData, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $values[] = $decoded['value'];
                    error_log("Добавлено значение: " . $decoded['value']);
                } else {
                    error_log("Ошибка декодирования JSON для ключа {$cacheKeyIndividual}: " . json_last_error_msg());
                }
            } else {
                error_log("Значение размерного ряда с ID {$id} не найдено в Redis.");
            }
        }

        return $values;
    }


    /**
     * Получение информации о размерном ряду по ID.
     *
     * @param int $dimensionRangeId
     * @return array|null
     */
    public function getDimensionRangeById(int $dimensionRangeId): ?array
    {
        $cacheKey = "dimension_range:id:{$dimensionRangeId}";

        // Попытка получить данные из Redis
        $cachedData = $this->cacheService->jsonGet($cacheKey, '.');
        if ($cachedData) {
            $decoded = json_decode($cachedData, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            } else {
                error_log("Ошибка декодирования JSON для ключа {$cacheKey}: " . json_last_error_msg());
            }
        }

        // Если данных нет в Redis, загружаем из MySQL и кешируем
        $query = "SELECT id, name, description, category_characteristic_id, created_at, updated_at FROM DimensionRanges WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $dimensionRangeId, PDO::PARAM_INT);
        $stmt->execute();

        $range = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($range) {
            $range['values'] = $this->getAllDimensionRangeValues($range['id']);

            // Кешируем в Redis
            $this->cacheService->jsonSet($cacheKey, '.', $range);

            return $range;
        }

        return null;
    }

    /**
     * Редактирование размерного ряда.
     *
     * @param int $dimensionRangeId
     * @param string|null $name
     * @param string|null $description
     * @return array
     * @throws Exception
     */
    public function editDimensionRange(int $dimensionRangeId, ?string $name = null, ?string $description = null): array
    {
        if (empty($dimensionRangeId)) {
            throw new \InvalidArgumentException("ID размерного ряда обязательно.");
        }

        // Получаем текущие данные размерного ряда
        $dimensionRange = $this->getDimensionRangeById($dimensionRangeId);
        if (!$dimensionRange) {
            throw new \Exception("Размерный ряд не найден.");
        }

        // Формируем динамический SQL-запрос
        $fields = [];
        $params = [':id' => $dimensionRangeId];

        if ($name !== null) {
            $fields[] = "name = :name";
            $params[':name'] = $name;
        }

        if ($description !== null) {
            $fields[] = "description = :description";
            $params[':description'] = $description;
        }

        if (empty($fields)) {
            throw new \Exception("Нет данных для обновления.");
        }

        $fields[] = "updated_at = :updated_at";
        $params[':updated_at'] = date('Y-m-d H:i:s');

        $query = "UPDATE DimensionRanges SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $val) {
            if (is_int($val)) {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }
        }

        if ($stmt->execute()) {
            // Обновляем данные в Redis
            $updatedRange = $this->getDimensionRangeById($dimensionRangeId);
            if (!$updatedRange) {
                throw new \Exception("Не удалось получить обновлённые данные размерного ряда.");
            }

            $cacheKey = "dimension_range:id:{$dimensionRangeId}";
            $this->cacheService->jsonSet($cacheKey, '.', $updatedRange);

            // Добавление события в стрим для асинхронной обработки
            $this->cacheService->addToStream('dimension_range_update_stream', $updatedRange);

            return $updatedRange;
        }

        throw new \Exception("Не удалось обновить размерный ряд.");
    }

    /**
     * Удаление размерного ряда.
     *
     * @param int $dimensionRangeId
     * @return array
     * @throws Exception
     */
    public function deleteDimensionRange(int $dimensionRangeId): array
    {
        if (empty($dimensionRangeId)) {
            throw new \InvalidArgumentException("ID размерного ряда обязательно.");
        }

        // Получаем текущие данные размерного ряда
        $dimensionRange = $this->getDimensionRangeById($dimensionRangeId);
        if (!$dimensionRange) {
            throw new \Exception("Размерный ряд не найден.");
        }

        $query = "DELETE FROM DimensionRanges WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $dimensionRangeId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Удаляем данные из Redis
            $cacheKey = "dimension_range:id:{$dimensionRangeId}";
            $this->cacheService->del($cacheKey);

            // Удаляем ID размерного ряда из списка размерных рядов характеристики
            $characteristicDimensionRangesKey = "category_characteristic:{$dimensionRange['category_characteristic_id']}:DimensionRanges";
            $this->cacheService->lRem($characteristicDimensionRangesKey, (string)$dimensionRangeId, 0);

            // Удаление всех значений размерного ряда из Redis
            $dimensionRangeValuesKey = "dimension_range:{$dimensionRangeId}:values";
            $valueIds = $this->cacheService->lRange($dimensionRangeValuesKey, 0, -1);
            foreach ($valueIds as $valueId) {
                $valueCacheKey = "dimension_range_value:id:{$valueId}";
                $this->cacheService->del($valueCacheKey);
            }
            $this->cacheService->del($dimensionRangeValuesKey);

            // Добавление события в стрим для асинхронной обработки
            $this->cacheService->addToStream('dimension_range_deletion_stream', [
                'dimension_range_id' => $dimensionRangeId,
                'deleted_at' => date('Y-m-d H:i:s')
            ]);

            return ["message" => "Размерный ряд удален успешно."];
        }

        throw new \Exception("Не удалось удалить размерный ряд.");
    }

    /**
     * Получение всех значений размерного ряда.
     *
     * @param int $dimensionRangeId
     * @return array
     */
    public function getAllDimensionRangeValues(int $dimensionRangeId): array
    {
        return $this->getDimensionRangeValues($dimensionRangeId);
    }

    /**
     * Получение значения размерного ряда по его ID.
     *
     * @param int $valueId
     * @return array|null
     */
    public function getDimensionRangeValueById(int $valueId): ?array
    {
        $cacheKey = "dimension_range_value:id:{$valueId}";

        // Попытка получить данные из Redis
        $cachedData = $this->cacheService->jsonGet($cacheKey, '.');
        if ($cachedData) {
            $decoded = json_decode($cachedData, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            } else {
                error_log("Ошибка декодирования JSON для ключа {$cacheKey}: " . json_last_error_msg());
            }
        }

        // Если данных нет в Redis, загружаем из MySQL и кешируем
        $query = "SELECT id, dimension_range_id, value, sort_order, created_at, updated_at FROM DimensionRangeValues WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $valueId, PDO::PARAM_INT);
        $stmt->execute();

        $value = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($value) {
            // Кешируем в Redis
            $this->cacheService->jsonSet($cacheKey, '.', $value);
            return $value;
        }

        return null;
    }

    /**
     * Обновление значения размерного ряда.
     *
     * @param int $valueId
     * @param string $value
     * @param int $sortOrder
     * @return array
     * @throws Exception
     */
    public function updateDimensionRangeValue(int $valueId, string $value, int $sortOrder = 0): array
    {
        if (empty($valueId)) {
            throw new \InvalidArgumentException("ID значения размерного ряда обязательно.");
        }

        // Получаем существующее значение
        $existingValue = $this->getDimensionRangeValueById($valueId);
        if (!$existingValue) {
            throw new \Exception("Значение размерного ряда не найдено.");
        }

        $dimensionRangeId = (int)$existingValue['dimension_range_id'];
        $updatedAt = date('Y-m-d H:i:s');

        $query = "UPDATE DimensionRangeValues 
                  SET value = :value, sort_order = :sort_order, updated_at = :updated_at
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':value', $value, PDO::PARAM_STR);
        $stmt->bindParam(':sort_order', $sortOrder, PDO::PARAM_INT);
        $stmt->bindParam(':updated_at', $updatedAt, PDO::PARAM_STR);
        $stmt->bindParam(':id', $valueId, PDO::PARAM_INT);

        try {
            // Начало транзакции
            $this->conn->beginTransaction();

            if (!$stmt->execute()) {
                throw new \Exception("Не удалось обновить значение размерного ряда.");
            }

            $updatedValue = $this->getDimensionRangeValueById($valueId);
            if (!$updatedValue) {
                throw new \Exception("Не удалось получить обновлённые данные значения размерного ряда.");
            }

            // Обновляем данные в Redis
            $cacheKey = "dimension_range_value:id:{$valueId}";
            $this->cacheService->jsonSet($cacheKey, '.', $updatedValue);

            // Добавление события в стрим для асинхронной обработки
            $this->cacheService->addToStream('dimension_range_value_update_stream', $updatedValue);

            // Фиксация транзакции
            $this->conn->commit();

            return $updatedValue;
        } catch (\Exception $e) {
            // Откат транзакции
            $this->conn->rollBack();

            // Логирование ошибки
            error_log("Ошибка при обновлении значения размерного ряда: " . $e->getMessage());

            throw new \Exception("Не удалось обновить значение размерного ряда: " . $e->getMessage());
        }
    }

    /**
     * Удаление значения размерного ряда.
     *
     * @param int $valueId
     * @return array
     * @throws Exception
     */
    public function deleteDimensionRangeValue(int $valueId): array
    {
        if (empty($valueId)) {
            throw new \InvalidArgumentException("ID значения размерного ряда обязательно.");
        }

        // Получаем существующее значение
        $existingValue = $this->getDimensionRangeValueById($valueId);
        if (!$existingValue) {
            throw new \Exception("Значение размерного ряда не найдено.");
        }

        $dimensionRangeId = (int)$existingValue['dimension_range_id'];

        $query = "DELETE FROM DimensionRangeValues WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $valueId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Удаляем данные из Redis
            $cacheKey = "dimension_range_value:id:{$valueId}";
            $this->cacheService->del($cacheKey);

            // Удаляем ID значения из списка размерного ряда
            $dimensionRangeValuesKey = "dimension_range:{$dimensionRangeId}:values";
            $this->cacheService->lRem($dimensionRangeValuesKey, (string)$valueId, 0);

            // Добавление события в стрим для асинхронной обработки
            $this->cacheService->addToStream('dimension_range_value_deletion_stream', [
                'dimension_range_id' => $dimensionRangeId,
                'value_id' => $valueId,
                'deleted_at' => date('Y-m-d H:i:s')
            ]);

            return ["message" => "Значение размерного ряда удалено успешно."];
        }

        throw new \Exception("Не удалось удалить значение размерного ряда.");
    }
}
