<?php

namespace Controller;

use Model\UserModel;
use Error;
use finfo;

class UserController extends BaseController
{
    private $authMiddleware;
    private $userModel;

    public function __construct($authMiddleware, UserModel $userModel)
    {
        $this->authMiddleware = $authMiddleware;
        $this->userModel = $userModel;
    }


    /**
     * Обновление информации о текущем пользователе.
     */
    public function updateSelfInfo($userId)
    {
        $inputData = json_decode(file_get_contents('php://input'), true);
        $inputData = (isset($_POST['is_new_avatar']) && $_POST['is_new_avatar'] == true) ? $_POST : $inputData;

        $userName = $inputData['user_name'] ?? null;
        $firstName = $inputData['first_name'] ?? null;
        $email = $inputData['email'] ?? null;
        $existed_avatar_url = $inputData['existed_avatar_url'] ?? null;

        // Обработка загруженного файла (аватара)
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['avatar']['size'] > 5000000) { // 5 МБ
                http_response_code(400);
                echo json_encode(["error" => "File size exceeds the limit"]);
                return;
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $fileType = $finfo->file($_FILES['avatar']['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid file type"]);
                return;
            }

            $uploadDir = '/var/www/erp/uploads/users/avatar/';
            $fileName = md5(uniqid()) . '.' . pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadFile)) {
                $avatarUrl = '/uploads/users/avatar/' . $fileName;
            } else {
                http_response_code(500);
                echo json_encode(["error" => "An error occurred while uploading the avatar."]);
                return;
            }
        }

        $avatarUrl = isset($avatarUrl) ? $avatarUrl : $existed_avatar_url;

        if ($this->userModel->updateUser($userId, $userName, $firstName,  $email, $avatarUrl)) {
            http_response_code(200);
            echo json_encode(["message" => "User updated successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "An error occurred while updating the user."]);
        }
    }
}
