<?php

namespace Controller;

use Model\DimensionRangeModel;
use Model\CategoryModel;
use Model\CompanyModel;
use Middleware\AuthMiddleware;
use Exception;

/**
 * Класс DimensionRangeController отвечает за обработку HTTP-запросов, связанных с размерными рядами и их значениями.
 */
class DimensionRangeController
{
    private DimensionRangeModel $dimensionRangeModel;
    private CategoryModel $categoryModel;
    private CompanyModel $companyModel;
    private AuthMiddleware $authMiddleware;

    /**
     * Конструктор контроллера.
     *
     * @param DimensionRangeModel $dimensionRangeModel
     * @param CategoryModel $categoryModel
     * @param CompanyModel $companyModel
     * @param AuthMiddleware $authMiddleware
     */
    public function __construct(
        DimensionRangeModel $dimensionRangeModel,
        CategoryModel $categoryModel,
        CompanyModel $companyModel,
        AuthMiddleware $authMiddleware
    ) {
        $this->dimensionRangeModel = $dimensionRangeModel;
        $this->categoryModel = $categoryModel;
        $this->companyModel = $companyModel;
        $this->authMiddleware = $authMiddleware;
    }

    /**
     * Создание нового размерного ряда.
     *
     * @return void
     */
    public function createDimensionRange(): void
    {
        try {
            // Аутентификация пользователя
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            // Получение ID компании пользователя через CompanyModel
            $companyId = $this->companyModel->getUserCompanyId($userId);

            if (!$companyId) {
                throw new \Exception("Компания пользователя не найдена.");
            }

            // Получение и декодирование данных из входного запроса
            $data = json_decode(file_get_contents('php://input'), true);

            if (!is_array($data)) {
                throw new \InvalidArgumentException("Некорректный формат данных.");
            }

            // Валидация входных данных
            $this->validateCreateDimensionRangeData($data);

            // Извлечение данных
            // Теперь требуется category_characteristic_id вместо category_id
            $categoryCharacteristicId = intval($data['category_characteristic_id']);
            $name = trim($data['name']);
            $description = trim($data['description'] ?? '');
            $values = $data['values'];

            // Проверка существования связи CategoryCharacteristic через CategoryModel
            $categoryCharacteristic = $this->categoryModel->getCategoryCharacteristicById($companyId, $categoryCharacteristicId);
            if (!$categoryCharacteristic) {
                throw new \InvalidArgumentException("Связь CategoryCharacteristic с ID {$categoryCharacteristicId} не найдена.");
            }

            // Создание размерного ряда через DimensionRangeModel
            $dimensionRange = $this->dimensionRangeModel->createDimensionRange(
                $categoryCharacteristicId,
                $name,
                $description,
                $values
            );

            // Возврат успешного ответа
            http_response_code(201);
            echo json_encode([
                "message" => "Размерный ряд создан успешно.",
                "dimension_range" => $dimensionRange
            ]);
        } catch (\InvalidArgumentException $e) {
            // Обработка ошибок валидации
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            // Обработка общих ошибок
            http_response_code(500);
            echo json_encode([
                "error" => "Не удалось создать размерный ряд",
                "details" => $e->getMessage()
            ]);
        }
    }

    /**
     * Получение всех размерных рядов для определённой CategoryCharacteristic.
     *
     * @return void
     */
    public function getDimensionRangesByCategoryCharacteristic()
    {
        try {
            // Аутентификация пользователя
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            // Получение ID компании пользователя через CompanyModel
            $companyId = $this->companyModel->getUserCompanyId($userId);

            if (!$companyId) {
                throw new \Exception("Компания пользователя не найдена.");
            }

            // Получение параметра category_characteristic_id из GET-запроса
            if (!isset($_GET['category_characteristic_id'])) {
                throw new \InvalidArgumentException("Параметр 'category_characteristic_id' обязателен.");
            }

            $categoryCharacteristicId = (int)$_GET['category_characteristic_id'];

            // Проверка существования связи CategoryCharacteristic через CategoryModel
            $categoryCharacteristic = $this->categoryModel->getCategoryCharacteristicById($companyId, $categoryCharacteristicId);
            if (!$categoryCharacteristic) {
                throw new \InvalidArgumentException("Связь CategoryCharacteristic с ID {$categoryCharacteristicId} не найдена.");
            }

            // Получение размерных рядов через DimensionRangeModel
            $dimensionRanges = $this->dimensionRangeModel->getDimensionRangesByCharacteristic($categoryCharacteristicId);

            // Возврат успешного ответа
            http_response_code(200);
            echo json_encode([
                "dimension_ranges" => $dimensionRanges
            ]);
        } catch (\InvalidArgumentException $e) {
            // Обработка ошибок валидации
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            // Обработка общих ошибок
            http_response_code(500);
            echo json_encode([
                "error" => "Не удалось получить размерные ряды",
                "details" => $e->getMessage()
            ]);
        }
    }




