<?php

namespace Model;

use PDO;
use Middleware\AuthService;
use Service\CacheService;

/**
 * Предполагается, что класс Database:
 * - Предоставляет $this->conn для MySQL
 * - Имеет метод executeQuery() для выполнения запросов
 * - Имеет доступ к Redis, но мы его использовать не будем напрямую, положившись на CacheService.
 */
class UserModel extends Database
{
    private $authService;
    protected $cacheService;

    const ADMIN_ROLE_ID = 4;

    public function __construct($conn, $redis, AuthService $authService, CacheService $cacheService)
    {
        parent::__construct($conn, $redis);
        $this->authService = $authService;
        $this->cacheService = $cacheService;
    }

    /**
     * Возвращает массив ролей для набора пользователей. Ключ - userId, значение - массив id ролей.
     *
     * @param array $userIds
     * @param bool $fallbackToMySQL Если true - при отсутствии данных в кэше идём в MySQL.
     * @return array [userId => [roleId1, roleId2], ...]
     */
    public function getRolesForUsers(array $userIds, bool $fallbackToMySQL = true): array
    {
        $roles = [];
        $cacheKeys = array_map(fn($id) => "user_roles_{$id}", $userIds);

        // Получаем данные из Redis
        $cachedData = $this->cacheService->mget($cacheKeys);

        foreach ($userIds as $index => $userId) {
            $cached = $cachedData[$index] ?? null;

            if ($cached !== false && $cached !== null) {

                // Декодируем данные из Redis
                $decodedRoles = json_decode($cached, true);
                $roles[$userId] = $decodedRoles;
            } elseif ($fallbackToMySQL) {
                // Если данных в Redis нет, обращаемся к MySQL
                $mysqlRoles = $this->fetchUserRolesFromMySQL($userId);

                // Сохраняем данные в Redis, если они не пустые
                if (!empty($mysqlRoles)) {
                    // Преобразуем массив в JSON только если это массив
                    if (!is_string($mysqlRoles)) {
                        $mysqlRoles = json_encode($mysqlRoles, JSON_UNESCAPED_UNICODE);
                    }
                    $this->cacheService->set("user_roles_{$userId}", $mysqlRoles, 3600);
                }

                // Добавляем данные в массив результата
                $roles[$userId] = $mysqlRoles;
            } else {
                $roles[$userId] = [];
            }
        }

        return $roles;
    }



