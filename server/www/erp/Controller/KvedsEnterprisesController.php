<?php
namespace Controller;

use Model\KvedModel;
use Middleware\AuthMiddleware;
use Model\UserModel;

class KvedsEnterprisesController
{
    private $KvedModel;
    private $authMiddleware;
    private $userModel;

    public function __construct(KvedModel $KvedModel, AuthMiddleware $authMiddleware, UserModel $userModel)
    {
        $this->KvedModel = $KvedModel;
        $this->authMiddleware = $authMiddleware;
        $this->userModel = $userModel;
    }

    // Метод для отримання списку КВЕДів для певного підприємства
    public function getKvedsByEnterprise()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        // Отримання даних про КВЕД з запиту

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

        // Получение всех КВЕДов для указанного ЕДРПОУ
        $kveds = $this->KvedModel->getKvedsByEnterprise($data['eGRPOUId']);

        // Возвращение результатов
        http_response_code(200);
        echo json_encode(["kveds" => $kveds]);
    }


    // Метод для додавання КВЕДа до підприємства
    public function addKvedAndAssociateWithEnterprise()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        // Перевірка дозволів користувача для додавання КВЕДа
        if (!$this->userModel->hasPermission($userId, 'add_kved')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        // Отримання даних про КВЕД з запиту
        $data = json_decode(file_get_contents('php://input'), true);

        // Перевірка наявності обов'язкових полів
        if (!isset ($data['number']) || !isset ($data['name']) || !isset ($data['main']) || !isset ($data['eGRPOUId'])) {
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

        // Додавання КВЕДа
        $kvedId = $this->KvedModel->addKved($data['number'], $data['name'], $data['main']);

        // Check if $data['main'] is equal to 1
        if ($data['main'] == 1) {
            // Update existing main KVED to 0
            $this->KvedModel->updateMainKvedToZero($data['eGRPOUId']);
        }

        // Перевірка, чи вдалося додати КВЕД
        if (!$kvedId[0]) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to add KVED."]);
            return;
        }

        // Асоціація КВЕДа з підприємством
        $added = $this->KvedModel->addKvedToEnterprise($data['eGRPOUId'], $kvedId[1]);
        $eGRPOUIds = $this->userModel->getUserEnterprises($userId);
        $isValidEnterprise = false;

        foreach ($eGRPOUIds as $enterpriseInfo) {
            $eGRPOUId = (int) $enterpriseInfo['eGRPOU']; // Ключь из масива который возвращает проверка предприятий
            if ($eGRPOUId === (int) $data['eGRPOUId']) {
                $isValidEnterprise = true;
                break;
            }
        }

        if (!$isValidEnterprise) {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'This is not your enterprise']);
            return;
        }

        if ($added) {
            http_response_code(201);
            echo json_encode(["message" => "KVED added and associated with enterprise successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to associate KVED with enterprise."]);
        }
    }


    // Метод для оновлення КВЕДів для підприємства
    public function updateKvedsForEnterprise()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        // Перевірка дозволів користувача для оновлення КВЕДів
        if (!$this->userModel->hasPermission($userId, 'update_kveds')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        // Отримання даних про КВЕД з запиту
        $data = json_decode(file_get_contents('php://input'), true);

        // Оновлення КВЕДів для підприємства
        $updated = $this->KvedModel->updateKvedForEnterprise($companyId, $data['kved_ids']);

        if ($updated) {
            http_response_code(200);
            echo json_encode(["message" => "KVEDs updated for enterprise successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update KVEDs for enterprise."]);
        }
    }

    // Метод для видалення КВЕДа з підприємства
    public function deleteKvedFromEnterprise()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        // Перевірка дозволів користувача для видалення КВЕДа
        if (!$this->userModel->hasPermission($userId, 'delete_kved')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        // Перевірка наявності обов'язкових полів
        $data = json_decode(file_get_contents('php://input'), true);

        // Перевірка наявності обов'язкових полів
        if (!isset ($data['kved_id']) || !isset ($data['eGRPOUId'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields."]);
            return;
        }

        if (!$this->userModel->isUserAssociatedWithEnterprise($companyId, $data['eGRPOUId'])) {
            http_response_code(403);
            echo json_encode(["error" => "Enterprise does not belong to the user's company."]);
            return;
        }

        // Видалення КВЕДа з підприємства
        $kvedIds = (array) $data['kved_id'];
        foreach ($kvedIds as $kvedId) {
            // Validate or sanitize $kvedId if needed
            $deleted = $this->KvedModel->deleteKvedFromEnterprise($data['eGRPOUId'], $kvedId);
            $eGRPOUIds = $this->userModel->getUserEnterprises($userId);
            $isValidEnterprise = false;

            foreach ($eGRPOUIds as $enterpriseInfo) {
                $eGRPOUId = (int) $enterpriseInfo['eGRPOU']; // Ключь из масива который возвращает проверка предприятий
                if ($eGRPOUId === (int) $data['eGRPOUId']) {
                    $isValidEnterprise = true;
                    break;
                }
            }

            if (!$isValidEnterprise) {
                http_response_code(403); // Forbidden
                echo json_encode(['error' => 'This is not your enterprise']);
                return;
            }
            if (!$deleted) {
                http_response_code(500);
                echo json_encode(["error" => "Failed to delete KVED from enterprise."]);
                return;
            }
        }

        http_response_code(200);
        echo json_encode(["message" => "KVEDs deleted from enterprise successfully."]);
    }


    // Додайте інші методи, які можуть знадобитися для роботи зі зв'язком між КВЕДами та підприємствами
}