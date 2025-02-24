<?php
namespace Controller;

use Model\EnterpriseAccountPlansModel;
use Middleware\AuthMiddleware;
use Model\UserModel;

class EnterpriseAccountPlansController
{
    private $enterpriseAccountPlansModel;
    private $authMiddleware;
    private $userModel;

    public function __construct(EnterpriseAccountPlansModel $enterpriseAccountPlansModel, AuthMiddleware $authMiddleware, UserModel $userModel)
    {
        $this->enterpriseAccountPlansModel = $enterpriseAccountPlansModel;
        $this->authMiddleware = $authMiddleware;
        $this->userModel = $userModel;
    }

    // Метод для добавления счета в план
    public function addEnterpriseAccountPlan()
    {
        $userId = $this->userModel->getUserIdFromToken();
        if (!$this->userModel->hasPermission($userId, 'add_account_to_plan')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $eGRPOUId = $data['eGRPOUId'] ?? null;
        $code = $data['code'] ?? null;
        $name = $data['name'] ?? null;
        $type = $data['type'] ?? null;
        $nonBalance = $data['non_balance'] ?? 0;
        $quantity = $data['quantity'] ?? 0;
        $currency = $data['currency'] ?? 0;
        $accruedOrRecognized = $data['accrued_amount'] ?? null;
        $vatPurpose = $data['vat_purpose'] ?? null;
        $accruedAmount = $data['accrued_amount'] ?? null;
        $subaccount1 = $data['subaccount1'] ?? null;
        $subaccount2 = $data['subaccount2'] ?? null;
        $subaccount3 = $data['subaccount3'] ?? null;
        $isDeleted = $data['is_deleted'] ?? 0;


        if ($eGRPOUId === null || $code === null || $name === null || $type === null || $nonBalance === null || $quantity === null || $currency === null || $isDeleted === null) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields."]);
            return;
        }

        $result = $this->enterpriseAccountPlansModel->AddEnterpriseAccountPlan(
            $eGRPOUId,
            $code,
            $name,
            $type,
            $nonBalance,
            $quantity,
            $currency,
            $accruedOrRecognized,
            $vatPurpose,
            $accruedAmount,
            $subaccount1,
            $subaccount2,
            $subaccount3,
            $isDeleted
        );

        if ($result[0]) {
            http_response_code(201);
            echo json_encode(["message" => "Account added to plan successfully.", "id" => $result[1]]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to add account to plan."]);
        }
    }

    // Метод для получения всех счетов из плана
    public function getAllEnterpriseAccountPlans()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        if (!$this->userModel->hasPermission($userId, 'get_enterprise_accounts_from_plan')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }
        // Отримання даних про КВЕД з запиту
        $data = json_decode(file_get_contents('php://input'), true);

        // Перевірка наявності обов'язкових полів
        if (!isset ($data['eGRPOUId'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields."]);
            return;
        }

        // Перевірка принадлежності предприятия до компанії користувача
        if (!$this->userModel->isUserAssociatedWithEnterprise($companyId, $data['eGRPOUId'])) {
            http_response_code(403);
            echo json_encode(["error" => "Enterprise does not belong to the user's company."]);
            return;
        }

        $accounts = $this->enterpriseAccountPlansModel->GetEnterpriseAccountPlansByEGRPOU($data['eGRPOUId']);
        http_response_code(200);
        echo json_encode(["enterpriseAccounts" => $accounts]);
    }


    // Метод для удаления счета из плана
    public function deleteEnterpriseAccountPlan()
    {
        $userId = $this->userModel->getUserIdFromToken();

        if (!$this->userModel->hasPermission($userId, 'delete_account_from_plan')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset ($data['Ids']) || !isset ($data['eGRPOUId'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields."]);
            return;
        }

        $account_plans_ids = $data['Ids'] ?? [];

        $successfullyDeleted = [];
        $failedToDelete = [];

        foreach ($account_plans_ids as $account_id) {
            $deleted = $this->enterpriseAccountPlansModel->DeleteEnterpriseAccountPlan($account_id, $data['eGRPOUId']);

            if ($deleted) {
                $successfullyDeleted[] = $account_id;
            } else {
                $failedToDelete[] = $account_id;
            }
        }

        if (empty ($failedToDelete)) {
            http_response_code(200);
            echo json_encode(["message" => "Account deleted from plan successfully."]);
        } else {
            http_response_code(500);
            echo json_encode([
                "error" => "Failed to delete account from plan.",
                "failedToDelete" => $failedToDelete,
            ]);
        }
    }

    // Метод для обновления счета в плане
    public function updateEnterpriseAccountPlan()
    {
        $userId = $this->userModel->getUserIdFromToken();

        if (!$this->userModel->hasPermission($userId, 'update_account_in_plan')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $id = $data['Id'] ?? null;
        $eGRPOUId = $data['EGRPOUId'] ?? null;
        $code = $data['Code'] ?? null;
        $name = $data['Name'] ?? null;
        $type = $data['Type'] ?? null;
        $nonBalance = $data['NonBalance'] ?? 0;
        $quantity = $data['Quantity'] ?? 0;
        $currency = $data['Currency'] ?? 0;
        $accruedOrRecognized = $data['AccruedOrRecognized'] ?? null;
        $vatPurpose = $data['VATPurpose'] ?? null;
        $accruedAmount = $data['AccruedAmount'] ?? null;
        $subaccount1 = $data['Subaccount1'] ?? null;
        $subaccount2 = $data['Subaccount2'] ?? null;
        $subaccount3 = $data['Subaccount3'] ?? null;
        $isDeleted = $data['IsDeleted'] ?? 0;

        if ($id === null || $eGRPOUId === null || $code === null || $name === null || $type === null || $nonBalance === null || $quantity === null || $currency === null || $isDeleted === null) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields."]);
            return;
        }

        $result = $this->enterpriseAccountPlansModel->UpdateEnterpriseAccountPlan(
            $id,
            $eGRPOUId,
            $code,
            $name,
            $type,
            $nonBalance,
            $quantity,
            $currency,
            $accruedOrRecognized,
            $vatPurpose,
            $accruedAmount,
            $subaccount1,
            $subaccount2,
            $subaccount3,
            $isDeleted
        );

        if ($result) {
            http_response_code(200);
            echo json_encode(["message" => "Account in plan updated successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update account in plan."]);
        }
    }

    // Добавьте другие методы, которые могут понадобиться для работы счетами в плане
}