    /**
     * Проверка наличия у пользователя определённого разрешения.
     * Предполагаем, что:
     * - Если пользователь владелец компании, он имеет все права.
     * - Иначе получаем роли пользователя и проверяем наличие нужного права в ролях.
     */
    public function hasPermission(int $userId, string $permissionName, ?int $companyId = null): bool
    {
        if (is_null($companyId)) {
            $companyId = $this->getUserCompanyId($userId);
            if (!$companyId) {
                return false;
            }
        }

        if ($this->isOwnerOfCompany($userId, $companyId)) {
            return true; // Владельцы имеют все права
        }

        $roleIds = $this->getRolesForUsers([$userId], true)[$userId] ?? [];
        foreach ($roleIds as $roleId) {
            $permissions = $this->getRolePermissions($roleId);
            if (in_array($permissionName, $permissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Получить разрешения роли по её ID
     */
    private function getRolePermissions(int $roleId): array
    {
        $role = $this->getRoleDetails($roleId);
        if (!$role) {
            return [];
        }

        // Логируем исходное значение permissions
        error_log("Raw permissions: " . $role['permissions']);
        $permissions = $role['permissions'] ?? [];

        // Если permissions - строка, декодируем дважды
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true);
            error_log("After first decode: " . print_r($permissions, true));
            if (is_string($permissions)) {
                $permissions = json_decode($permissions, true);
                error_log("After second decode: " . print_r($permissions, true));
            }
        }

        error_log("Final permissions: " . print_r($permissions, true));
        return is_array($permissions) ? $permissions : [];
    }



    /**
     * Получить детали роли (fallback в MySQL если нет в кэше)
     */
    private function getRoleDetails(int $roleId): ?array
    {
        $cacheKey = "role:id:{$roleId}";
        $roleData = $this->cacheService->get($cacheKey);

        if ($roleData) {
            return json_decode($roleData, true);
        }

        // Из MySQL
        $query = "SELECT * FROM Roles WHERE id = :roleId LIMIT 1";
        $stmt = $this->executeQuery($query, [':roleId' => $roleId]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($role) {
            $this->cacheService->set($cacheKey, json_encode($role));
            return $role;
        }

        return null;
    }

    /**
     * Проверка, является ли пользователь владельцем компании.
     */
    public function isOwnerOfCompany(int $userId, int $companyId): bool
    {
        $query = "SELECT company_created_user_id
                  FROM Companies
                  WHERE company_created_user_id = :userId AND id = :companyId
                  LIMIT 1";
        $stmt = $this->executeQuery($query, [':userId' => $userId, ':companyId' => $companyId]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        return (bool)$company;
    }

    /**
     * Проверка, является ли пользователь администратором.
     * Предполагается, что getRolesForUsers вернёт массив ролей-идентификаторов.
     */
    public function isAdmin(int $userId): bool
    {
        $roles = $this->getRolesForUsers([$userId]);
        $userRoles = $roles[$userId] ?? [];

        return in_array(self::ADMIN_ROLE_ID, $userRoles, true);
    }

    /**
     * Получить userId из токена
     */
    public function getUserIdFromToken(bool $isAdminSwitch = false): ?int
    {
        return $isAdminSwitch
            ? $this->authService->getAdminUserId()
            : $this->authService->getAuthenticatedUserId();
    }

    /**
     * Получить информацию о текущем пользователе.
     * Если userId не передан, берём из токена.
     */
    public function getCurrentUserInfo(?int $userId = null, bool $isAdminSwitch = false): ?array
    {
        $userId = $userId ?? $this->getUserIdFromToken($isAdminSwitch);

        if (!$userId) {
            return null;
        }

        $cacheKey = "user_info_{$userId}";
        $cached = $this->cacheService->get($cacheKey);

        if ($cached) {
            return json_decode($cached, true);
        }

        $query = "SELECT id, username, email, first_name, second_name, last_name, created_at, avatar_url, phone_number
                  FROM Users
                  WHERE id = :userId";
        $stmt = $this->executeQuery($query, [':userId' => $userId]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userInfo) {
            $this->cacheService->set($cacheKey, json_encode($userInfo));
        }

        return $userInfo ?: null;
    }

    /**
     * Обновление информации пользователя
     */
    public function updateUser(int $userId, string $userName, string $firstName, string $secondName, string $lastName, string $email, string $avatarUrl): bool
    {
        $query = "UPDATE Users
                  SET username = :userName, 
                      first_name = :firstName, 
                      second_name = :secondName, 
                      last_name = :lastName, 
                      email = :email, 
                      avatar_url = :avatarUrl
                  WHERE id = :userId";
        $params = [
            ':userName' => $userName,
            ':firstName' => $firstName,
            ':secondName' => $secondName,
            ':lastName' => $lastName,
            ':email' => $email,
            ':avatarUrl' => $avatarUrl,
            ':userId' => $userId
        ];
        return $this->executeQuery($query, $params) !== false;
    }

    /**
     * Создание нового пользователя
     */
    public function createUser(int $userId, string $userName, string $firstName, string $secondName, string $lastName, string $email, string $avatarUrl, string $phoneNumber): bool
    {
        $query = "INSERT INTO Users (id, username, first_name, second_name, last_name, email, avatar_url, phone_number)
                  VALUES (:userId, :userName, :firstName, :secondName, :lastName, :email, :avatarUrl, :phoneNumber)";
        $params = [
            ':userId' => $userId,
            ':userName' => $userName,
            ':firstName' => $firstName,
            ':secondName' => $secondName,
            ':lastName' => $lastName,
            ':email' => $email,
            ':avatarUrl' => $avatarUrl,
            ':phoneNumber' => $phoneNumber
        ];
        return $this->executeQuery($query, $params) !== false;
    }

    /**
     * Очистка кэша роли пользователя
     */
    public function clearUserRoleCache(int $userId): void
    {
        $this->cacheService->del("user_roles_{$userId}");
    }

    /**
     * Очистка кэша компании пользователя
     */
    public function clearUserCompanyCache(int $userId): void
    {
        $this->cacheService->del("user_company_{$userId}");
    }

    /**
     * Очистка кэша информации о пользователе
     */
    public function clearUserBioCache(int $userId): void
    {
        $this->cacheService->del("user_info_{$userId}");
    }

    /**
     * Получаем ID компании пользователя из MySQL (если нет кэша или др. источника)
     */
    private function getUserCompanyId(int $userId): ?int
    {
        // Можно кэшировать результат, аналогично другим данным
        $query = "SELECT company_id FROM UserCompanies WHERE user_id = :userId LIMIT 1";
        $stmt = $this->executeQuery($query, [':userId' => $userId]);
        $userCompany = $stmt->fetch(PDO::FETCH_ASSOC);
        return $userCompany['company_id'] ?? null;
    }


    /**
     * Получить роли пользователя из MySQL
     */
    private function fetchUserRolesFromMySQL(int $userId): array
    {
        $query = "SELECT role_id FROM UserRoles WHERE user_id = :userId";
        $stmt = $this->executeQuery($query, [':userId' => $userId]);
        $mysqlRoles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Преобразуем каждое role_id в целое число
        $mysqlRoles = array_map('intval', $mysqlRoles);
        return $mysqlRoles ?: [];
    }
}
