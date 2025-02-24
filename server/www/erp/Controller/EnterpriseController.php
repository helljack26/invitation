<?php
namespace Controller;

use Model\EnterpriseModel;
use Middleware\AuthMiddleware;
use Model\UserModel;

class EnterpriseController
{
    private $enterpriseModel;
    private $userModel;
    private $authMiddleware; // Добавлен AuthMiddleware

    public function __construct(EnterpriseModel $enterpriseModel, UserModel $userModel, AuthMiddleware $authMiddleware)
    {
        $this->enterpriseModel = $enterpriseModel;
        $this->userModel = $userModel;
        $this->authMiddleware = $authMiddleware;
    }

    // Метод для получения деталей предприятия по ID
    public function getEnterpriseDetails($eGRPOUId)
    {
        $enterprise = $this->enterpriseModel->getEnterpriseById($eGRPOUId);
        if ($enterprise) {
            $enterprise['taxRate'] = $this->enterpriseModel->getEnterpriseTaxRate($eGRPOUId);
            http_response_code(200);
            echo json_encode($enterprise);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Enterprise not found']);
        }
    }
    // Получения всех предриятий пользвателя из Токен ИД
    public function getUserEnterprises()
    {
        try {
            // Получаем ID пользователя из токена
            $userId = $this->userModel->getUserIdFromToken();
            // Получаем список предприятий для этого пользователя

            $enterprises = $this->userModel->getUserEnterprises($userId);
            // Отправляем ответ
            http_response_code(200);
            echo json_encode($enterprises);
        } catch (Exception $e) {
            // В случае возникновения ошибки отправляем сообщение об ошибке
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred while retrieving enterprises: ' . $e->getMessage()]);
        }
    }

}