<?php

namespace Controller;

use Model\CategoryModel;
use Model\CompanyModel;
use Model\UserModel;
use Middleware\AuthMiddleware;
use Service\CacheService;
use Exception;

class CategoryController
{
    private CategoryModel $categoryModel;
    private UserModel $userModel;
    private CompanyModel $companyModel;
    private AuthMiddleware $authMiddleware;

    public function __construct(CategoryModel $categoryModel, UserModel $userModel, CompanyModel $companyModel, AuthMiddleware $authMiddleware)
    {
        $this->categoryModel = $categoryModel;
        $this->userModel = $userModel;
        $this->companyModel = $companyModel;
        $this->authMiddleware = $authMiddleware;
    }

    /**
     * Добавление новой категории
     */
    public function createCategory(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);
            // Валидация данных
            $this->validateCreateCategoryData($data);

            // Данные для новой категории, включая дополнительные поля
            $categoryData = [
                'company_id' => $companyId,
                'name' => $data['name'] ?? '',
                'description' => $data['description'] ?? '',
                'includedInName' => $data['includedInName'] ?? false,
                'askQuantityOnSale' => $data['askQuantityOnSale'] ?? false,
                'markupFromPrice' => $data['markupFromPrice'] ?? false,
                'maxDiscountSet' => $data['maxDiscountSet'] ?? false,
                'extraChargeSet' => $data['extraChargeSet'] ?? false,
                'priceRounding' => $data['priceRounding'] ?? '',
                'roundingMinusOne' => $data['roundingMinusOne'] ?? false,
                'discountPercent' => $data['discountPercent'] ?? '',
                'markupWholesale' => $data['markupWholesale'] ?? '',
                'markupPercent' => $data['markupPercent'] ?? '',
                'fiscal' => $data['fiscal'] ?? false,
                'excise' => $data['excise'] ?? false,
                'vatApplicable' => $data['vatApplicable'] ?? false,
                'vatRateCode' => $data['vatRateCode'] ?? '',
                'benefitCode' => $data['benefitCode'] ?? '',
                'vatExemptionReason' => $data['vatExemptionReason'] ?? '',
                'minOrderQuantity' => $data['minOrderQuantity'] ?? '',
                'barcodeContainsQuantity' => $data['barcodeContainsQuantity'] ?? false,
                'barcodeQuantityCoefficient' => $data['barcodeQuantityCoefficient'] ?? '',
                'articleCode' => $data['articleCode'] ?? ''
            ];

            // Создаем категорию через модель с новыми данными
            $result = $this->categoryModel->createCategory($companyId, $categoryData);

            http_response_code(201);
            echo json_encode([
                "message" => "Категория создана успешно",
                "category_id" => $result['id']
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось создать категорию", "details" => $e->getMessage()]);
        }
    }

