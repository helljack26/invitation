<?php

namespace Controller;

use Model\WarehouseModel;
use Model\CompanyModel;
use Model\UserModel;
use Middleware\AuthMiddleware;
use Exception;

class WarehouseController
{
    private WarehouseModel $warehouseModel;
    private UserModel $userModel;
    private CompanyModel $companyModel;
    private AuthMiddleware $authMiddleware;

    public function __construct(WarehouseModel $warehouseModel, UserModel $userModel, CompanyModel $companyModel, AuthMiddleware $authMiddleware)
    {
        $this->warehouseModel = $warehouseModel;
        $this->userModel = $userModel;
        $this->companyModel = $companyModel;
        $this->authMiddleware = $authMiddleware;
    }

    /**
     * Добавление нового склада
     */
    public function createWarehouse(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);
            $this->validateWarehouseData($data);

            $warehouseData = [
                'company_id' => $companyId,
                'name' => $data['name'] ?? '',
                'location' => $data['location'] ?? '',
                'capacity' => $data['capacity'] ?? 0
            ];

            $result = $this->warehouseModel->createWarehouse($companyId, $warehouseData);

            http_response_code(201);
            echo json_encode([
                "message" => "Склад создан успешно",
                "warehouse_id" => $result['id']
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось создать склад", "details" => $e->getMessage()]);
        }
    }

    /**
     * Редактирование склада
     */
    public function editWarehouse(): void
    {
        try {

            $data = json_decode(file_get_contents('php://input'), true);
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);
            $this->validateEditWarehouseData($data);

            $warehouseData = array_filter([
                'name' => $data['name'] ?? null,
                'location' => $data['location'] ?? null,
                'capacity' => $data['capacity'] ?? null
            ], fn($value) => !is_null($value));

            $result = $this->warehouseModel->editWarehouse(intval($companyId), intval($data['warehouse_id']), $warehouseData);

            http_response_code(200);
            echo json_encode([
                "message" => "Склад обновлён успешно",
                "details" => $result
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось обновить склад", "details" => $e->getMessage()]);
        }
    }

    /**
     * Удаление склада
     */
    public function deleteWarehouse(): void
    {
        try {

            $data = json_decode(file_get_contents('php://input'), true);
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);

            if (empty($data['warehouse_id'])) {
                throw new \InvalidArgumentException("ID склада обязателен.");
            }

            $result = $this->warehouseModel->deleteWarehouse($companyId, $data['warehouse_id']);

            http_response_code(200);
            echo json_encode([
                "message" => "Склад удалён успешно",
                "details" => $result
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось удалить склад", "details" => $e->getMessage()]);
        }
    }

    /**
     * Получение списка складов
     */
    public function listWarehouses(): void
    {
        try {
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);


            // Проверка прав на назначение роли, включая проверку владельца компании
            if (!$this->userModel->hasPermission($userId, 'Змінити роль користувача', $companyId)) {
                http_response_code(403);
                echo json_encode(["error" => "Permission denied."]);
                return;
            }


            $warehouses = $this->warehouseModel->getWarehousesByCompany($companyId);

            http_response_code(200);
            echo json_encode([
                "warehouses" => $warehouses
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось получить список складов", "details" => $e->getMessage()]);
        }
    }

    /**
     * Получение склада по ID
     */
    public function getWarehouseById(): void
    {
        try {
            $inputData = json_decode(file_get_contents("php://input"), true);

            if (!isset($inputData['id'])) {
                throw new \InvalidArgumentException("Параметр 'id' обязателен.");
            }

            $warehouseId = intval($inputData['id']);
            if ($warehouseId <= 0) {
                throw new \InvalidArgumentException("Некорректный ID склада.");
            }

            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);
            $warehouse = $this->warehouseModel->getWarehouseById($companyId, $warehouseId);

            if (!$warehouse) {
                throw new \Exception("Склад не найден.");
            }

            http_response_code(200);
            echo json_encode(["warehouse" => $warehouse]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось получить склад", "details" => $e->getMessage()]);
        }
    }

    /**
     * Валидация данных для создания склада
     */
    private function validateWarehouseData(array $data): void
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException("Название склада обязательно.");
        }
    }

    /**
     * Валидация данных для редактирования склада
     */
    private function validateEditWarehouseData(array $data): void
    {
        if (empty($data['warehouse_id'])) {
            throw new \InvalidArgumentException("ID склада обязателен.");
        }
    }
}
