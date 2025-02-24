<?php
namespace Controller;

use Model\StandardAccountsPlanModel;
use Middleware\AuthMiddleware;
use Model\UserModel;

class StandardAccountsPlanController
{
    private $standardAccountsPlanModel;
    private $authMiddleware;
    private $userModel;

    public function __construct(StandardAccountsPlanModel $standardAccountsPlanModel, AuthMiddleware $authMiddleware, UserModel $userModel)
    {
        $this->standardAccountsPlanModel = $standardAccountsPlanModel;
        $this->authMiddleware = $authMiddleware;
        $this->userModel = $userModel;
    }

    // Метод для добавления стандартного счета в план
    public function addStandardAccountToPlan()
    {
        $userId = $this->userModel->getUserIdFromToken();
        if (!$this->userModel->hasPermission($userId, 'add_standard_account_to_plan')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

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

        if ($code === null || $name === null || $type === null || $nonBalance === null || $quantity === null || $currency === null || $isDeleted === null) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields."]);
            return;
        }

        $result = $this->standardAccountsPlanModel->AddStandardAccountsPlan(
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
            echo json_encode(["message" => "Standard account added to plan successfully.", "id" => $result[1]]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to add standard account to plan."]);
        }
    }



    // Метод для получения всех стандартных счетов из плана
    public function getAllStandardAccountsInPlan()
    {
        $userId = $this->userModel->getUserIdFromToken();

        if (!$this->userModel->hasPermission($userId, 'get_standard_account_from_plan')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $standardAccounts = $this->standardAccountsPlanModel->GetAllStandardAccountsPlan();
        http_response_code(200);
        echo json_encode(["standardAccounts" => $standardAccounts]);
    }

    // Метод для удаления стандартного счета из плана
    public function deleteStandardAccountFromPlan()
    {
        $userId = $this->userModel->getUserIdFromToken();

        if (!$this->userModel->hasPermission($userId, 'delete_standard_account_from_plan')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset ($data['Code'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields."]);
            return;
        }

        $result = $this->standardAccountsPlanModel->DeleteStandardAccountsPlan($data['Code']);

        if ($result) {
            http_response_code(200);
            echo json_encode(["message" => "Standard account deleted from plan successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to delete standard account from plan."]);
        }
    }

    // Метод для обновления стандартного счета в плане
    public function updateStandardAccountInPlan()
    {
        $userId = $this->userModel->getUserIdFromToken();

        if (!$this->userModel->hasPermission($userId, 'update_standard_account_in_plan')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $id = $data['Id'] ?? null; // Assuming you have an 'Id' field in your input data
        $code = $data['Code'] ?? null;
        $name = $data['Name'] ?? null;
        $type = $data['Type'] ?? null; // Add this line for 'type' field
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

        if ($id === null || $code === null || $name === null || $type === null || $nonBalance === null || $quantity === null || $currency === null || $isDeleted === null) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields."]);
            return;
        }

        $result = $this->standardAccountsPlanModel->UpdateStandardAccountsPlan(
            $id,
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
            echo json_encode(["message" => "Standard account in plan updated successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update standard account in plan."]);
        }
    }

    // Добавьте другие методы, которые могут понадобиться для работы со стандартными счетами в плане
}