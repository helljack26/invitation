<?php

namespace Router;

// Используйте автозагрузку Composer для загрузки всех классов
// require_once __DIR__ . '/../vendor/autoload.php';

// Импорт необходимых классов
use Model\Database;
use Middleware\AuthMiddleware;
use Middleware\AuthService;
use Controller\AuthController;
use Controller\GuestController;

// Создаём экземпляр Database, который автоматически инициализирует соединения с MySQL и Redis
$database = new Database();

// Получаем PDO соединение и сервис кеширования из экземпляра Database
$conn = $database->getConnection();
$cacheService = $database->getCacheService();
$imageService = $database->getImageService();

$fileServerManager  = new \Service\FileService\FileServerManager($conn);

// Создаём экземпляры промежуточных слоёв и сервисов
$authMiddleware = new AuthMiddleware();
$authService = new AuthService();


// Создаём экземпляры моделей, передавая необходимые зависимости
$guestModel = new \Model\GuestModel($conn);

// Создаём экземпляры контроллеров, передавая необходимые зависимости
$authController = new AuthController();

$guestController = new GuestController($guestModel);
// Определение маршрутов
$routes = [
    // Аутентификация
    '/api/auth/login' => [$authController, 'login'],
    '/api/auth/logout' => [$authController, 'logout'],
    '/api/auth/register' => [$authController, 'register'],
    '/api/auth/authenticate' => [$authController, 'authenticate'],

    // Guest routes
    '/api/guest/createGuest'   => [$guestController, 'createGuest', 'auth' => false],
    '/api/guest/updateGuest'   => [$guestController, 'updateGuest', 'auth' => false],
    '/api/guest/listGuests'    => [$guestController, 'listGuests', 'auth' => false],
    '/api/guest/getGuestById'  => [$guestController, 'getGuestById', 'auth' => false],
    '/api/guest/getGuestByUniquePath'  => [$guestController, 'getGuestByUniquePath', 'auth' => false],
    '/api/guest/deleteGuest'   => [$guestController, 'deleteGuest', 'auth' => false],
    '/api/guest/updateGuestDataByUser'   => [$guestController, 'updateGuestDataByUser', 'auth' => false],
];

// Обработка предзапросов (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Функция для обработки входящих запросов и маршрутизации их к соответствующим контроллерам.
 *
 * @param array $routes Массив маршрутов.
 * @param AuthMiddleware $authMiddleware Экземпляр класса AuthMiddleware.

 */
function handleRequest($routes, $authMiddleware)
{
    // Получаем текущий URI
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $queryParams = $_GET; // Получение GET-параметров

    if (array_key_exists($uri, $routes)) {
        $route = $routes[$uri];

        $userId = null;

        // Проверка авторизации, если это требуется маршрутом
        if (isset($route['auth']) && $route['auth']) {
            $authResult = $authMiddleware->authenticate();
            if (!is_array($authResult) || !isset($authResult['userId'])) {
                header("HTTP/1.1 401 Unauthorized");
                echo json_encode(["error" => "Unauthorized"]);
                exit();
            }
            $userId = $authResult['userId'];  // Получаем ID пользователя из декодированного JWT
        }

        // Если это POST-запрос, получаем данные из тела запроса
        $data = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
        }

        // Вызов метода контроллера
        if (isset($route)) {
            if (is_callable([$route[0], $route[1]])) {
                if ($data) {
                    // Если есть данные (POST-запрос с телом), передаём их
                    call_user_func([$route[0], $route[1]], $userId, $data);
                } else {
                    // Если нет данных, используем `queryParams` для GET-запросов
                    call_user_func([$route[0], $route[1]], $userId, $queryParams);
                }
            } else {
                header("HTTP/1.1 500 Internal Server Error");
                echo json_encode(["error" => "Controller method not callable"]);
                exit();
            }
        } else {
            header("HTTP/1.1 404 Not Found");
            echo json_encode(["error" => "Not Found"]);
            exit();
        }
    } else {
        header("HTTP/1.1 404 Not Found");
        echo json_encode(["error" => "Not Found"]);
        exit();
    }
}

// Вызов функции обработки запроса с передачей необходимых зависимостей
handleRequest($routes, $authMiddleware);
