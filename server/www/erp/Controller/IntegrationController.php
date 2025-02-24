<?php

namespace Controller;

use Model\IntegrationModel;
use Middleware\AuthMiddleware;
use Model\UserModel;

class IntegrationController
{
    private $integrationModel;
    private $userModel;
    private $authMiddleware; // Добавляем AuthMiddleware

    // Изменяем конструктор, добавляем AuthMiddleware
    public function __construct(IntegrationModel $integrationModel, UserModel $userModel, AuthMiddleware $authMiddleware)
    {
        $this->integrationModel = $integrationModel;
        $this->userModel = $userModel;
        $this->authMiddleware = $authMiddleware; // Инициализируем AuthMiddleware
    }


    // Получение всех интеграций для компании
    public function getIntegrations()
    {
        $userId = $this->userModel->getUserIdFromToken();

        // Получаем информацию о компании пользователя
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        if (!$companyInfo || !isset($companyInfo['details']['id'])) {
            http_response_code(403);
            echo json_encode(["error" => "Access denied. Company info not found for the user."]);
            return;
        }


        $companyId = $companyInfo['details']['id'];

        $integrations = $this->integrationModel->getIntegrationsByCompanyId($companyId);
        echo json_encode($integrations);
    }

    // Добавление интеграции
    public function addIntegration()
    {
        $userId = $this->userModel->getUserIdFromToken();

        // Получение данных из тела запроса
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData);

        if (!$data) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON"]);
            return;
        }

        // Получаем информацию о компании пользователя
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        if (!$companyInfo || !isset($companyInfo['details']['id'])) {
            http_response_code(403);
            echo json_encode(["error" => "Access denied. Company info not found for the user."]);
            return;
        }

        $companyId = $companyInfo['details']['id'];

        if (empty($data->integrationType) || empty($data->settings)) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields"]);
            return;
        }

        $integrationType = $data->integrationType;
        $settings = $data->settings; // Убедитесь, что $settings является объектом или массивом, а не строкой

        // Проверяем, существует ли уже интеграция данного типа для компании
        if ($this->integrationModel->isIntegrationTypeExists($companyId, $integrationType)) {
            http_response_code(400);
            echo json_encode(["error" => "Integration of this type already exists for the company."]);
            return;
        }

        // Добавляем интеграцию
        $this->integrationModel->addIntegration($companyId, $integrationType, $settings);
        echo json_encode(["message" => "Integration added successfully."]);
    }

    // Обновление интеграции
    public function updateIntegration($integrationId, $data)
    {
        $result = $this->integrationModel->updateIntegration($integrationId, $data->settings);
        if ($result) {
            echo json_encode(["message" => "Integration updated successfully"]);
        } else {
            echo json_encode(["error" => "Failed to update integration"]);
        }
    }

    // Удаление интеграции
    public function deleteIntegration($integrationId)
    {
        $result = $this->integrationModel->deleteIntegration($integrationId);
        if ($result) {
            echo json_encode(["message" => "Integration deleted successfully"]);
        } else {
            echo json_encode(["error" => "Failed to delete integration"]);
        }
    }
}
