<?php

namespace Controller;

use PDO;
use Model\Database;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Middleware\AuthMiddleware;

class AuthController
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $postData = json_decode($json);

            if (!isset($postData->username, $postData->email, $postData->password, $postData->first_name, $postData->second_name, $postData->last_name)) {
                http_response_code(400); // Bad Request
                echo json_encode(["message" => "Not all registration data was provided!"]);
                return;
            }

            $username = $postData->username;
            $email = $postData->email;
            $password = password_hash($postData->password, PASSWORD_DEFAULT);
            $first_name = $postData->first_name;
            $second_name = $postData->second_name;
            $last_name = $postData->last_name;

            $query = "SELECT * FROM Users WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                http_response_code(409); // Conflict
                echo json_encode(["message" => "User with this email already exists!"]);
                return;
            }

            $query = "INSERT INTO Users (username, email, password, first_name) VALUES (:username, :email, :password, :first_name)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':first_name', $first_name);

            if ($stmt->execute()) {
                http_response_code(201); // Created
                echo json_encode(["message" => "User successfully registered!"]);
            } else {
                http_response_code(500); // Internal Server Error
                echo json_encode(["message" => "Error occurred during registration!"]);
            }
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(["message" => "Invalid request method!"]);
        }
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $postData = json_decode($json);

            if (isset($postData->email, $postData->password)) {
                $email = $postData->email;
                $password = $postData->password;

                $query = "SELECT * FROM Users WHERE email = :email";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    // User exists, log them in
                    $jwt = $this->generateJWT($user['id']);
                    echo basename(__FILE__) . ' (Line ' . __LINE__ . ') - $jwt: ';
                    var_dump($jwt);
                    $this->setCookie($jwt);

                    http_response_code(200); // OK
                    echo json_encode([
                        "message" => "Successful login.",
                        "jwt" => $jwt
                    ]);
                    return;
                }
            }

            // If both email/password and Google login fail
            http_response_code(401); // Unauthorized
            echo json_encode(["message" => "Invalid email, password, or Google access token!"]);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(["message" => "Invalid request method!"]);
        }
    }

    // Placeholder for your existing JWT generation logic
    private function generateJWT($userId)
    {
        $key = getenv('API_SECRET');
        $FRONT_URL = getenv('FRONT_URL');
        echo basename(__FILE__) . ' (Line ' . __LINE__ . ') - $FRONT_URL: ';
        var_dump($FRONT_URL);
        $payload = [
            "iss" => "http://$FRONT_URL",
            "aud" => "http://$FRONT_URL",
            "iat" => time(),
            "exp" => time() + (60 * 60),
            "data" => [
                "userId" => $userId,
            ]
        ];

        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
    }
    // Placeholder for your existing cookie-setting logic
    private function setCookie($jwt)
    {
        $FRONT_URL = getenv('FRONT_URL');
        setcookie("token", $jwt, 0, "/", $FRONT_URL, false, true);
    }

    public function logout()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null; // Получаем заголовок авторизации

        if ($authHeader) {
            // Удаляем куки с JWT, устанавливая срок действия в прошлое
            if (isset($_COOKIE['token'])) {
                unset($_COOKIE['token']);
                setcookie('token', '', time() - 3600, '/'); // set the expiration date to one hour ago
                http_response_code(200); // OK
                echo json_encode(["message" => "Successfully logged out."]);
            } else {
                http_response_code(401);
                echo json_encode(["message" => "You are not logged in."]);
            }
        } else {
            http_response_code(405);
            echo json_encode(["message" => "Invalid request method."]);
        }
    }
    public function authenticate()
    {
        $authCheck = new AuthMiddleware();
        $authResult = $authCheck->authenticate();

        if (!is_array($authResult) || !isset($authResult['userId'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $authResult['userId'];
        $user = (object) [
            'userId' => $userId,
            'authenticated' => true,
        ];
        // print_r(); 
        $jsonData = json_encode($user);

        // Send the JSON data as the response
        header('Content-Type: application/json');

        echo ($jsonData);
    }
}
