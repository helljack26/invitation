<?php
namespace Controller;

use Model\WarehouseDocumentModel;
use Model\UserModel;
use Model\FinancialDocumentModel;
use Middleware\AuthMiddleware;
use Model\RoleModel;
use Model\TaxModel;
use Service\TaxCalculator;

class TaxController
{
    private $taxCalculator; // Добавлено свойство для TaxCalculator
    private $warehouseModel;
    private $financialModel;
    private $authMiddleware;
    private $roleModel;
    private $userModel; // Добавлено новое свойство
    private $taxModel;

    public function __construct(TaxCalculator $taxCalculator, WarehouseDocumentModel $warehouseModel, FinancialDocumentModel $financialModel, AuthMiddleware $authMiddleware, RoleModel $roleModel, UserModel $userModel, TaxModel $taxModel)
    {
        $this->taxCalculator = $taxCalculator;
        $this->warehouseModel = $warehouseModel;
        $this->financialModel = $financialModel;
        $this->authMiddleware = $authMiddleware;
        $this->roleModel = $roleModel;
        $this->userModel = $userModel; // Инициализация нового свойства
        $this->taxModel = $taxModel;
    }

    public function calculate()
    {
        $userId = $this->userModel->getUserIdFromToken();
        // Получение параметров запроса
        $queryParams = $_GET;
        $eGRPOUId = $queryParams['eGRPOUId'] ?? null;
        $startDate = $queryParams['startDate'] ?? null;
        $endDate = $queryParams['endDate'] ?? null;

        if (!$eGRPOUId) {
            http_response_code(400);
            echo json_encode(["error" => "Enterprise ID is required for tax calculation."]);
            return;
        }

        // Проверяем, может ли пользователь рассчитывать налоги для любого предприятия
        if ($this->userModel->hasPermission($userId, 'calculate_taxes_any')) {
            $taxCalculation = $this->taxCalculator->calculateTotalTax($eGRPOUId, $startDate, $endDate);
        } elseif ($this->userModel->hasPermission($userId, 'calculate_taxes_own')) {
            // Проверяем ассоциацию пользователя с предприятием
            $association = $this->userModel->isUserAssociatedWithEnterprise($userId, $eGRPOUId);
            if (!$association['associated']) {
                http_response_code(403); // или 404, в зависимости от желаемой логики
                echo json_encode(["error" => "Access denied: " . $association['reason']]);
                return;
            }
            $taxCalculation = $this->taxCalculator->calculateTotalTax($eGRPOUId, $startDate, $endDate);
        } else {
            http_response_code(403);
            echo json_encode(["error" => "Access denied. No permission to calculate taxes."]);
            return;
        }

        if (!empty ($taxCalculation['message'])) {
            http_response_code(400);
            echo json_encode(["error" => $taxCalculation['message']]);
            return;
        }

        http_response_code(200);

        echo json_encode([
            "message" => "Taxes calculated successfully.",
            "vatToPay" => $taxCalculation['vatToPay'],
            "exciseToPay" => $taxCalculation['exciseToPay'],
            "totalIncome" => $taxCalculation['totalIncome'],
            "totalTax" => $taxCalculation['totalTax'],
            "getFop2Tax" => $taxCalculation['getFop2Tax'],
            "getFop3Tax" => $taxCalculation['getFop3Tax']
        ]);
    }
    public function saveOrUpdateFOP2TaxDocument($documentData)
    {
        $this->saveOrUpdateTaxDocument($documentData, 'FOP2_Taxes_id');
    }

    public function saveOrUpdateFOP3TaxDocument($documentData)
    {
        $this->saveOrUpdateTaxDocument($documentData, 'FOP3_Taxes_id');
    }

    private function saveOrUpdateTaxDocument($documentData, $taxTypeField)
    {
        $userId = $this->userModel->getUserIdFromToken();

        $jsonData = file_get_contents('php://input');
        $documentData = json_decode($jsonData, true);

        if (!is_array($documentData) || empty ($documentData)) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Invalid data']);
            return;
        }

        // Checking for mandatory fields
        $requiredFields = ['Month', 'Quarter', 'Year', 'Default_ZP', 'eGRPOUId', 'EN_Tax_Rate', 'ESV_Rate'];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $documentData)) {
                http_response_code(400); // Bad Request
                echo json_encode(['error' => "Field '$field' is missing in the data"]);
                return;
            }
        }

        // Checking if the enterprise belongs to the user
        $eGRPOUIds = $this->userModel->getUserEnterprises($userId);
        $isValidEnterprise = false;
        foreach ($eGRPOUIds as $enterpriseInfo) {
            $eGRPOUId = (int) $enterpriseInfo['eGRPOUId'];
            if ($eGRPOUId === (int) $documentData['eGRPOU']) {
                $isValidEnterprise = true;
                break;
            }
        }
        if (!$isValidEnterprise) {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'This is not your enterprise']);
            return;
        }
        // Creating an array for passing it to the model
        $document = [
            $taxTypeField => $documentData[$taxTypeField] ?? null,
            'Month' => $documentData['Month'],
            'Quarter' => $documentData['Quarter'],
            'Year' => $documentData['Year'],
            'Default_ZP' => $documentData['Default_ZP'],
            'eGRPOUId' => $documentData['eGRPOUId'],
            'EN_Tax_Rate' => $documentData['EN_Tax_Rate'],
            'ESV_Rate' => $documentData['ESV_Rate']
        ];
        if ($document[$taxTypeField] === null) {
            unset($document[$taxTypeField]);
        }

        // Call the appropriate model method based on $taxTypeField
        $result = null;
        if ($taxTypeField === 'FOP2_Taxes_id') {
            $result = $this->taxModel->insertOrUpdateDocumentTax($document);
        } elseif ($taxTypeField === 'FOP3_Taxes_id') {
            $result = $this->taxModel->insertOrUpdateDocumentTax3($document);
        }

        if ($result === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error while saving or updating data']);
            return;
        }

        http_response_code(200);
        $taxTypeId = $document[$taxTypeField];
        $this->taxModel->clearDocumentTaxCache($taxTypeId);
        echo json_encode(['success' => 'Data successfully saved or updated']);
    }
}