    /**
     * Получение размерного ряда по его ID.
     *
     * @return void
     */
    public function getDimensionRangeById(): void
    {
        try {
            // Получение параметра id из GET-запроса
            if (!isset($_GET['id'])) {
                throw new \InvalidArgumentException("Параметр 'id' обязателен.");
            }

            $dimensionRangeId = (int)$_GET['id'];

            // Получение размерного ряда через DimensionRangeModel
            $dimensionRange = $this->dimensionRangeModel->getDimensionRangeById($dimensionRangeId);

            if (!$dimensionRange) {
                throw new \InvalidArgumentException("Размерный ряд с ID {$dimensionRangeId} не найден.");
            }

            // Возврат успешного ответа
            http_response_code(200);
            echo json_encode([
                "dimension_range" => $dimensionRange
            ]);
        } catch (\InvalidArgumentException $e) {
            // Обработка ошибок валидации
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            // Обработка общих ошибок
            http_response_code(500);
            echo json_encode([
                "error" => "Не удалось получить размерный ряд",
                "details" => $e->getMessage()
            ]);
        }
    }

    /**
     * Обновление существующего размерного ряда.
     *
     * @return void
     */
    public function updateDimensionRange(): void
    {
        try {
            // Аутентификация пользователя
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            // Получение ID компании пользователя через CompanyModel
            $companyId = $this->companyModel->getUserCompanyId($userId);

            if (!$companyId) {
                throw new \Exception("Компания пользователя не найдена.");
            }

            // Получение параметра id из PUT-запроса
            if (!isset($_GET['id'])) {
                throw new \InvalidArgumentException("Параметр 'id' обязателен.");
            }

            $dimensionRangeId = (int)$_GET['id'];

            // Получение существующего размерного ряда через DimensionRangeModel
            $existingDimensionRange = $this->dimensionRangeModel->getDimensionRangeById($dimensionRangeId);

            if (!$existingDimensionRange) {
                throw new \InvalidArgumentException("Размерный ряд с ID {$dimensionRangeId} не найден.");
            }

            // Получение и декодирование данных из входного запроса
            $data = json_decode(file_get_contents('php://input'), true);

            if (!is_array($data)) {
                throw new \InvalidArgumentException("Некорректный формат данных.");
            }

            // Валидация входных данных
            $this->validateUpdateDimensionRangeData($data);

            // Извлечение данных
            $name = isset($data['name']) ? trim($data['name']) : $existingDimensionRange['name'];
            $description = isset($data['description']) ? trim($data['description']) : $existingDimensionRange['description'];

            // Обновление размерного ряда через DimensionRangeModel
            $updatedDimensionRange = $this->dimensionRangeModel->editDimensionRange(
                $dimensionRangeId,
                $name,
                $description
            );

            // Возврат успешного ответа
            http_response_code(200);
            echo json_encode([
                "message" => "Размерный ряд обновлён успешно.",
                "dimension_range" => $updatedDimensionRange
            ]);
        } catch (\InvalidArgumentException $e) {
            // Обработка ошибок валидации
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            // Обработка общих ошибок
            http_response_code(500);
            echo json_encode([
                "error" => "Не удалось обновить размерный ряд",
                "details" => $e->getMessage()
            ]);
        }
    }

