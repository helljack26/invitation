<?php

namespace Controller;

use Model\ProductCardModel;
use Model\NomenclatureModel;
use Model\CompanyModel;
use Model\UserModel;
use Middleware\AuthMiddleware;
use Service\CacheService;

class ProductCardController
{
    private ProductCardModel $productCardModel;
    private NomenclatureModel $nomenclatureModel;
    private UserModel $userModel;
    private CompanyModel $companyModel;
    private AuthMiddleware $authMiddleware;

    public function __construct(ProductCardModel $productCardModel, NomenclatureModel $nomenclatureModel, UserModel $userModel, CompanyModel $companyModel, AuthMiddleware $authMiddleware)
    {
        $this->productCardModel = $productCardModel;
        $this->nomenclatureModel = $nomenclatureModel;
        $this->userModel = $userModel;
        $this->companyModel = $companyModel;
        $this->authMiddleware = $authMiddleware;
    }

    /**
     * Добавление новой карточки товара
     */
    public function createProductCard(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Валидация данных
            $this->validateCreateProductCardData($data);

            // Проверка существования номенклатуры
            $nomenclature = $this->nomenclatureModel->getNomenclatureById($companyId, $data['nomenclature_id']);
            if (!$nomenclature) {
                throw new \Exception("Номенклатура не найдена.");
            }

            // Создаем карточку товара через модель
            $result = $this->productCardModel->createProductCard(
                $companyId,
                $data['nomenclature_id'],
                $data['product_name'],
                $data['sku'],
                $data['price'],
                $data['stock'] ?? 0,
                $data['barcode'],
                $data['images'] ?? [],
                $data['characteristics'] ?? []
            );

            http_response_code(201);
            echo json_encode([
                "message" => "Карточка товара создана успешно",
                "product_card" => $result
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось создать карточку товара", "details" => $e->getMessage()]);
        }
    }

    /**
     * Валидация данных для создания карточки товара
     */
    private function validateCreateProductCardData(array $data): void
    {
        $requiredFields = ['nomenclature_id', 'product_name', 'sku', 'price', 'barcode'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Поле '{$field}' обязательно.");
            }
        }

        if (!is_numeric($data['price'])) {
            throw new \InvalidArgumentException("Цена товара должна быть числом.");
        }

        // Дополнительные проверки можно добавить здесь
    }

    /**
     * Редактирование карточки товара
     */
    public function editProductCard(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Валидация данных
            $this->validateEditProductCardData($data);

            // Проверка существования карточки товара
            $productCard = $this->productCardModel->getProductCardById($companyId, $data['product_card_id']);
            if (!$productCard) {
                throw new \Exception("Карточка товара не найдена.");
            }

            // Если указана новая номенклатура, проверяем её существование
            if (isset($data['nomenclature_id'])) {
                $nomenclature = $this->nomenclatureModel->getNomenclatureById($companyId, $data['nomenclature_id']);
                if (!$nomenclature) {
                    throw new \Exception("Новая номенклатура не найдена.");
                }
            }

            // Редактируем карточку товара через модель
            $result = $this->productCardModel->editProductCard(
                $companyId,
                $data['product_card_id'],
                $data['nomenclature_id'] ?? null,
                $data['product_name'] ?? null,
                $data['sku'] ?? null,
                $data['price'] ?? null,
                $data['stock'] ?? null,
                $data['barcode'] ?? null,
                $data['images'] ?? null,
                $data['characteristics'] ?? null
            );

            http_response_code(200);
            echo json_encode([
                "message" => "Карточка товара обновлена успешно",
                "product_card" => $result
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось обновить карточку товара", "details" => $e->getMessage()]);
        }
    }

    /**
     * Валидация данных для редактирования карточки товара
     */
    private function validateEditProductCardData(array $data): void
    {
        if (empty($data['product_card_id'])) {
            throw new \InvalidArgumentException("ID карточки товара обязательно.");
        }

        // Дополнительные проверки можно добавить здесь
    }

    /**
     * Удаление карточки товара
     */
    public function deleteProductCard(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);

            if (empty($data['product_card_id'])) {
                throw new \InvalidArgumentException("ID карточки товара обязательно.");
            }

            // Удаляем карточку товара через модель
            $result = $this->productCardModel->deleteProductCard($companyId, $data['product_card_id']);

            http_response_code(200);
            echo json_encode([
                "message" => "Карточка товара удалена успешно",
                "details" => $result
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось удалить карточку товара", "details" => $e->getMessage()]);
        }
    }

    /**
     * Получение списка карточек товаров
     */
    public function listProductCards(): void
    {
        try {
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Проверка на принадлежность пользователей одной компании
            if ($companyId) {
                http_response_code(403);
                echo json_encode(["error" => "Access denied. Users are not from the same company."]);
                return;
            }

            // Проверка прав на назначение роли, включая проверку владельца компании
            if (!$this->userModel->hasPermission($userId, 'Змінити роль користувача', $companyId)) {
                http_response_code(403);
                echo json_encode(["error" => "Permission denied."]);
                return;
            }

            // Получаем параметры пагинации и фильтрации из GET-запроса
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 50;
            $nomenclatureId = isset($_GET['nomenclature_id']) ? (int)$_GET['nomenclature_id'] : null;
            $search = isset($_GET['search']) ? trim($_GET['search']) : null;

            // Валидация параметров пагинации
            if ($page < 1) {
                throw new \InvalidArgumentException("Параметр 'page' должен быть положительным целым числом.");
            }

            if ($perPage < 1 || $perPage > 1000) {
                throw new \InvalidArgumentException("Параметр 'per_page' должен быть между 1 и 1000.");
            }

            // Получаем текущий пакет карточек товаров через модель
            $productCardsBatch = $this->productCardModel->getProductCardsList($companyId, $nomenclatureId, $search, $perPage, ($page - 1) * $perPage);

            // Получаем общее количество карточек товаров для компании
            $totalProductCards = $this->productCardModel->getTotalProductCardsCount($companyId, $nomenclatureId, $search);

            // Вычисляем общее количество страниц
            $totalPages = ceil($totalProductCards / $perPage);

            http_response_code(200);
            echo json_encode([
                "product_cards" => $productCardsBatch,
                "pagination" => [
                    "current_page" => $page,
                    "per_page" => $perPage,
                    "total_product_cards" => $totalProductCards,
                    "total_pages" => $totalPages
                ]
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось получить список карточек товаров", "details" => $e->getMessage()]);
        }
    }

    /**
     * Получение карточки товара по ID (через POST-запрос)
     */
    public function getProductCardById(): void
    {
        try {
            // Проверяем, передан ли ID в теле запроса
            $inputData = json_decode(file_get_contents("php://input"), true);

            if (!isset($inputData['id'])) {
                throw new \InvalidArgumentException("Параметр 'id' обязателен.");
            }

            $productCardId = intval($inputData['id']);

            if ($productCardId <= 0) {
                throw new \InvalidArgumentException("Некорректный ID карточки товара.");
            }

            // Аутентификация пользователя
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            // Получение ID компании пользователя
            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Получение карточки товара по ID через модель
            $productCard = $this->productCardModel->getProductCardById($companyId, $productCardId);

            if (!$productCard) {
                throw new \Exception("Карточка товара не найдена.");
            }

            // Возврат успешного ответа
            http_response_code(200);
            echo json_encode(["productCard" => $productCard]);
        } catch (\InvalidArgumentException $e) {
            // Ошибки валидации
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            // Общие ошибки
            http_response_code(500);
            echo json_encode(["error" => "Не удалось получить карточку товара", "details" => $e->getMessage()]);
        }
    }
}