    /**
     * Валидация данных для создания категории
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    private function validateCreateCategoryData(array $data): void
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException("Название категории обязательно.");
        }

        // Дополнительные проверки можно добавить здесь
    }
    /**
     * Редактирование категории
     */
    public function editCategory(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Валидация данных
            $this->validateEditCategoryData($data);

            // Данные для обновления категории
            $categoryData = [
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
                'includedInName' => $data['includedInName'] ?? null,
                'askQuantityOnSale' => $data['askQuantityOnSale'] ?? null,
                'markupFromPrice' => $data['markupFromPrice'] ?? null,
                'maxDiscountSet' => $data['maxDiscountSet'] ?? null,
                'extraChargeSet' => $data['extraChargeSet'] ?? null,
                'priceRounding' => $data['priceRounding'] ?? null,
                'roundingMinusOne' => $data['roundingMinusOne'] ?? null,
                'discountPercent' => $data['discountPercent'] ?? null,
                'markupWholesale' => $data['markupWholesale'] ?? null,
                'markupPercent' => $data['markupPercent'] ?? null,
                'fiscal' => $data['fiscal'] ?? null,
                'excise' => $data['excise'] ?? null,
                'vatApplicable' => $data['vatApplicable'] ?? null,
                'vatRateCode' => $data['vatRateCode'] ?? null,
                'benefitCode' => $data['benefitCode'] ?? null,
                'vatExemptionReason' => $data['vatExemptionReason'] ?? null,
                'minOrderQuantity' => $data['minOrderQuantity'] ?? null,
                'barcodeContainsQuantity' => $data['barcodeContainsQuantity'] ?? null,
                'barcodeQuantityCoefficient' => $data['barcodeQuantityCoefficient'] ?? null,
                'articleCode' => $data['articleCode'] ?? null
            ];

            // Фильтруем пустые значения, чтобы не обновлять их
            $categoryData = array_filter($categoryData, fn($value) => !is_null($value));

            // Редактируем категорию через модель
            $result = $this->categoryModel->editCategory(
                intval($companyId),
                intval($data['category_id']),
                $categoryData
            );

            http_response_code(200);
            echo json_encode([
                "message" => "Категория обновлена успешно",
                "details" => $result
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось обновить категорию", "details" => $e->getMessage()]);
        }
    }
    /**
     * Валидация данных для редактирования категории
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    private function validateEditCategoryData(array $data): void
    {
        if (empty($data['category_id'])) {
            throw new \InvalidArgumentException("ID категории обязательно.");
        }

        // Дополнительные проверки можно добавить здесь
    }

    /**
     * Удаление категории
     */
    public function deleteCategory(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);

            if (empty($data['category_id'])) {
                throw new \InvalidArgumentException("ID категории обязательно.");
            }

            // Удаляем категорию через модель
            $result = $this->categoryModel->deleteCategory($companyId, $data['category_id']);

            http_response_code(200);
            echo json_encode([
                "message" => "Категория удалена успешно",
                "details" => $result
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось удалить категорию", "details" => $e->getMessage()]);
        }
    }

    /**
     * Получение списка категорий с иерархией
     */
    public function listCategories(): void
    {
        try {
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Получаем список категорий через модель
            $categories = $this->categoryModel->getCategoriesByCompany($companyId);

            http_response_code(200);
            echo json_encode([
                "categories" => $categories
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось получить список категорий", "details" => $e->getMessage()]);
        }
    }

    /**
     * Получение категории по ID (через POST-запрос)
     */
    public function getCategoryById(): void
    {
        try {
            // Проверяем, передан ли ID в теле запроса
            $inputData = json_decode(file_get_contents("php://input"), true);

            if (!isset($inputData['id'])) {
                throw new \InvalidArgumentException("Параметр 'id' обязателен.");
            }

            $categoryId = intval($inputData['id']);

            if ($categoryId <= 0) {
                throw new \InvalidArgumentException("Некорректный ID категории.");
            }

            // Аутентификация пользователя
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            // Получение ID компании пользователя
            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Получение категории по ID через модель
            $category = $this->categoryModel->getCategoryById($companyId, $categoryId);

            if (!$category) {
                throw new \Exception("Категория не найдена.");
            }

            // Возврат успешного ответа
            http_response_code(200);
            echo json_encode(["category" => $category]);
        } catch (\InvalidArgumentException $e) {
            // Ошибки валидации
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            // Общие ошибки
            http_response_code(500);
            echo json_encode(["error" => "Не удалось получить категорию", "details" => $e->getMessage()]);
        }
    }


    /**
     * Добавление характеристики к категории
     */
    public function addCharacteristic(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);
            $categoryId = intval($data['category_id']) ?? null;
            $characteristics = $data['characteristics'] ?? [];

            if (!$categoryId || empty($characteristics)) {
                throw new \InvalidArgumentException("Категория и характеристики обязательны.");
            }

            $this->validateCharacteristicData($categoryId, $characteristics);

            $result = $this->categoryModel->addCategoryCharacteristics($companyId, $categoryId, $characteristics);

            http_response_code(201);
            echo json_encode([
                "message" => "Характеристики добавлены успешно",
                "characteristics" => $result
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось добавить характеристики", "details" => $e->getMessage()]);
        }
    }

    /**
     * Валидация данных для добавления характеристик
     *
     * @param int $categoryId
     * @param array $characteristics
     * @throws \InvalidArgumentException
     */
    private function validateCharacteristicData(int $categoryId, array $characteristics): void
    {
        if (!$categoryId) {
            throw new \InvalidArgumentException("ID категории обязательно.");
        }

        if (empty($characteristics)) {
            return;  // Exit the function if $characteristics is empty
        }
        $validTypes = ['text', 'number', 'select'];

        foreach ($characteristics as $char) {
            if (empty($char['characteristic_name'])) {
                throw new \InvalidArgumentException("Название характеристики обязательно.");
            }

            if (empty($char['characteristic_type'])) {
                throw new \InvalidArgumentException("Тип характеристики обязателен.");
            }

            if (!in_array($char['characteristic_type'], $validTypes)) {
                throw new \InvalidArgumentException("Неверный тип характеристики. Допустимые типы: text, number, select.");
            }

            if ($char['characteristic_type'] === 'select' && (empty($char['options']) || !is_array($char['options']))) {
                throw new \InvalidArgumentException("Для типа 'select' необходимо предоставить массив опций.");
            }
        }
    }

    /**
     * Получение характеристик категории
     */
    public function getCharacteristics(): void
    {
        try {
            // Получение параметров запроса
            $categoryId = $_GET['category_id'] ?? null;

            if (empty($categoryId)) {
                throw new \InvalidArgumentException("Параметр category_id обязателен.");
            }

            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Получаем характеристики через модель
            $characteristics = $this->categoryModel->getCategoryCharacteristics($companyId, $categoryId);

            http_response_code(200);
            echo json_encode([
                "characteristics" => $characteristics
            ], true);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось получить характеристики категории", "details" => $e->getMessage()]);
        }
    }

    /**
     * Удаление характеристики категории
     */
    public function deleteCharacteristic(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);

            if (empty($data['category_id']) || empty($data['characteristic_id'])) {
                throw new \InvalidArgumentException("Параметры category_id и characteristic_id обязательны.");
            }

            // Удаляем характеристику через модель
            $result = $this->categoryModel->deleteCategoryCharacteristic($companyId, $data['category_id'], $data['characteristic_id']);

            http_response_code(200);
            echo json_encode([
                "message" => "Характеристика удалена успешно",
                "details" => $result
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось удалить характеристику", "details" => $e->getMessage()]);
        }
    }

    /**
     * Валидация данных для удаления характеристики
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    private function validateDeleteCharacteristicData(array $data): void
    {
        if (empty($data['category_id'])) {
            throw new \InvalidArgumentException("ID категории обязательно.");
        }

        if (empty($data['characteristic_id'])) {
            throw new \InvalidArgumentException("ID характеристики обязательно.");
        }
    }
}