    /**
     * Удаление размерного ряда.
     *
     * @return void
     */
    public function deleteDimensionRange(): void
    {
        try {
            // Аутентификация пользователя
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            // Получение ID компании пользователя через CompanyModel
            $companyId = $this->companyModel->getUserCompanyId($userId);

            if (!$companyId) {
                throw new \Exception("Компания пользователя не найдена.");
            }

            // Получение параметра id из DELETE-запроса
            if (!isset($_GET['id'])) {
                throw new \InvalidArgumentException("Параметр 'id' обязателен.");
            }

            $dimensionRangeId = (int)$_GET['id'];

            // Проверка существования размерного ряда через DimensionRangeModel
            $existingDimensionRange = $this->dimensionRangeModel->getDimensionRangeById($dimensionRangeId);

            if (!$existingDimensionRange) {
                throw new \InvalidArgumentException("Размерный ряд с ID {$dimensionRangeId} не найден.");
            }

            // Удаление размерного ряда через DimensionRangeModel
            $this->dimensionRangeModel->deleteDimensionRange($dimensionRangeId);

            // Возврат успешного ответа
            http_response_code(200);
            echo json_encode([
                "message" => "Размерный ряд удалён успешно."
            ]);
        } catch (\InvalidArgumentException $e) {
            // Обработка ошибок валидации
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            // Обработка общих ошибок
            http_response_code(500);
            echo json_encode([
                "error" => "Не удалось удалить размерный ряд",
                "details" => $e->getMessage()
            ]);
        }
    }

    /**
     * Валидация данных для создания размерного ряда.
     *
     * @param array $data
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateCreateDimensionRangeData(array $data): void
    {
        $requiredFields = ['category_characteristic_id', 'name'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Поле '{$field}' обязательно.");
            }
        }

        if (!is_numeric($data['category_characteristic_id'])) {
            throw new \InvalidArgumentException("Поле 'category_characteristic_id' должно быть числом.");
        }

        if (!is_string($data['name']) || strlen(trim($data['name'])) === 0) {
            throw new \InvalidArgumentException("Поле 'name' должно быть непустой строкой.");
        }

        if (isset($data['description']) && !is_string($data['description'])) {
            throw new \InvalidArgumentException("Поле 'description' должно быть строкой.");
        }
    }

    /**
     * Валидация данных для обновления размерного ряда.
     *
     * @param array $data
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateUpdateDimensionRangeData(array $data): void
    {
        if (isset($data['name']) && (!is_string($data['name']) || strlen(trim($data['name'])) === 0)) {
            throw new \InvalidArgumentException("Поле 'name' должно быть непустой строкой.");
        }

        if (isset($data['description']) && !is_string($data['description'])) {
            throw new \InvalidArgumentException("Поле 'description' должно быть строкой.");
        }

        // Дополнительные проверки могут быть добавлены здесь
    }

    /**
     * Валидация данных для добавления значения размерного ряда.
     *
     * @param array $data
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateAddDimensionRangeValueData(array $data): void
    {
        $requiredFields = ['value'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Поле '{$field}' обязательно.");
            }
        }

        if (!is_string($data['value']) || strlen(trim($data['value'])) === 0) {
            throw new \InvalidArgumentException("Поле 'value' должно быть непустой строкой.");
        }

        if (isset($data['sort_order']) && !is_numeric($data['sort_order'])) {
            throw new \InvalidArgumentException("Поле 'sort_order' должно быть числом.");
        }
    }

    /**
     * Валидация данных для обновления значения размерного ряда.
     *
     * @param array $data
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateUpdateDimensionRangeValueData(array $data): void
    {
        if (isset($data['value']) && (!is_string($data['value']) || strlen(trim($data['value'])) === 0)) {
            throw new \InvalidArgumentException("Поле 'value' должно быть непустой строкой.");
        }

        if (isset($data['sort_order']) && !is_numeric($data['sort_order'])) {
            throw new \InvalidArgumentException("Поле 'sort_order' должно быть числом.");
        }

        // Дополнительные проверки могут быть добавлены здесь
    }

    // Дополнительные методы контроллера (например, для получения всех размерных рядов без характеристики) могут быть добавлены здесь
}
