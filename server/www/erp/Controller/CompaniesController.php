<?php

namespace Controller;

use Model\CompanyModel;
use Middleware\AuthMiddleware;
use Model\UserModel;
use Model\RoleModel;

class CompaniesController extends BaseController
{
    private $companyModel;
    private $authMiddleware;
    private $userModel;
    private $roleModel;

    /**
     * Конструктор класса CompaniesController.
     *
     * @param CompanyModel $companyModel - Модель для работы с компаниями.
     * @param AuthMiddleware $authMiddleware - Middleware для проверки авторизации и прав доступа.
     * @param UserModel $userModel - Модель для работы с пользователями.
     * @param RoleModel $roleModel - Модель для работы с ролями.
     */
    public function __construct(CompanyModel $companyModel, AuthMiddleware $authMiddleware, UserModel $userModel, RoleModel $roleModel)
    {
        $this->companyModel = $companyModel;
        $this->authMiddleware = $authMiddleware;
        $this->userModel = $userModel;
        $this->roleModel = $roleModel;
    }


    /**
     * Метод для создания новой компании.
     */
    public function createCompany()
    {
        $userId = $this->userModel->getUserIdFromToken();

        // Проверка прав на создание компании
        if (!$this->userModel->hasPermission($userId, 'create_company')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $companyName = $_POST['company_name'] ?? null;
        $address = $_POST['address'] ?? null;

        // Проверка наличия обязательных полей
        if (empty($companyName) || empty($address)) {
            http_response_code(400);
            echo json_encode(["error" => "Company name and address are required."]);
            return;
        }

        // Проверка, если у пользователя уже есть компания
        if ($this->companyModel->getUserCompanyId($userId)) {
            http_response_code(400);
            echo json_encode(["error" => "User already has a company."]);
            return;
        }

        // Создание новой компании
        $companyId = $this->companyModel->createCompany($companyName, $address, $userId);

        if ($companyId) {
            http_response_code(201);
            echo json_encode(["message" => "Company created successfully.", "company_id" => $companyId]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to create company."]);
        }
    }

    /**
     * Метод для получения информации о компании.
     */
    public function getCompanyInfo()
    {
        $userId = $this->userModel->getUserIdFromToken();

        // Проверка прав на просмотр информации о компании
        if (!$this->userModel->hasPermission($userId, 'view_company_info')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $companyId = $this->companyModel->getUserCompanyId($userId);

        if (!$companyId) {
            http_response_code(404);
            echo json_encode(["error" => "Company not found."]);
            return;
        }

        $companyInfo = $this->companyModel->getCompanyInfo($companyId);
        echo json_encode($companyInfo);
    }
    /**
     * Получение ID создателя компании.
     */
    public function getCompanyCreatorId($companyId)
    {
        // Получаем ID создателя компании
        $result = $this->fetchFromMySQL("SELECT company_created_user_id FROM Companies WHERE id = :companyId", [':companyId' => $companyId]);
        
        // Возвращаем ID создателя компании
        return $result['company_created_user_id'] ?? null;
    }
    /**
     * Метод для получения списка сотрудников компании.
     */
    public function getCompanyEmployees()
    {
        $userId = $this->userModel->getUserIdFromToken();
        
        if (!$this->userModel->hasPermission($userId, 'Перегляд співробітників компанії')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }
        
        $companyId = $this->companyModel->getUserCompanyId($userId);
        
        if (!$companyId) {
            http_response_code(404);
            echo json_encode(["error" => "Company not found."]);
            return;
        }
        
        // Получаем сотрудников из модели
        $employees = $this->companyModel->getCompanyEmployees($companyId);
        
        // Убедитесь, что данные правильно передаются в json_encode()
        echo json_encode($employees);  // Здесь $employees уже должен быть массивом или объектом
    }
}    
