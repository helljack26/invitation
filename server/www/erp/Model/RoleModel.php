<?php
namespace Model;

use PDO;
use Service\CacheService;
use Exception;

/**
 * Класс RoleModel отвечает за управление ролями в системе.
 */
class RoleModel
{
    /**
     * Подключение к базе данных.
     *
     * @var PDO
     */
    protected $conn;

    /**
     * Сервис кеширования.
     *
     * @var CacheService
     */
    protected $cacheService;

    /**
     * Конструктор класса RoleModel.
     *
     * @param PDO $conn Подключение к базе данных.
     * @param CacheService $cacheService Сервис кеширования.
     * @throws Exception Если сервис кеширования не предоставлен.
     */
    public function __construct(PDO $conn, CacheService $cacheService)
    {
        $this->conn = $conn;
        $this->cacheService = $cacheService;

        if (!$cacheService) {
            throw new Exception("CacheService instance is not provided in RoleModel.");
        }
    }


/**
 * Назначение роли пользователю.
 *
 * @param int $currentUserId ID текущего пользователя.
 * @param int $targetUserId ID целевого пользователя.
 * @param int $roleId ID роли для назначения.
 * @return array Сообщение об успешном назначении.
 * @throws Exception Если роль не найдена.
 */
public function assignRoleToUser(int $currentUserId, int $targetUserId, int $roleId): array
{
    $updatedAtFormatted = date('Y-m-d H:i:s');

    $streamData = [
        'current_user_id' => (string)$currentUserId,
        'target_user_id' => (string)$targetUserId,
        'role_id' => (string)$roleId,
        'updated_at' => $updatedAtFormatted,
    ];

    // Записываем событие в Redis Stream через CacheService
    $this->cacheService->addToStream('role_assignment_stream', $streamData);

    // Обновляем кэш пользователя с назначенной ролью
    $this->cacheService->set("user_roles_{$targetUserId}", json_encode([$roleId]));

    // Проверяем наличие роли в кэше Redis через CacheService
    $roleKey = "role:id:{$roleId}";
    $cachedRole = $this->cacheService->get($roleKey);

    // Если роли нет в кэше, извлекаем её из MySQL и обновляем кэш
    if (!$cachedRole) {
        $stmt = $this->executeQuery(
            "SELECT * FROM Roles WHERE id = :roleId",
            [':roleId' => $roleId]
        );

        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$role) {
            throw new Exception("Role not found in MySQL for ID: $roleId");
        }

        // Обновляем кэш ролью, полученной из MySQL
        $this->cacheService->set($roleKey, json_encode($role));
    }

    return ['message' => 'Role assignment updated in Redis and queued for processing'];
}


   /**
 * Получение всех ролей компании с проверкой наличия данных в Redis.
 * Если данных нет, выводится сообщение "Restore data from MySQL".
 *
 * @param int $companyId ID компании.
 * @param int|null $userId ID пользователя (для проверки роли).
 * @param int $limit Количество ролей на странице.
 * @param int $offset Смещение для пагинации.
 * @return array Список ролей.
 * @throws Exception
 */
public function getRolesRedis(int $companyId, ?int $userId = null, int $limit = 50, int $offset = 0): array
{

    // Ключ списка ролей компании
    $rolesListKey = "company:{$companyId}:roles";

    // Получаем список ролей из Redis с учётом пагинации
    $roleIds = $this->cacheService->lRange($rolesListKey, $offset, $offset + $limit - 1);

    // Если ролей нет в Redis, выводим сообщение и возвращаем пустой массив
    if (empty($roleIds)) {
        echo "Restore data from MySQL";
        return [];
    }

    // Получаем данные ролей из Redis с использованием CacheService
    $cacheKeys = array_map(fn($roleId) => "role:id:{$roleId}", $roleIds);
    $cachedRolesData = $this->cacheService->mget($cacheKeys);

    // Декодируем данные ролей и формируем итоговый массив
    $roles = [];
    foreach ($roleIds as $index => $roleId) {
        $roleData = $cachedRolesData[$index] ?? null;
        if ($roleData) {
            $decodedRole = json_decode($roleData, true);
            if ($decodedRole) {
                // Декодируем permissions, если это строка
                if (!empty($decodedRole['permissions']) && is_string($decodedRole['permissions'])) {
                    $decodedRole['permissions'] = json_decode($decodedRole['permissions'], true);
                }

                // Добавляем роль в итоговый список
                $roles[] = [
                    'id' => $roleId,
                    'role_name' => $decodedRole['role_name'] ?? '',
                    'description' => $decodedRole['description'] ?? '',
                    'permissions' => $decodedRole['permissions'] ?? []
                ];
            }
        }
    }

    return $roles;
}

