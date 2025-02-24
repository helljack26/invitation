<?php
namespace Controller;

use Model\WarehouseDocumentModel;
use Model\UserModel;
use Model\FinancialDocumentModel;
use Model\IntegrationModel;
use Model\EnterpriseModel;
use Controller\EnterpriseController;

class TorgsoftController
{
    private $integrationModel;
    private $userModel;
    private $enterpriseModel;
    private $enterpriseController;
    private $warehouseDocumentModel;
    private $financialDocumentModel;

    public function __construct(IntegrationModel $integrationModel, UserModel $userModel, EnterpriseModel $enterpriseModel, EnterpriseController $enterpriseController, warehouseDocumentModel $warehouseDocumentModel, financialDocumentModel $financialDocumentModel)
    {
        $this->integrationModel = $integrationModel;
        $this->userModel = $userModel;
        $this->enterpriseModel = $enterpriseModel;
        $this->enterpriseController = $enterpriseController;
        $this->warehouseDocumentModel = $warehouseDocumentModel;
        $this->financialDocumentModel = $financialDocumentModel;
    }


    public function syncEnterprises()
    {
        try {
            $userId = $this->userModel->getUserIdFromToken();
            $companyInfo = $this->userModel->getCompanyInfo($userId);

            if (!$companyInfo || !isset ($companyInfo['details']['id'])) {
                throw new Exception('Company info not found for user');
            }

            $companyId = $companyInfo['details']['id'];
            $integrations = $this->integrationModel->getIntegrationsByCompanyId($companyId);

            foreach ($integrations as $integration) {
                if ($integration['integrationType'] === 'Torgsoft') {
                    $settings = $integration['settings'];

                    if (!isset ($settings['host'], $settings['port'])) {
                        throw new Exception('Missing required settings for Torgsoft integration');
                    }

                    $torgsoftService = new TorgsoftService(
                        $settings['host'],
                        $settings['port'],
                        $settings['username'] ?? null,
                        $settings['password'] ?? null
                    );

                    $enterprises = $torgsoftService->getAllEnterprises();

                    foreach ($enterprises as $enterprise) {
                        $name = $enterprise['name'];
                        $eGRPOU = $enterprise['egrpou'];
                        $individualTaxNumber = $enterprise['individualTaxNumber'];
                        $this->enterpriseModel->addOrUpdateEnterprise($name, $eGRPOU, $individualTaxNumber);
                    }
                }
            }

            // Відсутність помилок, повертаємо статус 200
            http_response_code(200);
            echo json_encode(["success" => true, "enterprises" => $enterprises], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            // Обробка винятків і запис у журнал
            error_log('Error in syncEnterprises: ' . $e->getMessage());

            // Повертаємо статус 500 та повідомлення про помилку
            http_response_code(500);
            // Для дебага
            // echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);

            echo json_encode(['error' => 'Internal Server Error Duplicate EDRPO OR Empty'], JSON_UNESCAPED_UNICODE);
        }
    }



    public function syncInvoices()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);

        //Получить всю инфу о компании 
        if (!$companyInfo || !isset ($companyInfo['details']['id'])) {
            // Обработка ошибки: информация о компании не найдена
            echo json_encode(['error' => 'Company info not found for user']);
            return;
        }

        //Отделить ИД компании из companyInfo
        $companyId = $companyInfo['details']['id'];
        //Подставить полученое ИД
        $integrations = $this->integrationModel->getIntegrationsByCompanyId($companyId);


        foreach ($integrations as $integration) {
            if ($integration['integrationType'] === 'Torgsoft') {
                $settings = $integration['settings'];

                if (!isset ($settings['host'], $settings['port'])) {
                    continue;
                }

                $torgsoftService = new TorgsoftService(
                    $settings['host'],
                    $settings['port'],
                    $settings['username'] ?? null,
                    $settings['password'] ?? null
                );
            }

            $date1 = '2001-01-01';
            $date2 = '2024-12-31';
            $isPayVAT = true;

            // Получаем ID предприятий напрямую, без JSON декодирования
            $enterpriseIds = $this->userModel->getUserEnterprises($userId);
            $allInvoices = [];

            foreach ($enterpriseIds as $enterpriseInfo) {
                $enterpriseId = (int) $enterpriseInfo['enterpriseId']; // Преобразование в целое число
                $enterpriseName = $enterpriseInfo['name']; // Преобразование в целое число

                $invoices = $torgsoftService->getInvoices($date1, $date2, $enterpriseId, $isPayVAT);

                foreach ($invoices as &$invoice) {
                    $invoice['enterpriseId'] = $enterpriseId;
                    $invoice['enterpriseName'] = $enterpriseName;
                    $this->warehouseDocumentModel->insertOrUpdateInvoice($invoice);
                }
                if (!empty ($invoices)) {
                    $allInvoices = array_merge($allInvoices, $invoices);
                }
            }
        }
        echo json_encode(
            [
                "message" => "Data synchronized successfully.",
                "invoices" => $allInvoices,
            ],
            JSON_UNESCAPED_UNICODE
        );
    }

    public function syncFinancialDocuments()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);

        //Получить всю инфу о компании 
        if (!$companyInfo || !isset ($companyInfo['details']['id'])) {
            // Обработка ошибки: информация о компании не найдена
            echo json_encode(['error' => 'Company info not found for user']);
            return;
        }

        //Отделить ИД компании из companyInfo
        $companyId = $companyInfo['details']['id'];
        //Подставить полученое ИД
        $integrations = $this->integrationModel->getIntegrationsByCompanyId($companyId);


        foreach ($integrations as $integration) {
            if ($integration['integrationType'] === 'Torgsoft') {
                $settings = $integration['settings'];

                if (!isset ($settings['host'], $settings['port'])) {
                    continue;
                }

                $torgsoftService = new TorgsoftService(
                    $settings['host'],
                    $settings['port'],
                    $settings['username'] ?? null,
                    $settings['password'] ?? null
                );
            }

            $date1 = '2001-01-01';
            $date2 = '2024-12-31';

            // Получаем ID предприятий напрямую, без JSON декодирования
            $enterpriseIds = $this->userModel->getUserEnterprises($userId);
            $allfinancialDocuments = [];

            foreach ($enterpriseIds as $enterpriseInfo) {
                $enterpriseId = (int) $enterpriseInfo['enterpriseId'];
                $enterpriseName = $enterpriseInfo['name'];
                $financialDocuments = $torgsoftService->getFinancialDocuments($enterpriseId, $date1, $date2);
                foreach ($financialDocuments as &$financialDocument) {
                    // Decode each financial document before appending it
                    $financialDocument['enterpriseId'] = $enterpriseId;
                    $financialDocument['enterpriseName'] = $enterpriseName;
                    $this->financialDocumentModel->insertOrUpdateFinancialDocument($financialDocument);
                    $allfinancialDocuments[] = $financialDocument; // Добавление в массив
                }
            }
        }
        echo json_encode(
            [
                "message" => "Data synchronized successfully.",
                "financialDocuments" => $allfinancialDocuments,
            ],
            JSON_UNESCAPED_UNICODE
        );
    }
}