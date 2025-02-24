<?php

namespace Model;

use PDO;
use Service\CacheService;
use Exception;

/**
 * Класс CategoryModel отвечает за управление категориями и их характеристиками с использованием кеширования Redis.
 */
class CategoryModel
{
    protected PDO $conn;
    protected CacheService $cacheService;

    public function __construct(PDO $conn, CacheService $cacheService)
    {
        $this->conn = $conn;
        $this->cacheService = $cacheService;

        if (!$cacheService) {
            throw new Exception("CacheService instance is not provided in CategoryModel.");
        }
    }

    /**
     * Создание новой категории.
     */
    public function createCategory(int $companyId, array $data): array
    {
        // Валидация данных
        if (empty($data['name'])) {
            throw new \InvalidArgumentException("Название категории обязательно.");
        }

        $createdAt = date('Y-m-d H:i:s');
        $updatedAt = $createdAt;

        // SQL-запрос для вставки в базу данных
        $query = "INSERT INTO Categories (
    company_id, name, description, includedInName, askQuantityOnSale,
    markupFromPrice, maxDiscountSet, extraChargeSet, priceRounding, roundingMinusOne,
    discountPercent, markupWholesale, markupPercent, fiscal, excise, vatApplicable,
    vatRateCode, benefitCode, vatExemptionReason, minOrderQuantity, barcodeContainsQuantity,
    barcodeQuantityCoefficient, articleCode, created_at, updated_at
) VALUES (
    :company_id, :name, :description, :includedInName, :askQuantityOnSale,
    :markupFromPrice, :maxDiscountSet, :extraChargeSet, :priceRounding, :roundingMinusOne,
    :discountPercent, :markupWholesale, :markupPercent, :fiscal, :excise, :vatApplicable,
    :vatRateCode, :benefitCode, :vatExemptionReason, :minOrderQuantity, :barcodeContainsQuantity,
    :barcodeQuantityCoefficient, :articleCode, :created_at, :updated_at
)";

        // Подготовка запроса
        $stmt = $this->conn->prepare($query);

        // Привязка параметров для типа INT
        $includedInName = intval($data['includedInName'] ?? false);
        $discountPercent = intval($data['discountPercent'] ?? 0);
        $markupPercent = intval($data['markupPercent'] ?? 0);
        $askQuantityOnSale = intval($data['askQuantityOnSale'] ?? false);
        $markupFromPrice = intval($data['markupFromPrice'] ?? false);
        $markupWholesale = intval($data['markupWholesale'] ?? false);
        $maxDiscountSet = intval($data['maxDiscountSet'] ?? false);
        $extraChargeSet = intval($data['extraChargeSet'] ?? false);
        $roundingMinusOne = intval($data['roundingMinusOne'] ?? false);
        $fiscal = intval($data['fiscal'] ?? false);
        $excise = intval($data['excise'] ?? false);
        $vatApplicable = intval($data['vatApplicable'] ?? false);
        $barcodeContainsQuantity = intval($data['barcodeContainsQuantity'] ?? false);

        $name = $data['name'];
        $description = $data['description'] ?? '';
        $priceRounding = $data['priceRounding'] ?? '';
        $vatRateCode = $data['vatRateCode'] ?? '';
        $benefitCode = $data['benefitCode'] ?? '';
        $vatExemptionReason = $data['vatExemptionReason'] ?? '';
        $minOrderQuantity = intval($data['minOrderQuantity'] ?? 0);
        $barcodeQuantityCoefficient = intval($data['barcodeQuantityCoefficient'] ?? 0);
        $articleCode = $data['articleCode'] ?? '';

