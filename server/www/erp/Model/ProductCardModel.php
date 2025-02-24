<?php

namespace Model;

use PDO;
use Service\CacheService;
use Exception;

/**
 * Класс ProductCardModel отвечает за управление карточками товаров в системе с использованием кеширования Redis.
 */
class ProductCardModel
{
    protected $conn;
    protected $cacheService;

    public function __construct(PDO $conn, CacheService $cacheService)
    {
        $this->conn = $conn;
        $this->cacheService = $cacheService;

        if (!$cacheService) {
            throw new Exception("CacheService instance is not provided in ProductCardModel.");
        }
    }

    /**
     * Создание новой карточки товара.
     */
    public function createProductCard(int $companyId, int $nomenclatureId, string $productName, string $sku, float $price, int $stock = 0, string $barcode, array $images = [], array $characteristics = []): array
    {
        if (empty($productName) || empty($sku) || empty($barcode)) {
            throw new \InvalidArgumentException("Название товара, артикул и штрих-код обязательны.");
        }

        // Проверка уникальности SKU и Barcode
        if ($this->getProductCardBySKU($companyId, $sku)) {
            throw new Exception("Карточка товара с таким SKU уже существует.");
        }

        if ($this->getProductCardByBarcode($companyId, $barcode)) {
            throw new Exception("Карточка товара с таким штрих-кодом уже существует.");
        }

        $createdAt = date('Y-m-d H:i:s');

        // Начало транзакции для обеспечения целостности данных
        $this->conn->beginTransaction();

        try {
            // Вставка в таблицу ProductCards
            $query = "INSERT INTO ProductCards (company_id, nomenclature_id, product_name, sku, price, stock, barcode, images, created_at, updated_at)
                      VALUES (:company_id, :nomenclature_id, :product_name, :sku, :price, :stock, :barcode, :images, :created_at, :updated_at)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
            $stmt->bindParam(':nomenclature_id', $nomenclatureId, PDO::PARAM_INT);
            $stmt->bindParam(':product_name', $productName, PDO::PARAM_STR);
            $stmt->bindParam(':sku', $sku, PDO::PARAM_STR);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
            $stmt->bindParam(':barcode', $barcode, PDO::PARAM_STR);
            $stmt->bindParam(':images', json_encode($images), PDO::PARAM_STR);
            $stmt->bindParam(':created_at', $createdAt, PDO::PARAM_STR);
            $stmt->bindParam(':updated_at', $createdAt, PDO::PARAM_STR);

            if (!$stmt->execute()) {
                throw new Exception("Не удалось создать карточку товара.");
            }

            $productCardId = (int)$this->conn->lastInsertId();

            // Вставка характеристик
            foreach ($characteristics as $charName => $charValue) {
                $charQuery = "INSERT INTO ProductCardCharacteristics (product_card_id, characteristic_name, value)
                             VALUES (:product_card_id, :characteristic_name, :value)";
                $charStmt = $this->conn->prepare($charQuery);
                $charStmt->bindParam(':product_card_id', $productCardId, PDO::PARAM_INT);
                $charStmt->bindParam(':characteristic_name', $charName, PDO::PARAM_STR);
                $charStmt->bindParam(':value', $charValue, PDO::PARAM_STR);
                if (!$charStmt->execute()) {
                    throw new Exception("Не удалось добавить характеристику к карточке товара.");
                }
            }

            // Подтверждение транзакции
            $this->conn->commit();

            // Получение данных карточки товара
            $productCardData = $this->getProductCardById($companyId, $productCardId);
            if (!$productCardData) {
                throw new Exception("Не удалось получить данные созданной карточки товара.");
            }

            // Сохранение в Redis
            $cacheKey = "product_card:id:{$productCardId}";
            $this->cacheService->set($cacheKey, json_encode($productCardData));

            // Добавление ID карточки товара в список товаров компании
            $productsListKey = "company:{$companyId}:product_cards";
            $this->cacheService->rPush($productsListKey, (string)$productCardId);

            // Добавление события в стрим для асинхронной обработки
            $this->cacheService->addToStream('product_card_creation_stream', $productCardData);

            return $productCardData;
        } catch (Exception $e) {
            // Откат транзакции в случае ошибки
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Редактирование карточки товара.
     */
    public function editProductCard(int $companyId, int $productCardId, ?string $productName = null, ?string $sku = null, ?float $price = null, ?int $stock = null, ?string $barcode = null, ?array $images = null, ?array $characteristics = null): array
    {
        if (empty($productCardId)) {
            throw new \InvalidArgumentException("ID карточки товара обязательно.");
        }

        $updatedAt = date('Y-m-d H:i:s');

        // Проверка существования карточки товара
        $productCard = $this->getProductCardById($companyId, $productCardId);
        if (!$productCard) {
            throw new Exception("Карточка товара не найдена.");
        }

        // Проверка уникальности SKU и Barcode, если они изменяются
        if ($sku !== null && $sku !== $productCard['sku']) {
            if ($this->getProductCardBySKU($companyId, $sku)) {
                throw new Exception("Карточка товара с таким SKU уже существует.");
            }
        }

        if ($barcode !== null && $barcode !== $productCard['barcode']) {
            if ($this->getProductCardByBarcode($companyId, $barcode)) {
                throw new Exception("Карточка товара с таким штрих-кодом уже существует.");
            }
        }

        // Начало транзакции
        $this->conn->beginTransaction();

        try {
            // Формируем динамический SQL-запрос
            $fields = [];
            $params = [':product_card_id' => $productCardId, ':company_id' => $companyId];

            if ($productName !== null) {
                $fields[] = "product_name = :product_name";
                $params[':product_name'] = $productName;
            }

            if ($sku !== null) {
                $fields[] = "sku = :sku";
                $params[':sku'] = $sku;
            }

            if ($price !== null) {
                $fields[] = "price = :price";
                $params[':price'] = $price;
            }

            if ($stock !== null) {
                $fields[] = "stock = :stock";
                $params[':stock'] = $stock;
            }

            if ($barcode !== null) {
                $fields[] = "barcode = :barcode";
                $params[':barcode'] = $barcode;
            }

            if ($images !== null) {
                $fields[] = "images = :images";
                $params[':images'] = json_encode($images);
            }

            if (empty($fields) && $characteristics === null) {
                throw new Exception("Нет данных для обновления.");
            }

            if (!empty($fields)) {
                $fields[] = "updated_at = :updated_at";
                $params[':updated_at'] = $updatedAt;

                $query = "UPDATE ProductCards SET " . implode(', ', $fields) . " WHERE id = :product_card_id AND company_id = :company_id";
                $stmt = $this->conn->prepare($query);

                foreach ($params as $key => $value) {
                    if ($key === ':images' && $value === null) {
                        $stmt->bindValue($key, null, PDO::PARAM_NULL);
                    } elseif (is_int($value)) {
                        $stmt->bindValue($key, $value, PDO::PARAM_INT);
                    } elseif (is_float($value)) {
                        $stmt->bindValue($key, $value);
                    } else {
                        $stmt->bindValue($key, $value, PDO::PARAM_STR);
                    }
                }

                if (!$stmt->execute()) {
                    throw new Exception("Не удалось обновить карточку товара.");
                }
            }

            // Обновление характеристик, если предоставлены
            if ($characteristics !== null) {
                // Удаляем существующие характеристики
                $deleteQuery = "DELETE FROM ProductCardCharacteristics WHERE product_card_id = :product_card_id";
                $deleteStmt = $this->conn->prepare($deleteQuery);
                $deleteStmt->bindParam(':product_card_id', $productCardId, PDO::PARAM_INT);
                if (!$deleteStmt->execute()) {
                    throw new Exception("Не удалось удалить существующие характеристики.");
                }

                // Добавляем новые характеристики
                foreach ($characteristics as $charName => $charValue) {
                    $charQuery = "INSERT INTO ProductCardCharacteristics (product_card_id, characteristic_name, value)
                                 VALUES (:product_card_id, :characteristic_name, :value)";
                    $charStmt = $this->conn->prepare($charQuery);
                    $charStmt->bindParam(':product_card_id', $productCardId, PDO::PARAM_INT);
                    $charStmt->bindParam(':characteristic_name', $charName, PDO::PARAM_STR);
                    $charStmt->bindParam(':value', $charValue, PDO::PARAM_STR);
                    if (!$charStmt->execute()) {
                        throw new Exception("Не удалось добавить характеристику к карточке товара.");
                    }
                }
            }

            // Подтверждение транзакции
            $this->conn->commit();

            // Получение обновленных данных карточки товара
            $updatedProductCard = $this->getProductCardById($companyId, $productCardId);
            if (!$updatedProductCard) {
                throw new Exception("Не удалось получить обновленные данные карточки товара.");
            }

            // Обновляем данные в Redis
            $cacheKey = "product_card:id:{$productCardId}";
            $this->cacheService->set($cacheKey, json_encode($updatedProductCard));

            // Добавление события в стрим для асинхронной обработки
            $this->cacheService->addToStream('product_card_update_stream', $updatedProductCard);

            return $updatedProductCard;
        } catch (Exception $e) {
            // Откат транзакции в случае ошибки
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Удаление карточки товара.
     */
    public function deleteProductCard(int $companyId, int $productCardId): array
    {
        if (empty($productCardId)) {
            throw new \InvalidArgumentException("ID карточки товара обязательно.");
        }

        // Проверка существования карточки товара
        $productCard = $this->getProductCardById($companyId, $productCardId);
        if (!$productCard) {
            throw new Exception("Карточка товара не найдена.");
        }

        $query = "DELETE FROM ProductCards WHERE id = :product_card_id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_card_id', $productCardId, PDO::PARAM_INT);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Удаляем из Redis
            $cacheKey = "product_card:id:{$productCardId}";
            $this->cacheService->del($cacheKey);

            // Удаляем ID карточки товара из списка товаров компании
            $productsListKey = "company:{$companyId}:product_cards";
            $this->cacheService->lRem($productsListKey, (string)$productCardId, 0);

            // Добавление события в стрим для асинхронной обработки
            $this->cacheService->addToStream('product_card_deletion_stream', [
                'product_card_id' => $productCardId,
                'company_id' => $companyId,
                'deleted_at' => date('Y-m-d H:i:s')
            ]);

            return ["message" => "Карточка товара удалена успешно."];
        }

        throw new Exception("Не удалось удалить карточку товара.");
    }

    /**
     * Получение карточки товара по ID.
     */
    public function getProductCardById(int $companyId, int $productCardId): ?array
    {
        $cacheKey = "product_card:id:{$productCardId}";

        // Попытка получить данные из Redis
        $cachedProductCard = $this->cacheService->get($cacheKey);
        if ($cachedProductCard) {
            return json_decode($cachedProductCard, true);
        }

        // Если данных нет в Redis, обращаемся к MySQL
        $query = "SELECT * FROM ProductCards WHERE id = :product_card_id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_card_id', $productCardId, PDO::PARAM_INT);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        $productCard = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($productCard) {
            // // Получение характеристик
            // $charQuery = "SELECT characteristic_name, value FROM ProductCardCharacteristics WHERE product_card_id = :product_card_id";
            // $charStmt = $this->conn->prepare($charQuery);
            // $charStmt->bindParam(':product_card_id', $productCardId, PDO::PARAM_INT);
            // $charStmt->execute();
            // $characteristics = $charStmt->fetchAll(PDO::FETCH_ASSOC);

            // $productCard['characteristics'] = [];
            // foreach ($characteristics as $char) {
            //     $productCard['characteristics'][$char['characteristic_name']] = $char['value'];
            // }

            // Декодирование изображений
            $productCard['images'] = $productCard['images'] ? json_decode($productCard['images'], true) : [];

            // Сохранение в Redis
            $this->cacheService->set($cacheKey, json_encode($productCard));

            return $productCard;
        }

        return null;
    }

    /**
     * Получение карточки товара по SKU.
     */
    public function getProductCardBySKU(int $companyId, string $sku): ?array
    {
        $cacheKey = "product_card:sku:{$sku}";

        // Попытка получить данные из Redis
        $cachedProductCard = $this->cacheService->get($cacheKey);
        if ($cachedProductCard) {
            return json_decode($cachedProductCard, true);
        }

        // Если данных нет в Redis, обращаемся к MySQL
        $query = "SELECT * FROM ProductCards WHERE sku = :sku AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sku', $sku, PDO::PARAM_STR);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        $productCard = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($productCard) {
            // Получение характеристик
            $charQuery = "SELECT characteristic_name, value FROM ProductCardCharacteristics WHERE product_card_id = :product_card_id";
            $charStmt = $this->conn->prepare($charQuery);
            $charStmt->bindParam(':product_card_id', $productCard['id'], PDO::PARAM_INT);
            $charStmt->execute();
            $characteristics = $charStmt->fetchAll(PDO::FETCH_ASSOC);

            $productCard['characteristics'] = [];
            foreach ($characteristics as $char) {
                $productCard['characteristics'][$char['characteristic_name']] = $char['value'];
            }

            // Декодирование изображений
            $productCard['images'] = $productCard['images'] ? json_decode($productCard['images'], true) : [];

            // Сохранение в Redis
            $this->cacheService->set($cacheKey, json_encode($productCard));

            // Сохранение по ID
            $idCacheKey = "product_card:id:{$productCard['id']}";
            $this->cacheService->set($idCacheKey, json_encode($productCard));

            return $productCard;
        }

        return null;
    }

    /**
     * Получение карточки товара по Barcode.
     */
    public function getProductCardByBarcode(int $companyId, string $barcode): ?array
    {
        $cacheKey = "product_card:barcode:{$barcode}";

        // Попытка получить данные из Redis
        $cachedProductCard = $this->cacheService->get($cacheKey);
        if ($cachedProductCard) {
            return json_decode($cachedProductCard, true);
        }

        // Если данных нет в Redis, обращаемся к MySQL
        $query = "SELECT * FROM ProductCards WHERE barcode = :barcode AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':barcode', $barcode, PDO::PARAM_STR);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        $productCard = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($productCard) {
            // Получение характеристик
            $charQuery = "SELECT characteristic_name, value FROM ProductCardCharacteristics WHERE product_card_id = :product_card_id";
            $charStmt = $this->conn->prepare($charQuery);
            $charStmt->bindParam(':product_card_id', $productCard['id'], PDO::PARAM_INT);
            $charStmt->execute();
            $characteristics = $charStmt->fetchAll(PDO::FETCH_ASSOC);

            $productCard['characteristics'] = [];
            foreach ($characteristics as $char) {
                $productCard['characteristics'][$char['characteristic_name']] = $char['value'];
            }

            // Декодирование изображений
            $productCard['images'] = $productCard['images'] ? json_decode($productCard['images'], true) : [];

            // Сохранение в Redis
            $this->cacheService->set($cacheKey, json_encode($productCard));

            // Сохранение по ID
            $idCacheKey = "product_card:id:{$productCard['id']}";
            $this->cacheService->set($idCacheKey, json_encode($productCard));

            return $productCard;
        }

        return null;
    }

