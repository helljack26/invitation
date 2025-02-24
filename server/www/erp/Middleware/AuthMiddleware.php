<?php
namespace Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthMiddleware
{
    private $authService;

    public function authenticate()
    {
        return $this->authService->authenticate();  // Call authenticate() method from AuthService
    }
    
    public function __construct()
    {
        $this->authService = new AuthService(); // Инициализируем AuthService
    }

    /**
     * Мидлварь для аутентификации пользователя, проверка JWT токена.
     */
    public function handle()
    {
        $authResult = $this->authService->authenticate();

        if ($authResult == 'Not authenticated!') {
            // Если не аутентифицирован, возвращаем ошибку 401 Unauthorized
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error' => 'Not authenticated!']);
            exit;
        }

        if ($authResult == 'Invalid token!') {
            // Если токен недействителен, возвращаем ошибку 401 Unauthorized
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error' => 'Invalid token!']);
            exit;
        }

        // Добавляем данные пользователя в контекст запроса (если нужно)
        $_SESSION['user'] = $authResult;
    }

    /**
     * Опционально: проверяем, является ли пользователь администратором.
     */
    public function checkAdmin()
    {
        $userData = $_SESSION['user'] ?? null;
        if ($userData && $userData['isAdminSwitch']) {
            return true;
        }
        return false;
    }
}