        // Привязка параметров для типа INT
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->bindParam(':discountPercent', $discountPercent, PDO::PARAM_INT);
        $stmt->bindParam(':markupPercent', $markupPercent, PDO::PARAM_INT);
        $stmt->bindParam(':markupWholesale', $markupWholesale, PDO::PARAM_INT);
        $stmt->bindParam(':includedInName', $includedInName, PDO::PARAM_INT);
        $stmt->bindParam(':askQuantityOnSale', $askQuantityOnSale, PDO::PARAM_INT);
        $stmt->bindParam(':markupFromPrice', $markupFromPrice, PDO::PARAM_INT);
        $stmt->bindParam(':maxDiscountSet', $maxDiscountSet, PDO::PARAM_INT);
        $stmt->bindParam(':extraChargeSet', $extraChargeSet, PDO::PARAM_INT);
        $stmt->bindParam(':roundingMinusOne', $roundingMinusOne, PDO::PARAM_INT);
        $stmt->bindParam(':fiscal', $fiscal, PDO::PARAM_INT);
        $stmt->bindParam(':excise', $excise, PDO::PARAM_INT);
        $stmt->bindParam(':vatApplicable', $vatApplicable, PDO::PARAM_INT);
        $stmt->bindParam(':barcodeContainsQuantity', $barcodeContainsQuantity, PDO::PARAM_INT);