    /**
     * Получение списка карточек товаров компании с фильтрацией и пагинацией.
     */
    public function getProductCardsList(int $companyId, ?int $nomenclatureId, ?string $search, int $limit, int $offset): array
    {
        // Ключ для списка карточек товаров компании
        $productCardsListKey = "company:{$companyId}:product_cards";

        // Получаем список карточек товаров из Redis с учетом пагинации
        $productCardIds = $this->cacheService->lRange($productCardsListKey, $offset, $offset + $limit - 1);

        if (empty($productCardIds)) {
            // Если данных нет в Redis, обращаемся к MySQL
            $productCards = $this->fetchProductCardsFromMySQL($companyId, $nomenclatureId, $search, $limit, $offset);

            // Сохранение полученных карточек товаров в Redis
            foreach ($productCards as $productCard) {
                $cacheKey = "product_card:id:{$productCard['id']}";
                $this->cacheService->set($cacheKey, json_encode($productCard));
            }

            return $productCards;
        }

        // Получаем данные карточек товаров из Redis
        $cacheKeys = array_map(fn($id) => "product_card:id:{$id}", $productCardIds);
        $cachedProductCardsData = $this->cacheService->mGet($cacheKeys);

        // Декодируем данные карточек товаров
        $productCards = [];
        foreach ($cachedProductCardsData as $data) {
            if ($data) {
                $productCards[] = json_decode($data, true);
            }
        }

        // Применяем фильтрацию и поиск на уровне приложения (опционально)
        if ($nomenclatureId !== null) {
            $productCards = array_filter($productCards, fn($product) => $product['nomenclature_id'] === $nomenclatureId);
        }

        if ($search !== null && $search !== '') {
            $productCards = array_filter($productCards, fn($product) => stripos($product['product_name'], $search) !== false);
        }

        return array_slice($productCards, 0, $limit);
    }