/**
 * Получение общего количества ролей компании.
 *
 * @param int $companyId ID компании.
 * @param int|null $userId ID пользователя.
 * @return int Общее количество ролей.
 * @throws Exception
 */
public function getTotalRolesCount(int $companyId, ?int $userId = null): int
{

    // Ключ списка ролей компании
    $roleIdsKey = "company:{$companyId}:roles";

    // Получаем общее количество ролей из Redis
    $totalRoles = $this->cacheService->lLen($roleIdsKey);

    // Если ролей нет в Redis, выводим сообщение и возвращаем 0
    if ($totalRoles === 0) {
        echo "Restore data from MySQL\n";
        return 0;
    }

    return $totalRoles;
}

    /**
     * Получение всех ролей компании из Redis.
     *
     * @param int $companyId ID компании.
     * @param int|null $userId ID пользователя.
     * @return array Список ролей.
     */
    public function getRolesFromRedis(int $companyId, ?int $userId = null): array
    {

        // Ключ списка ролей компании
        $roleIdsKey = "company:{$companyId}:roles";

        // Получаем список ролей из Redis
        $roleIds = $this->cacheService->lRange($roleIdsKey, 0, -1);

        // Если роли отсутствуют, возвращаем пустой массив
        if (empty($roleIds)) {
            return [];
        }

        // Получаем данные ролей из Redis
        $cacheKeys = array_map(fn($roleId) => "role:id:{$roleId}", $roleIds);
        $cachedRolesData = $this->cacheService->mget($cacheKeys);

        // Декодируем данные ролей
        $roles = [];
        foreach ($roleIds as $index => $roleId) {
            $roleData = $cachedRolesData[$index] ?? null;
            if ($roleData) {
                $decodedRole = json_decode($roleData, true);
                // Декодируем permissions, если это строка
                if (!empty($decodedRole['permissions']) && is_string($decodedRole['permissions'])) {
                    $decodedRole['permissions'] = json_decode($decodedRole['permissions'], true);
                }
                $roles[] = $decodedRole;
            }
        }
        return $roles;
    }

    /**
     * Получение деталей роли.
     *
     * @param int $roleId ID роли.
     * @return array|null Детали роли или null, если не найдена.
     */
    public function getRoleDetails(int $roleId): ?array
    {
        // Ключ для кэша в Redis
        $cacheKey = "role:id:{$roleId}";

        // Попытка получить данные из Redis через CacheService
        $cachedRole = $this->cacheService->get($cacheKey);
        if ($cachedRole) {
            $decodedRole = json_decode($cachedRole, true);
            // Декодируем permissions, если это строка
            if (!empty($decodedRole['permissions']) && is_string($decodedRole['permissions'])) {
                $decodedRole['permissions'] = json_decode($decodedRole['permissions'], true);
            }
            return $decodedRole;
        }

        // Если данные отсутствуют в Redis, выполняем запрос к MySQL
        $stmt = $this->executeQuery("SELECT * FROM Roles WHERE id = :roleId", [':roleId' => $roleId]);

        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$role) {
            return null;
        }

        // Преобразование разрешений в массив
        if (!empty($role['permissions'])) {
            $role['permissions'] = json_decode($role['permissions'], true);
        }

        // Сохранение данных в Redis через CacheService
        $this->cacheService->set($cacheKey, json_encode([
            'id' => $role['id'],
            'company_id' => $role['company_id'],
            'role_name' => $role['role_name'],
            'description' => $role['description'],
            'permissions' => json_encode($role['permissions']),
            'updated_at' => $role['updated_at'],
        ]));

        return $role;
    }

    /**
     * Назначение разрешений роли.
     *
     * @param int $roleId ID роли.
     * @param array $permissions Массив разрешений.
     * @return array Сообщение об успешном обновлении.
     * @throws Exception Если роль не найдена.
     */
    public function assignPermissionsToRole(int $roleId, array $permissions): array
    {
        $updatedAtFormatted = date('Y-m-d H:i:s');

        $streamData = [
            'role_id' => (string)$roleId,
            'permissions' => json_encode($permissions),
            'updated_at' => $updatedAtFormatted,
        ];

        // Добавляем событие обновления в Redis Stream через CacheService
        $this->cacheService->addToStream('role_update_stream', $streamData);

        // Ключ для кэша роли
        $cacheKey = "role:id:{$roleId}";
        $cachedRole = $this->cacheService->get($cacheKey);

        if ($cachedRole) {
            // Если роль есть в кэше, обновляем её данные
            $roleData = json_decode($cachedRole, true);
            $roleData['permissions'] = json_encode($permissions);
            $roleData['updated_at'] = $updatedAtFormatted;
            $this->cacheService->set($cacheKey, json_encode($roleData));
        } else {
            // Если роли нет в кэше, получаем её из MySQL
            $stmt = $this->executeQuery("SELECT * FROM Roles WHERE id = :roleId", [':roleId' => $roleId]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$role) {
                throw new Exception("Role not found in MySQL for ID: $roleId");
            }

            // Обновляем разрешения и время
            $role['permissions'] = $permissions;
            $role['updated_at'] = $updatedAtFormatted;

            // Сохраняем обновлённые данные в кэш через CacheService
            $this->cacheService->set($cacheKey, json_encode($role));
        }

        return ['message' => 'Permissions updated and queued for processing'];
    }

    /**
     * Получение всех разрешений с использованием Redis.
     *
     * @return array Список всех разрешений.
     * @throws Exception Если разрешения не найдены.
     */
    public function getAllPermissions(): array
    {
        $cacheKey = 'permissions:all';

        // Проверяем наличие разрешений в кэше Redis через CacheService
        $cachedPermissions = $this->cacheService->get($cacheKey);
        if ($cachedPermissions) {
            return json_decode($cachedPermissions, true);
        }

        // Если данных нет в Redis, извлекаем из базы данных MySQL
        $query = "SELECT permission_name, description FROM Permissions";
        $stmt = $this->executeQuery($query);
        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($permissions) {
            // Сохраняем полученные разрешения в кэш Redis на 1 час (3600 секунд)
            $this->cacheService->set($cacheKey, json_encode($permissions), 3600);
            return $permissions;
        }

        throw new Exception("No permissions found.");
    }

    /**
     * Создание новой роли с согласованным типом данных.
     *
     * @param int $companyId ID компании.
     * @param string $roleName Название роли.
     * @param string $description Описание роли.
     * @param array $permissions Массив разрешений.
     * @return array Информация о созданной роли.
     * @throws Exception Если операция Redis не удалась.
     */
    public function createRole(int $companyId, string $roleName, string $description, array $permissions): array
    {
        if (empty($roleName)) {
            throw new \InvalidArgumentException("Role name is required.");
        }

        $updatedAt = date('Y-m-d H:i:s');
        $permissionsJson = json_encode($permissions);

        // Генерация нового ID роли
        $roleId = $this->cacheService->incr("role:next_id");

        // Подготовка данных роли
        $roleData = [
            'id' => (string)$roleId,
            'company_id' => (string)$companyId,
            'role_name' => (string)$roleName,
            'description' => (string)$description,
            'permissions' => (string)$permissionsJson,
            'is_default' => '0',
            'updated_at' => (string)$updatedAt,
        ];

        $roleKey = "role:id:{$roleId}";
        $rolesListKey = "company:{$companyId}:roles";

        try {
            // Сначала через pipeline сохраняем роль и добавляем её в список ролей компании
            $this->cacheService->pipeline(function ($pipe, $client) use (
                $roleKey,
                $roleData,
                $rolesListKey,
                $roleId,
                $companyId
            ) {
                $roleClient = $this->cacheService->getClient($roleKey);
                if ($client === $roleClient) {
                    $pipe->set($roleKey, json_encode($roleData));
                }

                $listClient = $this->cacheService->getClient($rolesListKey);
                if ($client === $listClient) {
                    $pipe->rpush($rolesListKey, (string)$roleId);
                }
            });

            // После pipeline отдельно добавляем запись в стрим (вне pipeline)
            $this->cacheService->addToStream('role_creation_stream', $roleData);

        } catch (\Exception $e) {
            throw new \Exception("Redis operation failed: " . $e->getMessage());
        }

        return [
            'status' => 'success',
            'role_id' => (int)$roleId,
            'role_data' => $roleData,
        ];
    }


    /**
     * Тестовый метод для массового создания ролей с рандомными данными.
     *
     * @param int $companyId ID компании.
     * @param int $count Количество ролей для создания.
     * @return array Информация о выполнении операции.
     * @throws Exception
     */
    public function testBulkCreateRoles(int $companyId, int $count): array
    {
        if ($count <= 0) {
            throw new \InvalidArgumentException("Count must be a positive integer.");
        }

        $createdRolesCount = 0;
        $startTime = microtime(true);

        // Определение размера пакета
        $batchSize = 1000; // Можно настроить в зависимости от доступной памяти и требований
        $batches = ceil($count / $batchSize);

        for ($batch = 0; $batch < $batches; $batch++) {
            $currentBatchSize = min($batchSize, $count - ($batch * $batchSize));
            $rolesData = [];

            // Подготовка данных для текущего пакета
            for ($i = 0; $i < $currentBatchSize; $i++) {
                $rolesData[] = [
                    'role_name' => 'Role_' . bin2hex(random_bytes(4)),
                    'description' => 'Description for Role_' . bin2hex(random_bytes(4)),
                    'permissions' => $this->generateRandomPermissions()
                ];
            }

            try {
                // Начало Pipeline для INCR role:next_id
                $this->cacheService->pipeline(function ($pipe) use ($rolesData) {
                    foreach ($rolesData as $role) {
                        $pipe->incr("role:next_id");
                    }
                });
                $roleIds = $this->cacheService->exec();

                // Подготовка данных для MSET и RPUSH
                $roleDataKeyValuePairs = [];
                $rolesListKey = "company:{$companyId}:roles";
                $roleIdsToPush = [];

                foreach ($rolesData as $index => $role) {
                    $roleId = $roleIds[$index];
                    $roleKey = "role:id:{$roleId}";
                    $permissionsJson = json_encode($role['permissions']);
                    $updatedAt = date('Y-m-d H:i:s');

                    $roleDataKeyValuePairs[$roleKey] = json_encode([
                        'id' => $roleId,
                        'company_id' => $companyId,
                        'role_name' => $role['role_name'],
                        'description' => $role['description'],
                        'permissions' => $permissionsJson,
                        'is_default' => 0,
                        'updated_at' => $updatedAt
                    ]);

                    $roleIdsToPush[] = $roleId;
                }

                // Начало Pipeline для MSET и RPUSH
                $this->cacheService->pipeline(function ($pipe) use ($roleDataKeyValuePairs, $rolesListKey, $roleIdsToPush) {
                    if (!empty($roleDataKeyValuePairs)) {
                        $pipe->mset($roleDataKeyValuePairs);
                    }
                    if (!empty($roleIdsToPush)) {
                        $pipe->rPush($rolesListKey, ...$roleIdsToPush);
                    }
                });
                $this->cacheService->exec();

                // Начало Pipeline для XADD
                $this->cacheService->pipeline(function ($pipe) use ($rolesData, $roleIds, $companyId) {
                    foreach ($rolesData as $index => $role) {
                        $roleId = $roleIds[$index];
                        $permissionsJson = json_encode($role['permissions']);
                        $updatedAt = date('Y-m-d H:i:s');

                        $pipe->xAdd('role_creation_stream', '*', [
                            'role_id' => (string)$roleId,
                            'company_id' => (string)$companyId,
                            'role_name' => $role['role_name'],
                            'description' => $role['description'],
                            'permissions' => $permissionsJson,
                            'updated_at' => $updatedAt
                        ]);
                    }
                });
                $this->cacheService->exec();

                $createdRolesCount += $currentBatchSize;

                // Очистка массивов для освобождения памяти
                unset($rolesData, $roleDataKeyValuePairs, $roleIdsToPush, $roleIds);
                gc_collect_cycles(); // Принудительная сборка мусора
            } catch (\Exception $e) {
                // Логирование ошибки и продолжение
                error_log("Batch {$batch}: " . $e->getMessage());
                continue;
            }
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        return [
            'created_roles_count' => $createdRolesCount,
            'time_taken_seconds' => round($duration, 4)
        ];
    }

    /**
     * Генерация рандомных разрешений для роли.
     *
     * @return array Массив разрешений.
     */
    private function generateRandomPermissions(): array
    {
        $possiblePermissions = [
            'Видалення контенту', 
            'Отримання стандартних планів рахунків', 
            'Оновлення стандартних груп для рахунків', 
            'Створення контрагента', 
            'Редагувати товари', 
            'Управління налаштуваннями', 
            'Додавання стандартних груп для рахунків'
        ];

        // Генерируем случайное количество разрешений от 1 до 5
        $count = rand(1, 5);
        shuffle($possiblePermissions);

        return array_slice($possiblePermissions, 0, $count);
    }

    /**
     * Выполнение подготовленного запроса с параметрами.
     *
     * @param string $query SQL-запрос.
     * @param array $params Параметры для привязки.
     * @return \PDOStatement
     */
    protected function executeQuery(string $query, array $params = []): \PDOStatement
    {
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            // Проверяем, начинается ли параметр с двоеточия
            $param = strpos($key, ':') === 0 ? $key : ':' . $key;
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        return $stmt;
    }

    /**
     * Получение всех ролей по умолчанию.
     *
     * @return array Список ролей по умолчанию.
     */
    public function getAllRolesDefault(): array
    {
        $query = "SELECT * FROM Roles WHERE is_default = 1";
        $stmt = $this->executeQuery($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Получение роли по ID.
     *
     * @param int $roleId ID роли.
     * @return array|null Детали роли или null, если не найдена.
     */
    public function getRoleById(int $roleId): ?array
    {
        // Используем метод getRoleDetails для получения роли
        return $this->getRoleDetails($roleId);
    }

    /**
     * Удаление роли с проверкой на `global_admin`.
     *
     * @param int $roleId ID роли.
     * @return array Сообщение об успешном удалении.
     * @throws Exception Если роль не найдена или при ошибке транзакции.
     */
    public function deleteRole(int $roleId): array
    {
        $role = $this->getRoleById($roleId);

        if (!$role) {
            return ["error" => "Role not found."];
        }

        $this->conn->beginTransaction();

        try {
            // Удаляем все назначения роли пользователям
            $deleteUserRolesQuery = "DELETE FROM UserRoles WHERE role_id = :roleId";
            $this->executeQuery($deleteUserRolesQuery, [':roleId' => $roleId]);

            // Удаляем роль из MySQL
            $deleteRoleQuery = "DELETE FROM Roles WHERE id = :roleId";
            $this->executeQuery($deleteRoleQuery, [':roleId' => $roleId]);

            // Удаляем роль из Redis через CacheService
            $cacheKey = "role:id:{$roleId}";
            $this->cacheService->del($cacheKey);

            // Удаляем ID роли из списка компании
            $rolesListKey = "company:{$role['company_id']}:roles";
            $this->cacheService->lRem($rolesListKey, $roleId, 0);

            $this->conn->commit();
        } catch (\Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }

        return ["message" => "Role deleted successfully."];
    }

    /**
     * Получение всех ролей компании с пагинацией.
     *
     * @param int $companyId ID компании.
     * @param int $limit Количество ролей на страницу.
     * @param int $offset Смещение для пагинации.
     * @return array Список ролей.
     * @throws Exception
     */
    public function listRoles(int $companyId, int $limit = 50, int $offset = 0): array
    {
        return $this->getRolesRedis($companyId, null, $limit, $offset);
    }

    /**
     * Получение списка всех ролей.
     *
     * @return array Список всех ролей.
     * @throws Exception
     */
    public function listAllRoles(): array
    {
        $query = "SELECT * FROM Roles";
        $stmt = $this->executeQuery($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
