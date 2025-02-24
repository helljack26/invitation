<?php

namespace Controller;

use Model\RoleModel;
use Model\UserModel;
use Model\CompanyModel;
use Middleware\AuthMiddleware;

class RoleController
{
    private RoleModel $roleModel;
    private UserModel $userModel;
    private CompanyModel $companyModel;
    private AuthMiddleware $authMiddleware;

    public function __construct(RoleModel $roleModel, UserModel $userModel, CompanyModel $companyModel, AuthMiddleware $authMiddleware)
    {
        $this->roleModel = $roleModel;
        $this->userModel = $userModel;
        $this->companyModel = $companyModel;
        $this->authMiddleware = $authMiddleware;
    }

    /**
     * Создание новой роли
     */
    public function createRole(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Authentication failed.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Валидация данных
            $this->validateCreateRoleData($data);

            // Создаём роль через модель
            $result = $this->roleModel->createRole(
                $companyId,
                $data['role_name'],
                $data['description'],
                $data['permissions']
            );

            http_response_code(201);
            echo json_encode([
                "message" => "Role created successfully",
                "role_id" => $result['role_id']
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to create role", "details" => $e->getMessage()]);
        }
    }

    /**
     * Валидация данных для создания роли
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    private function validateCreateRoleData(array $data): void
    {
        if (empty($data['role_name'])) {
            throw new \InvalidArgumentException("Role name is required.");
        }

        if (empty($data['description'])) {
            throw new \InvalidArgumentException("Role description is required.");
        }

        if (empty($data['permissions']) || !is_array($data['permissions'])) {
            throw new \InvalidArgumentException("At least one permission is required.");
        }
    }

    /**
     * Получение всех ролей компании с автоматическим восстановлением данных в Redis при необходимости.
     */

    public function listRoles(): void
    {
        try {
            $startTime = microtime(true); // Начало измерения времени

            // Получаем параметры пагинации из GET-запроса
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 50;

            // Валидация параметров пагинации
            if ($page < 1) {
                throw new \InvalidArgumentException("Параметр 'page' должен быть положительным целым числом.");
            }

            if ($perPage < 1 || $perPage > 1000) { // Максимальное значение per_page ограничено 1000
                throw new \InvalidArgumentException("Параметр 'per_page' должен быть между 1 и 1000.");
            }

            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Вычисляем limit и offset
            $limit = $perPage;
            $offset = ($page - 1) * $limit;

            // Получаем текущий пакет ролей
            $rolesBatch = $this->roleModel->getRolesRedis($companyId, $userId, $limit, $offset);

            // Получаем общее количество ролей для компании
            $totalRoles = $this->roleModel->getTotalRolesCount($companyId, $userId);

            // Вычисляем общее количество страниц
            $totalPages = ceil($totalRoles / $perPage);

            $endTime = microtime(true); // Конец измерения времени
            $timeTaken = round(($endTime - $startTime) * 1000, 2); // Время выполнения в миллисекундах

            http_response_code(200);
            echo json_encode([
                "roles" => array_values($rolesBatch),
                "pagination" => [
                    "current_page" => $page,
                    "per_page" => $perPage,
                    "total_roles" => $totalRoles,
                    "total_pages" => $totalPages
                ],
                "time_taken_ms" => $timeTaken
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }


    /**
     * Назначение роли пользователю
     */
    public function assignRoleToUser(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $authData = $this->authMiddleware->authenticate();
            $currentUserId = $authData['userId'] ?? null;

            if (!$currentUserId) {
                throw new \Exception("Authentication failed.");
            }

            $targetUserId = $data['target_user_id'] ?? null;
            $roleId = $data['role_id'] ?? null;

            if (!$targetUserId || !$roleId) {
                throw new \InvalidArgumentException("User ID and Role ID are required.");
            }

            // Получаем компанию текущего пользователя
            $currentCompanyId = $this->companyModel->getUserCompanyId($currentUserId);
            $targetCompanyId = $this->companyModel->getUserCompanyId($targetUserId);

            // Проверка на принадлежность пользователей одной компании
            if ($currentCompanyId !== $targetCompanyId) {
                http_response_code(403);
                echo json_encode(["error" => "Access denied. Users are not from the same company."]);
                return;
            }

            // Проверка прав на назначение роли, включая проверку владельца компании
            if (!$this->userModel->hasPermission($currentUserId, 'Змінити роль користувача', $currentCompanyId)) {
                http_response_code(403);
                echo json_encode(["error" => "Permission denied."]);
                return;
            }

            // Проверяем, принадлежит ли роль компании текущего пользователя
            // $role = $this->roleModel->getRoleById($roleId);
            // if (!$role || $role['company_id'] != $currentCompanyId) {
            //     http_response_code(403);
            //     echo json_encode(["error" => "Access denied. Role does not belong to your company."]);
            //     return;
            // }


            // Назначение роли через модель
            $result = $this->roleModel->assignRoleToUser($currentUserId, $targetUserId, $roleId);
            echo $result;
            http_response_code(200);
            echo json_encode(["message" => "Role assigned successfully", "details" => $result]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to assign role", "details" => $e->getMessage()]);
        }
    }

    /**
     * Удаление роли
     */
    public function deleteRole(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $roleId = isset($data['role_id']) ? (int)$data['role_id'] : null;

            if (!$roleId) {
                throw new \InvalidArgumentException("Role ID is required.");
            }

            $result = $this->roleModel->deleteRole($roleId);

            if (isset($result['error'])) {
                http_response_code(400);
                echo json_encode($result);
            } else {
                http_response_code(200);
                echo json_encode($result);
            }
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to delete role", "details" => $e->getMessage()]);
        }
    }

    /**
     * Назначение разрешений роли
     */
    public function assignPermissionsToRole(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $roleId = isset($data['role_id']) ? (int)$data['role_id'] : null;
            $permissions = $data['permissions'] ?? [];

            if (!$roleId || empty($permissions) || !is_array($permissions)) {
                throw new \InvalidArgumentException("Role ID and permissions are required and must be valid.");
            }

            // Проверяем, существует ли роль с данным ID
            $role = $this->roleModel->getRoleById($roleId);
            if (!$role) {
                http_response_code(404);
                echo json_encode(["error" => "Role not found."]);
                return;
            }

            // Назначаем разрешения через модель
            $result = $this->roleModel->assignPermissionsToRole($roleId, $permissions);

            http_response_code(200);
            echo json_encode(["message" => "Permissions assigned successfully.", "details" => $result]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to assign permissions.", "details" => $e->getMessage()]);
        }
    }

    /**
     * Получение всех ролей по умолчанию
     */
    public function getAllRolesDefault(): void
    {
        try {
            $roles = $this->roleModel->getAllRolesDefault();
            http_response_code(200);
            echo json_encode(["roles" => $roles]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to retrieve default roles.", "details" => $e->getMessage()]);
        }
    }

    /**
     * Получение всех доступных разрешений
     */
    public function getAllPermissions(): void
    {
        try {
            $permissions = $this->roleModel->getAllPermissions();
            http_response_code(200);
            echo json_encode(["permissions" => $permissions]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to retrieve permissions.", "details" => $e->getMessage()]);
        }
    }

    /**
     * Получение роли пользователя
     */
    public function getUserRole(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(["error" => "Invalid request method"]);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $userId = isset($data['userId']) ? (int)$data['userId'] : null;

            if (!$userId) {
                throw new \InvalidArgumentException("User ID is required.");
            }

            $authData = $this->authMiddleware->authenticate();
            $currentUserId = $authData['userId'] ?? null;
            $companyId = isset($data['companyId']) ? (int)$data['companyId'] : null;

            if (!$currentUserId) {
                throw new \Exception("Authentication failed.");
            }

            // Проверка прав на получение роли, включая проверку владельца компании
            if (!$this->userModel->hasPermission($currentUserId, 'Отримання ролі користувача', $companyId)) {
                http_response_code(403);
                echo json_encode(["error" => "Permission denied."]);
                return;
            }

            // Получение ролей для указанного пользователя
            $roles = $this->userModel->getRolesForUsers([$userId]);
            $role = $roles[$userId] ?? [];

            http_response_code(200);
            echo json_encode(["roles" => $role]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to retrieve user roles.", "details" => $e->getMessage()]);
        }
    }

    /**
     * Получение деталей роли
     */
    public function getRoleDetails(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $roleId = isset($data['role_id']) ? (int)$data['role_id'] : null;

            if (!$roleId) {
                throw new \InvalidArgumentException("Role ID is required.");
            }

            $authData = $this->authMiddleware->authenticate();
            $currentUserId = $authData['userId'] ?? null;
            $companyId = isset($data['companyId']) ? (int)$data['companyId'] : null;

            if (!$currentUserId) {
                throw new \Exception("Authentication failed.");
            }

            // Проверка прав на получение роли, включая проверку владельца компании
            if (!$this->userModel->hasPermission($currentUserId, 'Отримання ролі користувача', $companyId)) {
                http_response_code(403);
                echo json_encode(["error" => "Permission denied."]);
                return;
            }

            $role = $this->roleModel->getRoleDetails($roleId);

            if ($role) {
                http_response_code(200);
                echo json_encode($role);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Role not found."]);
            }
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to retrieve role details.", "details" => $e->getMessage()]);
        }
    }

    /**
     * Тестовый метод для массового создания ролей.
     * Эндпоинт: POST /api/role/test-bulk-create-roles
     */
    public function testBulkCreateRoles(): void
    {
        try {
            // Аутентификация пользователя
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Authentication failed.");
            }

            // Получение ID компании текущего пользователя
            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Получение данных из запроса
            $data = json_decode(file_get_contents('php://input'), true);
            $count = isset($data['count']) ? (int)$data['count'] : 0;

            if ($count <= 0) {
                throw new \InvalidArgumentException("Count must be a positive integer.");
            }

            // Ограничение максимального количества для предотвращения злоупотреблений
            $maxAllowed = 1000000000000; // Увеличен до 10000
            if ($count > $maxAllowed) {
                throw new \InvalidArgumentException("Count cannot exceed {$maxAllowed}.");
            }

            // Валидация прав пользователя (например, только администратор может запускать этот тест)
            if (!$this->userModel->hasPermission($userId, 'bulk_create_roles', $companyId)) {
                http_response_code(403);
                echo json_encode(["error" => "Permission denied."]);
                return;
            }

            // Вызов метода модели для массового создания ролей
            $result = $this->roleModel->testBulkCreateRoles($companyId, $count);

            http_response_code(200);
            echo json_encode([
                "roles_created" => $result['created_roles_count'],
                "time_taken_seconds" => $result['time_taken_seconds']
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to bulk create roles.", "details" => $e->getMessage()]);
        }
    }
}
