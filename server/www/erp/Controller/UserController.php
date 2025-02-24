<?php

namespace Controller;

use Model\UserModel;
use Error;
use finfo;

// Определение класса UserController, который наследуется от базового контроллера.
class UserController extends BaseController
{
    private $conn;
    private $redis;
    private $authMiddleware;
    private $userModel;

    public function __construct($conn, $redis, $authMiddleware, $userModel)
    {
        $this->conn = $conn;
        $this->redis = $redis;
        $this->authMiddleware = $authMiddleware;
        $this->userModel = $userModel;
    }
    /** 
     * "/user/list" Endpoint - Получение списка пользователей.
     */
    public function list()
    {
        // Инициализация переменной для хранения описания ошибок.
        $strErrorDesc = '';
        // Получение метода HTTP-запроса.
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        // Получение параметров строки запроса из URL.
        $arrQueryStringParams = $this->getQueryStringParams();
        // Проверка, является ли метод запроса GET.
        if (strtoupper($requestMethod) == 'GET') {
            try {
                // Создание экземпляра модели пользователя.
                $userModel = new UserModel($this->conn, $this->redis, $this->authMiddleware);

                // Установка предела количества пользователей для запроса по умолчанию.
                $intLimit = 10;
                // Если в параметрах запроса есть предел, устанавливаем его.
                if (isset($arrQueryStringParams['limit']) && $arrQueryStringParams['limit']) {
                    $intLimit = $arrQueryStringParams['limit'];
                }
                // Получение списка пользователей из модели.
                $arrUsers = $userModel->getUsers($intLimit);
                // Преобразование данных о пользователях в формат JSON.
                $responseData = json_encode($arrUsers);
            } catch (Error $e) { // Ловля исключений при возникновении ошибок.
                // Установка сообщения об ошибке и заголовка ошибки.
                $strErrorDesc = $e->getMessage() . ' Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            // Если метод запроса не GET, устанавливаем сообщение об ошибке и соответствующий заголовок.
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }
        // Отправка ответа.
        if (!$strErrorDesc) {
            // Если ошибок нет, отправляем данные пользователей и устанавливаем заголовок 200 OK.
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            // Если есть ошибка, отправляем сообщение об ошибке и устанавливаем соответствующий заголовок ошибки.
            $this->sendOutput(
                json_encode(array('error' => $strErrorDesc)),
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }
    // Получения ФИО... текущего пользвателя
    public function getCurrentUser()
    {
        // Инициализация переменной для хранения описания ошибок.
        $strErrorDesc = '';
        // Получение метода HTTP-запроса.
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        if (strtoupper($requestMethod) == 'POST') {

            try {
                // Создание экземпляра модели пользователя.
                $userModel = new UserModel($this->conn, $this->redis, $this->authMiddleware);
                // Извлечение ID пользователя из токена.
                $userId = $userModel->getUserIdFromToken();

                // Получение информации о текущем пользователе.
                $currentUser = $userModel->getCurrentUserInfo($userId);

                // Преобразование данных о текущем пользователе в формат JSON.
                $responseData = json_encode($currentUser);
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage() . ' Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }

        // Отправка ответа.
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(
                json_encode(array('error' => $strErrorDesc)),
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }
    public function updateSelfInfo($userId)
    {
        $inputData = json_decode(file_get_contents('php://input'), true);

        $inputData = $_POST['is_new_avatar'] == true ? $_POST : $inputData;

        $userName = $inputData['user_name'] ?? null;
        $firstName = $inputData['first_name'] ?? null;
        $secondName = $inputData['second_name'] ?? null;
        $lastName = $inputData['last_name'] ?? null;
        $email = $inputData['email'] ?? null;
        $existed_avatar_url = $inputData['existed_avatar_url'] ?? null;

        // Проверк  а и обработка загруженного файла (аватара)
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            // Проверка размера файла
            if ($_FILES['avatar']['size'] > 5000000) { // Размер файла не более 5 МБ
                http_response_code(400);
                echo json_encode(["error" => "File size exceeds the limit"]);
                return;
            }

            // Проверка MIME типа файла
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $fileType = $finfo->file($_FILES['avatar']['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid file type"]);
                return;
            }

            // Обработка загрузки файла
            $uploadDir = '/var/www/erp/uploads/users/avatar/'; // Путь к папке загрузки
            $fileName = md5(uniqid()) . '.' . pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadFile)) {
                // Если файл загружен успешно http//localhost или который будет указывать в энве на клиенте
                $avatarUrl = '/uploads/users/avatar/' . $fileName; // URL к аватару
            } else {
                http_response_code(500);
                echo json_encode(["error" => "An error occurred while uploading the avatar."]);
                return;
            }
        }

        $avatarUrl = isset($avatarUrl) ? $avatarUrl : $existed_avatar_url;

        // Обновление данных пользователя
        if ($this->userModel->updateUser($userId, $userName, $firstName, $secondName, $lastName, $email, $avatarUrl)) {
            http_response_code(200);
            echo json_encode(["message" => "User updated successfully."]);
            $this->userModel->clearUserBioCache($userId);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "An error occurred while updating the user."]);
        }
    }
    // Получиние данных о компании
    public function getCompanyInfo()
    {
        // Инициализация переменной для хранения описания ошибок
        $strErrorDesc = '';
        $strErrorHeader = '';
        // Получение метода HTTP-запроса
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if (strtoupper($requestMethod) == 'GET') {
            try {
                // Получение ID пользователя из токена
                $userId = $this->userModel->getUserIdFromToken();

                // Проверка наличия у пользователя разрешения manage_users
                if (!$this->userModel->hasPermission($userId, 'manage_users')) {
                    http_response_code(403);
                    echo json_encode(["error" => "Access denied. No permission to manage users."]);
                    return;
                }

                // Получение информации о компании пользователя
                $companyInfo = $this->userModel->getCompanyInfo($userId);

                // Преобразование данных о компании в формат JSON
                $responseData = json_encode($companyInfo);
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage() . ' Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }

        // Отправка ответа
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(
                json_encode(array('error' => $strErrorDesc)),
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }
    public function switchToUser($userId)
    {
        if ($this->userModel->isAdmin($userId)) {
            // Генерация временного токена для доступа от имени выбранного пользователя
            $temporaryToken = $this->authMiddleware->generateTemporaryToken($userId);

            if ($temporaryToken) {
                http_response_code(200); // OK
                echo json_encode(["temporary_token" => $temporaryToken]);
            } else {
                http_response_code(500); // Internal Server Error
                echo json_encode(["message" => "Error generating temporary token"]);
            }
        } else {
            http_response_code(403); // Forbidden
            echo json_encode(["message" => "Access denied"]);
        }
    }
}