        // Привязка параметров для строковых значений
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':priceRounding', $priceRounding, PDO::PARAM_STR);
        $stmt->bindParam(':vatRateCode', $vatRateCode, PDO::PARAM_STR);
        $stmt->bindParam(':benefitCode', $benefitCode, PDO::PARAM_STR);
        $stmt->bindParam(':vatExemptionReason', $vatExemptionReason, PDO::PARAM_STR);
        $stmt->bindParam(':minOrderQuantity', $minOrderQuantity, PDO::PARAM_INT);
        $stmt->bindParam(':barcodeQuantityCoefficient', $barcodeQuantityCoefficient, PDO::PARAM_INT);
        $stmt->bindParam(':articleCode', $articleCode, PDO::PARAM_STR);

        // Привязка даты
        $createdAt = date('Y-m-d H:i:s');
        $stmt->bindParam(':created_at', $createdAt, PDO::PARAM_STR);
        $stmt->bindParam(':updated_at', $createdAt, PDO::PARAM_STR);


        try {
            // Начало транзакции MySQL
            $this->conn->beginTransaction();

            if (!$stmt->execute()) {
                throw new \Exception("Не удалось выполнить запрос к базе данных.");
            }

            // Получаем ID новой категории
            $categoryId = (int)$this->conn->lastInsertId();
            $data['id'] = $categoryId;
            $data['created_at'] = $createdAt;
            $data['updated_at'] = $updatedAt;

            // Сохранение в RedisJSON
            $cacheKey = "category:id:{$categoryId}";
            $result = $this->cacheService->jsonSet($cacheKey, '.', $data);
            if (!$result) {
                throw new \Exception("Не удалось сохранить категорию в Redis.");
            }

            // Добавление ID категории в список категорий компании
            $categoryListKey = "company:{$companyId}:categories";
            $result = $this->cacheService->rPush($categoryListKey, (string)$categoryId);
            if ($result === false) {
                throw new \Exception("Не удалось добавить категорию в список категорий компании в Redis.");
            }

            // Добавление события в стрим для асинхронной обработки
            $result = $this->cacheService->addToStream('category_creation_stream', $data);
            if ($result === false) {
                throw new \Exception("Не удалось добавить событие создания категории в стрим Redis.");
            }

            // Фиксация транзакции MySQL
            $this->conn->commit();

            return $data;
        } catch (\Exception $e) {
            // Откат транзакции MySQL в случае ошибки
            $this->conn->rollBack();

            // Логирование ошибки
            error_log("Ошибка при создании категории: " . $e->getMessage());

            throw new \Exception("Не удалось создать категорию: " . $e->getMessage());
        }
    }

    /**
     * Редактирование категории.
     */

    public function editCategory(int $companyId, int $categoryId, array $data): array
    {
        if (empty($categoryId)) {
            throw new \InvalidArgumentException("ID категории обязательно.");
        }


        // Проверка существования категории
        $category = $this->getCategoryById($companyId, $categoryId);
        if (!$category) {
            throw new \Exception("Категория не найдена.");
        }

        // Декларация значений и привязка параметров
        // Привязка параметров для типа INT
        $includedInName = intval($data['includedInName']);
        $discountPercent = intval($data['discountPercent']);
        $markupPercent = intval($data['markupPercent']);
        $askQuantityOnSale = (bool)$data['askQuantityOnSale'];
        $markupFromPrice = (bool)$data['markupFromPrice'];
        $markupWholesale = (bool)$data['markupWholesale'];
        $maxDiscountSet = (bool)$data['maxDiscountSet'];
        $extraChargeSet = (bool)$data['extraChargeSet'];
        $roundingMinusOne = (bool)$data['roundingMinusOne'];
        $fiscal = (bool)$data['fiscal'];
        $excise = (bool)$data['excise'];
        $vatApplicable = (bool)$data['vatApplicable'];
        $barcodeContainsQuantity = (bool)$data['barcodeContainsQuantity'];
        $name = $data['name'];
        $description = $data['description'] ?? '';
        $priceRounding = $data['priceRounding'] ?? '';
        $vatRateCode = $data['vatRateCode'] ?? '';
        $benefitCode = $data['benefitCode'] ?? '';
        $vatExemptionReason = $data['vatExemptionReason'] ?? '';
        $minOrderQuantity = $data['minOrderQuantity'] ?? '';
        $barcodeQuantityCoefficient = $data['barcodeQuantityCoefficient'] ?? '';
        $articleCode = $data['articleCode'] ?? '';


        // Формируем запрос
        $query = "UPDATE Categories SET
        company_id = :company_id,
        name = :name,
        description = :description,
        includedInName = :includedInName,
        askQuantityOnSale = :askQuantityOnSale,
        markupFromPrice = :markupFromPrice,
        maxDiscountSet = :maxDiscountSet,
        extraChargeSet = :extraChargeSet,
        priceRounding = :priceRounding,
        roundingMinusOne = :roundingMinusOne,
        discountPercent = :discountPercent,
        markupWholesale = :markupWholesale,
        markupPercent = :markupPercent,
        fiscal = :fiscal,
        excise = :excise,
        vatApplicable = :vatApplicable,
        vatRateCode = :vatRateCode,
        benefitCode = :benefitCode,
        vatExemptionReason = :vatExemptionReason,
        minOrderQuantity = :minOrderQuantity,
        barcodeContainsQuantity = :barcodeContainsQuantity,
        barcodeQuantityCoefficient = :barcodeQuantityCoefficient,
        articleCode = :articleCode,
        updated_at = :updated_at
    WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Привязка параметров
        // Привязка параметров для типа INT
        $stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->bindParam(':discountPercent', $discountPercent, PDO::PARAM_INT);
        $stmt->bindParam(':markupPercent', $markupPercent, PDO::PARAM_INT);
        $stmt->bindParam(':markupWholesale', $markupWholesale, PDO::PARAM_INT);
        $stmt->bindParam(':includedInName', $includedInName, PDO::PARAM_INT);
        $stmt->bindParam(':askQuantityOnSale', $askQuantityOnSale, PDO::PARAM_INT);
        $stmt->bindParam(':markupFromPrice', $markupFromPrice, PDO::PARAM_INT);
        $stmt->bindParam(':maxDiscountSet', $maxDiscountSet, PDO::PARAM_INT);
        $stmt->bindParam(':extraChargeSet', $extraChargeSet, PDO::PARAM_INT);
        $stmt->bindParam(':roundingMinusOne', $roundingMinusOne, PDO::PARAM_INT);
        $stmt->bindParam(':fiscal', $fiscal, PDO::PARAM_INT);
        $stmt->bindParam(':excise', $excise, PDO::PARAM_INT);
        $stmt->bindParam(':vatApplicable', $vatApplicable, PDO::PARAM_INT);
        $stmt->bindParam(':barcodeContainsQuantity', $barcodeContainsQuantity, PDO::PARAM_INT);
        // Привязка параметров для строковых значений
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':priceRounding', $priceRounding, PDO::PARAM_STR);
        $stmt->bindParam(':vatRateCode', $vatRateCode, PDO::PARAM_STR);
        $stmt->bindParam(':benefitCode', $benefitCode, PDO::PARAM_STR);
        $stmt->bindParam(':vatExemptionReason', $vatExemptionReason, PDO::PARAM_STR);
        $stmt->bindParam(':minOrderQuantity', $minOrderQuantity, PDO::PARAM_STR);
        $stmt->bindParam(':barcodeQuantityCoefficient', $barcodeQuantityCoefficient, PDO::PARAM_STR);
        $stmt->bindParam(':articleCode', $articleCode, PDO::PARAM_STR);

        // Привязка даты
        $updatedAt = date('Y-m-d H:i:s');
        $stmt->bindParam(':updated_at', $updatedAt, PDO::PARAM_STR);


        if ($stmt->execute()) {
            // Обновляем данные в Redis
            $cacheKey = "category:id:{$categoryId}";
            $this->cacheService->del($cacheKey); // Удаление старого кэша

            // Получаем обновленные данные категории
            $updatedCategory = $this->getCategoryById($companyId, $categoryId);
            if (!$updatedCategory) {
                throw new \Exception("Не удалось получить обновленные данные категории.");
            }

            $this->cacheService->jsonSet($cacheKey, '.', $updatedCategory);

            // Возвращаем обновленные данные
            return $updatedCategory;
        }

        throw new \Exception("Не удалось обновить категорию.");
    }


    /**
     * Удаление категории.
     */
    public function deleteCategory(int $companyId, int $categoryId): array
    {
        if (empty($categoryId)) {
            throw new \InvalidArgumentException("ID категории обязательно.");
        }

        // Проверка существования категории
        $category = $this->getCategoryById($companyId, $categoryId);
        if (!$category) {
            throw new Exception("Категория не найдена.");
        }

        // Проверка наличия номенклатур, связанных с категорией
        $query = "SELECT COUNT(*) as count FROM Nomenclature WHERE group_name = :group_name AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':group_name', $category['name'], PDO::PARAM_STR);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            throw new Exception("Невозможно удалить категорию, так как с ней связаны номенклатуры.");
        }

        // Удаляем категорию
        $query = "DELETE FROM Categories WHERE id = :category_id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Удаляем из Redis
            $cacheKey = "category:id:{$categoryId}";
            $this->cacheService->del($cacheKey);

            // Удаляем ключ характеристик
            $characteristicsCacheKey = "category:characteristics:{$categoryId}";
            $this->cacheService->del($characteristicsCacheKey);

            // Удаляем ID категории из списка категорий компании
            $categoryListKey = "company:{$companyId}:categories";
            $this->cacheService->lRem($categoryListKey, (string)$categoryId, 0);

            // Добавление события в стрим для асинхронной обработки
            $this->cacheService->addToStream('category_deletion_stream', [
                'category_id' => $categoryId,
                'company_id' => $companyId,
                'deleted_at' => date('Y-m-d H:i:s')
            ]);

            return ["message" => "Категория удалена успешно."];
        }

        throw new Exception("Не удалось удалить категорию.");
    }

    /**
     * Получение категории по ID.
     */
    public function getCategoryById(int $companyId, int $categoryId): ?array
    {
        $cacheKey = "category:id:{$categoryId}";

        // Попытка получить данные из Redis
        $cachedCategory = $this->cacheService->jsonGet($cacheKey, '.');
        if ($cachedCategory) {
            return json_decode($cachedCategory, true);
        }

        // Если данных нет в Redis, обращаемся к MySQL
        $query = "SELECT c.*, dr.id AS dimension_range_id
          FROM Categories c
          LEFT JOIN DimensionRanges dr ON c.id = dr.category_characteristic_id
          WHERE c.id = :category_id AND c.company_id = :company_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($category) {
            // Добавляем пустой массив характеристик, если их ещё нет
            // $category['characteristics'] = $this->getCategoryCharacteristics($companyId, $categoryId);

            // Сохранение в Redis
            $this->cacheService->jsonSet($cacheKey, '.', $category);

            return $category;
        }

        return null;
    }
    /**
     * Загружает все категории компании из MySQL и сохраняет их в Redis.
     *
     * @param int $companyId
     * @return array Загруженные категории
     * @throws \Exception При ошибках сохранения в Redis или MySQL
     */
    private function loadCategoriesToCache(int $companyId): array
    {
        $cacheKey = "company:{$companyId}:categories";

        // Запрос к MySQL для получения всех категорий компании
        // $query = "SELECT * FROM Categories WHERE company_id = :company_id ORDER BY name";
        $query = "SELECT c.*, dr.id AS dimension_range_id
                        FROM Categories c
                        LEFT JOIN DimensionRanges dr ON c.id = dr.category_characteristic_id
                        WHERE c.company_id = :company_id ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$categories) {
            // Если в базе данных нет категорий, возвращаем пустой массив
            return [];
        }

        // Начало транзакции MySQL для обеспечения консистентности данных
        $this->conn->beginTransaction();

        try {
            foreach ($categories as $category) {
                $categoryId = $category['id'];

                // Получение характеристик категории
                $characteristics = $this->getCategoryCharacteristics($companyId, $categoryId);

                // Формирование данных категории
                $categoryData = array_merge([
                    'id' => $categoryId,
                    'company_id' => $companyId,
                    'options' => $characteristics
                ], $category);

                $pushResult = $this->cacheService->rPush($cacheKey, (string)$categoryId);

                // Добавление ID категории в список категорий компании в Redis
                if ($pushResult === false) {
                    throw new \Exception("Не удалось добавить категорию с ID {$categoryId} в список категорий компании в Redis.");
                }
                // Сохранение данных категории в RedisJSON
                $categoryCacheKey = "category:id:{$categoryId}";
                $jsonSetResult = $this->cacheService->jsonSet($categoryCacheKey, '.', $categoryData);
                if (!$jsonSetResult) {
                    throw new \Exception("Не удалось сохранить категорию с ID {$categoryId} в Redis по ключу {$categoryCacheKey}.");
                }
            }

            // Фиксация транзакции MySQL после успешного сохранения всех категорий в Redis
            $this->conn->commit();

            // Возвращаем список ID категорий
            return array_column($categories, 'id');
        } catch (\Exception $e) {
            // Откат транзакции MySQL в случае ошибки
            $this->conn->rollBack();

            // Логирование ошибки
            error_log("Ошибка при загрузке категорий в кеш Redis: " . $e->getMessage());

            throw $e; // Перебрасываем исключение дальше
        }
    }

    /**
     * Получение списка категорий компании.
     */
    public function getCategoriesByCompany(int $companyId): array
    {
        $cacheKey = "company:{$companyId}:categories";

        try {
            // Попытка получить список категорий из Redis
            $categoryIds = $this->cacheService->lRange($cacheKey, 0, -1);

            if (empty($categoryIds)) {
                // Если данных нет в Redis, загружаем их из MySQL
                $categories = $this->loadCategoriesToCache($companyId);
                return $categories;
            }

            $categories = [];
            foreach ($categoryIds as $id) {
                $categoryCacheKey = "category:id:{$id}";

                // Попытка получить данные категории из Redis
                $data = $this->cacheService->jsonGet($categoryCacheKey, '.');
                if ($data) {
                    $decoded = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $categories[] = $decoded;
                    } else {
                        // Логирование ошибки декодирования JSON
                    }
                } else {
                    // Если данные отсутствуют в Redis, восстанавливаем из MySQL
                    $query = "SELECT c.*, dr.id AS dimension_range_id
                                        FROM Categories c
                                        LEFT JOIN DimensionRanges dr ON c.id = dr.category_characteristic_id
                                        WHERE c.id = :category_id AND c.company_id = :company_id";

                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(':category_id', $id, PDO::PARAM_INT);
                    $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
                    $stmt->execute();

                    $category = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($category) {
                        $category['characteristics'] = $this->getCategoryCharacteristics($companyId, $id);

                        // Сохраняем восстановленные данные в Redis
                        $this->cacheService->jsonSet($categoryCacheKey, '.', $category);

                        $categories[] = $category;
                    } else {
                    }
                }
            }

            return $categories;
        } catch (\Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            throw new \Exception("Не удалось получить категории: " . $e->getMessage());
        }
    }


    /**
     * Получение всех размерных рядов, связанных с категорией.
     *
     * @param int $categoryId
     * @return array
     */
    public function getDimensionRangesForCategory(int $categoryId): array
    {
        $dimensionRangeModel = new DimensionRangeModel($this->conn, $this->cacheService);
        return $dimensionRangeModel->getDimensionRangesByCategory($categoryId);
    }

    /**
     * Добавление характеристики к категории.
     */
    public function addCategoryCharacteristics(int $companyId, int $categoryId, array $characteristics): array
    {
        $category = $this->getCategoryById($companyId, $categoryId);
        if (!$category) {
            throw new Exception("Категория не найдена.");
        }

        $cacheKey = "category_characteristic:company_id:{$companyId}:id:{$categoryId}";

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Ошибка при чтении данных из кэша.");
        }

        foreach ($characteristics as $index => &$char) {
            if ($char['characteristic_type'] === 'select') {
                $char['options'] = json_encode($char['options'], JSON_UNESCAPED_UNICODE);
            }
            // Set the characteristic row ID
            $char['id'] = $index ?? null;
        }

        // Check if category_id exists in CategoryCharacteristics table
        $checkQuery = "SELECT COUNT(*) FROM CategoryCharacteristics WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        $characteristicsJson = json_encode($characteristics, JSON_UNESCAPED_UNICODE);
        $createdAt = date('Y-m-d H:i:s');

        if ($count > 0) {
            // If category_id exists, update the existing record
            $query = "UPDATE CategoryCharacteristics 
                  SET options = :options, updated_at = :updated_at 
                  WHERE category_id = :category_id";
        } else {
            // If category_id does not exist, insert a new record
            $query = "INSERT INTO CategoryCharacteristics (category_id, options, created_at, updated_at) 
                  VALUES (:category_id, :options, :created_at, :updated_at)";
        }

        // Prepare and execute the query
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':options', $characteristicsJson, PDO::PARAM_STR);
        $stmt->bindParam(':updated_at', $createdAt, PDO::PARAM_STR);
        if ($count === 0 || $count === '0') {
            $stmt->bindParam(':created_at', $createdAt, PDO::PARAM_STR);
        }

        if ($stmt->execute()) {
            // Удаляем все существующие характеристики из кеша
            $this->cacheService->del($cacheKey);
            $updated_characteristics = $this->getCategoryCharacteristics($companyId, $categoryId);

            // Set new data in cache
            $this->cacheService->jsonSet($cacheKey, '.', $updated_characteristics);
            $this->cacheService->addToStream('category_characteristic_creation_stream', [
                'category_id' => $categoryId,
                'characteristics' => $updated_characteristics
            ]);
            return json_decode($characteristicsJson, true);
        }

        throw new Exception("Не удалось добавить или обновить характеристики категории.");
    }

    /**
     * Получение характеристик категории.
     */
    public function getCategoryCharacteristics(int $companyId, int $categoryId): array
    {
        error_log("[DEBUG] Начало получения характеристик для категории ID: {$categoryId}, компании ID: {$companyId}");

        $cacheKey = "category_characteristic:company_id:{$companyId}:id:{$categoryId}";

        error_log("[DEBUG] Используется ключ Redis для характеристик: {$cacheKey}");

        // Попытка получить характеристики из Redis
        $cachedCharacteristics = $this->cacheService->jsonGet($cacheKey, '.');
        if ($cachedCharacteristics) {
            $decoded =  json_decode($cachedCharacteristics, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                foreach ($decoded as &$char) {
                    if (isset($char['options']) && is_string($char['options'])) {
                        $decodedOptions = json_decode($char['options'], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedOptions)) {
                            $char['options'] = is_array($decodedOptions) ? $decodedOptions : [];
                        }
                    }

                    error_log("[DEBUG] Обработанная характеристика: " . json_encode($char));
                }

                error_log("[DEBUG] Характеристики найдены в Redis для категории ID: {$categoryId}");
                return $decoded;
            } else {
                error_log("[ERROR] Ошибка декодирования JSON из Redis для ключа {$cacheKey}: " . json_last_error_msg());
            }
        } else {
            error_log("[DEBUG] Характеристики отсутствуют в Redis для категории ID: {$categoryId}");
        }

        // Если данных нет в Redis, обращаемся к MySQL
        error_log("[DEBUG] Запрос характеристик из MySQL для категории ID: {$categoryId}");
        $query = "SELECT * FROM CategoryCharacteristics WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();

        $characteristics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("[DEBUG] Получены характеристики из MySQL для категории ID {$categoryId}: " . json_encode($characteristics));

        foreach ($characteristics as &$char) {
            $char['options'] = $char['options'] ? json_decode($char['options'], true) : [];
            error_log("[DEBUG] Обработанная характеристика: " . json_encode($char));
        }

        // Сохранение в Redis
        $this->cacheService->jsonSet($cacheKey, '.', $characteristics);
        error_log("[DEBUG] Характеристики сохранены в Redis для ключа {$cacheKey}");

        return $characteristics;
    }

    /**
     * Получение связи CategoryCharacteristic по ID с использованием кеширования в Redis.
     *
     * @param int $companyId
     * @param int $categoryCharacteristicId
     * @return array|null
     */
    public function getCategoryCharacteristicById(int $companyId, int $categoryCharacteristicId): ?array
    {
        // Формируем уникальный ключ для кеша, учитывая companyId и categoryCharacteristicId
        $cacheKey = "category_characteristic:company_id:{$companyId}:id:{$categoryCharacteristicId}";

        // Попытка получить данные из Redis
        $cachedData = $this->cacheService->jsonGet($cacheKey, '.');
        if ($cachedData) {
            $decodedData = json_decode($cachedData, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Данные успешно получены из кеша
                return $decodedData;
            } else {
                // Ошибка декодирования JSON, логируем и продолжаем
                error_log("Ошибка декодирования JSON для ключа {$cacheKey}: " . json_last_error_msg());
            }
        }

        // Если данные не найдены в кеше, выполняем запрос к базе данных
        $query = "SELECT cc.*, c.company_id
                  FROM CategoryCharacteristics cc
                  JOIN Categories c ON cc.category_id = c.id
                  WHERE cc.id = :id AND c.company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $categoryCharacteristicId, PDO::PARAM_INT);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        $categoryCharacteristic = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($categoryCharacteristic) {
            // Сохраняем полученные данные в Redis для последующих запросов
            $encodedData = json_encode($categoryCharacteristic);
            if ($encodedData !== false) {
                // Устанавливаем данные в кеш с временем жизни (например, 1 час)
                $this->cacheService->jsonSet($cacheKey, '.', $categoryCharacteristic, ['EX' => 3600]);
            } else {
                // Ошибка кодирования JSON, логируем
                error_log("Ошибка кодирования JSON для данных категории характеристик ID {$categoryCharacteristicId}");
            }

            return $categoryCharacteristic;
        }

        // Если запись не найдена, возвращаем null
        return null;
    }
    /**
     * Удаление характеристики категории.
     */
    public function deleteCategoryCharacteristic(int $companyId, int $categoryId, int $characteristicId): bool
    {
        // Проверка существования характеристики
        $characteristic = $this->getCategoryCharacteristicById($companyId, $characteristicId);
        if (!$characteristic) {
            throw new Exception("Характеристика не найдена.");
        }

        $query = "DELETE FROM CategoryCharacteristics
                  WHERE id = :characteristic_id AND category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':characteristic_id', $characteristicId, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Обновление кеша характеристик категории
            $cacheKey = "category:characteristics:{$categoryId}";
            $currentCharacteristics = $this->getCategoryCharacteristics($companyId, $categoryId);
            $currentCharacteristics = array_filter($currentCharacteristics, function ($char) use ($characteristicId) {
                return $char['id'] !== $characteristicId;
            });
            $this->cacheService->jsonSet($cacheKey, '.', array_values($currentCharacteristics));

            // Добавление события в стрим для асинхронной обработки
            $this->cacheService->addToStream('category_characteristic_deletion_stream', [
                'category_id' => $categoryId,
                'characteristic_id' => $characteristicId,
                'deleted_at' => date('Y-m-d H:i:s')
            ]);

            return true;
        }

        throw new Exception("Не удалось удалить характеристику.");
    }
}
