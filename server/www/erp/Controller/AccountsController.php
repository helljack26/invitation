<?php

namespace Controller;

use Model\AccountsModel;
use Middleware\AuthMiddleware;
use Model\UserModel;

class AccountsController
{
    private $accountsModel;
    private $authMiddleware;
    private $userModel;
    public function __construct(AccountsModel $accountsModel, AuthMiddleware $authMiddleware, UserModel $userModel)
    {
        $this->accountsModel = $accountsModel;
        $this->authMiddleware = $authMiddleware;
        $this->userModel = $userModel;
    }


    public function createAccount()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);

        if (!$this->userModel->hasPermission($userId, 'create_account')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        if (!$this->userModel->getCompanyInfo($userId)) {
            http_response_code(403);
            echo json_encode(["error" => "You don't have a signed company"]);
            return;
        }

        // Получение данных о Постачальнике из запроса
        $data = json_decode(file_get_contents('php://input'), true);

        // Проверка наличия обязательных полей
        $requiredFields = [
            'name',
            'industry',
            'phone_office',
            'email',
        ];
        // 'website',
        // 'billing_address_street',
        // 'billing_address_city',
        // 'billing_address_state',
        // 'billing_address_postalcode',
        // 'billing_address_country',
        // 'shipping_address_street',
        // 'shipping_address_city',
        // 'shipping_address_state',
        // 'shipping_address_postalcode',
        // 'shipping_address_country',
        // 'description'

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required field: $field"]);
                return;
            }
        }

        // Установка значения assigned_to_user_id в случае отсутствия assigned_to_user_id в запросе
        $assignedToUserId = isset($data['assigned_to_user_id']) ? $data['assigned_to_user_id'] : $userId;

        // Создание Постачальника
        $accountId = $this->accountsModel->createAccount(
            $data['name'],
            $data['industry'],
            $data['website'],
            $data['phone_office'],
            $data['billing_address_street'],
            $data['billing_address_city'],
            $data['billing_address_state'],
            $data['billing_address_postalcode'],
            $data['billing_address_country'],
            $data['shipping_address_street'],
            $data['shipping_address_city'],
            $data['shipping_address_state'],
            $data['shipping_address_postalcode'],
            $data['shipping_address_country'],
            $data['description'],
            $data['email'],
            $assignedToUserId // Используйте переменную $assignedToUserId вместо $userId
        );

        if ($accountId) {
            // Создание связи между Постачальником и предприятием
            $companyId = $companyInfo['details']['id'];
            $this->accountsModel->linkAccountToCompany($companyId, $accountId);
            $this->accountsModel->linkUserToAccount($userId, $accountId);
            // Очистка кеша связанного с Постачальниками для данной компании
            $this->accountsModel->clearCompanyAccountsCache($companyId);
            http_response_code(201);
            echo json_encode(["message" => "Account created successfully.", "accountId" => $accountId]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to create account."]);
        }
    }


    // Добавьте другие методы, например, для получения списка Постачальников, обновления Постачальника и других операций.

    // Пример метода для получения списка Постачальников
    public function getAccounts()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        // Проверка разрешений пользователя для получения списка Постачальников
        if (!$this->userModel->hasPermission($userId, 'get_account')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        // Получите список Постачальников из модели и верните его в формате JSON
        $accounts = $this->accountsModel->getAccountsByCompany($companyId);

        // Проверка на пустой результат
        if (empty($accounts)) {
            http_response_code(404);
            echo json_encode(["error" => "No records found."]);
            return;
        }

        http_response_code(200);
        echo json_encode($accounts);
    }

    // Удалить Постачальников по списку ИД
    public function delAccountById()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        if (!$this->userModel->hasPermission($userId, 'del_account')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        $account_ids = $input['account_ids'] ?? [];

        if (empty($account_ids) || !$userId) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid request parameters."]);
            return;
        }

        $successfullyDeleted = [];
        $failedToDelete = [];

        foreach ($account_ids as $account_id) {
            $accountInfo = $this->accountsModel->getAccountByCompanyAndId($companyId, $account_id);
            if ($accountInfo) {
                $deleted = $this->accountsModel->deleteAccountById($account_id, $userId);

                if ($deleted) {
                    $successfullyDeleted[] = $account_id;
                } else {
                    $failedToDelete[] = $account_id;
                }
            } else {
                $failedToDelete[] = $account_id;
            }
        }

        if (empty($failedToDelete)) {
            $this->accountsModel->clearCompanyAccountsCache($companyId);

            http_response_code(200);
            echo json_encode([
                "success" => $successfullyDeleted,
                "error" => $failedToDelete,
            ]);
        } else {
            http_response_code(422);
            echo json_encode([
                "error" => $failedToDelete,
            ]);
        }
    }

    //  Восстановить постачальников по списку ИД
    public function restoreAccountById()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        if (!$this->userModel->hasPermission($userId, 'del_account')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        $account_ids = $input['account_ids'] ?? [];

        if (empty($account_ids) || !$userId) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid request parameters."]);
            return;
        }

        $successfullyRestored = [];
        $failedToRestore = [];

        foreach ($account_ids as $account_id) {
            $accountInfo = $this->accountsModel->getAccountByCompanyAndId($companyId, $account_id);
            if ($accountInfo) {
                $deleted = $this->accountsModel->restoreAccountById($account_id, $userId);

                if ($deleted) {
                    $successfullyRestored[] = $account_id;
                } else {
                    $failedToRestore[] = $account_id;
                }
            } else {
                $failedToRestore[] = $account_id;
            }
        }

        if (empty($failedToRestore)) {
            $this->accountsModel->clearCompanyAccountsCache($companyId);

            http_response_code(200);
            echo json_encode([
                "success" => $successfullyRestored,
                "error" => $failedToRestore,
            ]);
        } else {
            http_response_code(422);
            echo json_encode([
                "error" => $failedToRestore,
            ]);
        }
    }



    // Обновить информацию о Постачальнике по его ID
    public function updateAccountById()
    {
        $userId = $this->userModel->getUserIdFromToken();

        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        if (!$this->userModel->hasPermission($userId, 'update_account')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);

        $accountId = $data['account_id'] ?? null;

        if (!$accountId) {
            http_response_code(400);
            echo json_encode(["error" => "Missing account ID parameter."]);
            return;
        }

        $accountInfo = $this->accountsModel->getAccountByCompanyAndId($companyId, $accountId);

        if (!$accountInfo) {
            http_response_code(404);
            echo json_encode(["error" => "Account not found for this company or invalid account ID."]);
            return;
        }

        // Проверяем обновляемые поля и обновляем информацию о Постачальнике
        if (isset($data['name'])) {
            $accountInfo['name'] = $data['name'];
            $updatedFields['name'] = $data['name'];
        }
        if (isset($data['industry'])) {
            $accountInfo['industry'] = $data['industry'];
            $updatedFields['industry'] = $data['industry'];
        }
        if (isset($data['website'])) {
            $accountInfo['website'] = $data['website'];
            $updatedFields['website'] = $data['website'];
        }
        if (isset($data['phone_office'])) {
            $accountInfo['phone_office'] = $data['phone_office'];
            $updatedFields['phone_office'] = $data['phone_office'];
        }
        if (isset($data['billing_address_street'])) {
            $accountInfo['billing_address_street'] = $data['billing_address_street'];
            $updatedFields['billing_address_street'] = $data['billing_address_street'];
        }
        if (isset($data['billing_address_city'])) {
            $accountInfo['billing_address_city'] = $data['billing_address_city'];
            $updatedFields['billing_address_city'] = $data['billing_address_city'];
        }
        if (isset($data['billing_address_state'])) {
            $accountInfo['billing_address_state'] = $data['billing_address_state'];
            $updatedFields['billing_address_state'] = $data['billing_address_state'];
        }
        if (isset($data['billing_address_postalcode'])) {
            $accountInfo['billing_address_postalcode'] = $data['billing_address_postalcode'];
            $updatedFields['billing_address_postalcode'] = $data['billing_address_postalcode'];
        }
        if (isset($data['billing_address_country'])) {
            $accountInfo['billing_address_country'] = $data['billing_address_country'];
            $updatedFields['billing_address_country'] = $data['billing_address_country'];
        }
        if (isset($data['shipping_address_street'])) {
            $accountInfo['shipping_address_street'] = $data['shipping_address_street'];
            $updatedFields['shipping_address_street'] = $data['shipping_address_street'];
        }
        if (isset($data['shipping_address_city'])) {
            $accountInfo['shipping_address_city'] = $data['shipping_address_city'];
            $updatedFields['shipping_address_city'] = $data['shipping_address_city'];
        }
        if (isset($data['shipping_address_state'])) {
            $accountInfo['shipping_address_state'] = $data['shipping_address_state'];
            $updatedFields['shipping_address_state'] = $data['shipping_address_state'];
        }
        if (isset($data['shipping_address_postalcode'])) {
            $accountInfo['shipping_address_postalcode'] = $data['shipping_address_postalcode'];
            $updatedFields['shipping_address_postalcode'] = $data['shipping_address_postalcode'];
        }
        if (isset($data['shipping_address_country'])) {
            $accountInfo['shipping_address_country'] = $data['shipping_address_country'];
            $updatedFields['shipping_address_country'] = $data['shipping_address_country'];
        }
        if (isset($data['description'])) {
            $accountInfo['description'] = $data['description'];
            $updatedFields['description'] = $data['description'];
        }
        if (isset($accountId)) {
            $accountInfo['accountId'] = $accountId;
            $updatedFields['accountId'] = $accountId;
        }
        if (isset($data['assigned_to_user_id'])) {
            $accountInfo['assigned_to_user_id'] = $data['assigned_to_user_id'];
            $updatedFields['assigned_to_user_id'] = $data['assigned_to_user_id'];
        }
        if (isset($data['email'])) {
            $accountInfo['email'] = $data['email'];
            $updatedFields['email'] = $data['email'];
        }

        // Вызываем метод модели для обновления информации о Постачальнике
        $updated = $this->accountsModel->updateAccount(
            $accountId,
            $accountInfo['name'],
            $accountInfo['industry'],
            $accountInfo['website'],
            $accountInfo['phone_office'],
            $accountInfo['billing_address_street'],
            $accountInfo['billing_address_city'],
            $accountInfo['billing_address_state'],
            $accountInfo['billing_address_postalcode'],
            $accountInfo['billing_address_country'],
            $accountInfo['shipping_address_street'],
            $accountInfo['shipping_address_city'],
            $accountInfo['shipping_address_state'],
            $accountInfo['shipping_address_postalcode'],
            $accountInfo['shipping_address_country'],
            $accountInfo['description'],
            $accountInfo['assigned_to_user_id'],
            $accountInfo['email'],
            $userId
        );
        if ($updated) {
            $this->accountsModel->clearCompanyAccountsCache($companyId);
            http_response_code(200);
            echo json_encode(["message" => "Account updated successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update account."]);
        }
    }
}
