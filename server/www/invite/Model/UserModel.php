<?php

namespace Model;

use PDO;
use Middleware\AuthService;

/**
 * Предполагается, что класс Database:
 * - Предоставляет $this->conn для MySQL
 * - Имеет метод executeQuery() для выполнения запросов
 */
class UserModel extends Database
{
    private $authService;

    const ADMIN_ROLE_ID = 4;

    public function __construct($conn, AuthService $authService)
    {
        parent::__construct($conn);
        $this->authService = $authService;
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
     * Обновление информации пользователя
     */
    public function updateUser(int $userId, string $userName, string $firstName, string $email, string $avatarUrl): bool
    {
        $query = "UPDATE Users
                  SET username = :userName, 
                      first_name = :firstName, 
                      email = :email, 
                      avatar_url = :avatarUrl
                  WHERE id = :userId";
        $params = [
            ':userName' => $userName,
            ':firstName' => $firstName,
            ':email' => $email,
            ':avatarUrl' => $avatarUrl,
            ':userId' => $userId
        ];
        return $this->executeQuery($query, $params) !== false;
    }

    /**
     * Создание нового пользователя
     */
    public function createUser(int $userId, string $userName, string $firstName, string $email, string $avatarUrl, string $phoneNumber): bool
    {
        $query = "INSERT INTO Users (id, username, first_name, email, avatar_url, phone_number)
                  VALUES (:userId, :userName, :firstName, :email, :avatarUrl, :phoneNumber)";
        $params = [
            ':userId' => $userId,
            ':userName' => $userName,
            ':firstName' => $firstName,
            ':email' => $email,
            ':avatarUrl' => $avatarUrl,
            ':phoneNumber' => $phoneNumber
        ];
        return $this->executeQuery($query, $params) !== false;
    }
}