    /**
     * Получение общего количества карточек товаров для компании с фильтрацией.
     */
    public function getTotalProductCardsCount(int $companyId, ?int $nomenclatureId, ?string $search): int
    {
        // Формируем динамический SQL-запрос
        $query = "SELECT COUNT(*) as count FROM ProductCards WHERE company_id = :company_id";
        $params = [':company_id' => $companyId];

        if ($nomenclatureId !== null) {
            $query .= " AND nomenclature_id = :nomenclature_id";
            $params[':nomenclature_id'] = $nomenclatureId;
        }

        if ($search !== null && $search !== '') {
            $query .= " AND product_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$result['count'];
    }

    /**
     * Получение списка карточек товаров из MySQL.
     */
    private function fetchProductCardsFromMySQL(int $companyId, ?int $nomenclatureId, ?string $search, int $limit, int $offset): array
    {
        $query = "SELECT * FROM ProductCards WHERE company_id = :company_id";
        $params = [':company_id' => $companyId];

        if ($nomenclatureId !== null) {
            $query .= " AND nomenclature_id = :nomenclature_id";
            $params[':nomenclature_id'] = $nomenclatureId;
        }

        if ($search !== null && $search !== '') {
            $query .= " AND product_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                continue;
            }
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        $productCards = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Получение характеристик для каждой карточки товара
        foreach ($productCards as &$productCard) {
            $charQuery = "SELECT characteristic_name, value FROM ProductCardCharacteristics WHERE product_card_id = :product_card_id";
            $charStmt = $this->conn->prepare($charQuery);
            $charStmt->bindParam(':product_card_id', $productCard['id'], PDO::PARAM_INT);
            $charStmt->execute();
            $characteristics = $charStmt->fetchAll(PDO::FETCH_ASSOC);

            $productCard['characteristics'] = [];
            foreach ($characteristics as $char) {
                $productCard['characteristics'][$char['characteristic_name']] = $char['value'];
            }

            // Декодирование изображений
            $productCard['images'] = $productCard['images'] ? json_decode($productCard['images'], true) : [];
        }

        return $productCards;
    }

    /**
     * Получение списка всех карточек товаров.
     */
    public function listAllProductCards(): array
    {
        $query = "SELECT * FROM ProductCards ORDER BY company_id, nomenclature_id, product_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $productCards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $productCards;
    }
}
