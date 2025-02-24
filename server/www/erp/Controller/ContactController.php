<?php

namespace Controller;

use Model\ContactModel;
use Middleware\AuthMiddleware;
use Model\UserModel;
use DateTime;

class ContactController
{
    private $contactModel;
    private $authMiddleware;
    private $userModel;
    public function __construct(ContactModel $contactModel, AuthMiddleware $authMiddleware, UserModel $userModel)
    {
        $this->contactModel = $contactModel;
        $this->authMiddleware = $authMiddleware;
        $this->userModel = $userModel;
    }

    public function createContact()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        if (!$this->userModel->hasPermission($userId, 'create_contact')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }
        if (!$this->userModel->getCompanyInfo($userId)) {
            http_response_code(403);
            echo json_encode(["error" => "You dont have signed company"]);
            return;
        }

        // Получение данных о контакте из запроса
        $data = json_decode(file_get_contents('php://input'), true);

        // Проверка наличия обязательных полей
        if (
            !isset($data['first_name']) ||
            !isset($data['last_name']) ||
            !isset($data['account_id']) ||
            !isset($data['assigned_to_user_id']) ||
            !isset($userId)
        ) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields."]);
            return;
        }
        // Check if birthdate exists and is not empty
        if (isset($data['birthdate']) && !empty($data['birthdate'])) {
            // Convert birthdate to DateTime object
            $birthdateDateTime = new DateTime($data['birthdate']);
            // Add one day
            $birthdateDateTime->modify('+1 day');
            // Format the DateTime object to MySQL format
            $birthdate = $birthdateDateTime->format('Y-m-d H:i:s');
            // Now $birthdate contains the date in MySQL format with one day added
            $data['birthdate'] = $birthdate;
        } else {
            // If birthdate is empty, set it to NULL
            $data['birthdate'] = NULL;
        }
        // Создание контакта
        $contactId = $this->contactModel->createContact(
            $data['first_name'],
            $data['last_name'],
            $data['second_name'] ?? NULL,
            $data['title'] ?? NULL,
            $data['department'] ?? NULL,
            $data['account_id'],
            $data['email'] ?? NULL,
            $data['phone_mobile'] ?? NULL,
            $data['phone_work'] ?? NULL,
            $data['phone_other'] ?? NULL,
            $data['birthdate'] ?? NULL,
            $data['description'] ?? NULL,
            intval($userId) ?? NULL,
            $data['assigned_to_user_id']
        );

        if ($contactId) {
            // Создание связи между контактом и предприятием
            $companyId = $companyInfo['details']['id'];
            $this->contactModel->linkContactToCompany($companyId, $contactId);

            // Проверяем наличие $accountId перед вызовом метода связывания контакта с контрагентом
            if (isset($data['account_id'])) {
                $this->contactModel->linkContactToAccount($data['account_id'], $contactId);
            }
            $this->contactModel->linkContactToUser($userId, $contactId);
            // Очистка кеша связанного с контактами для данной компании
            $this->contactModel->clearCompanyContactsCache($companyId);
            http_response_code(201);
            echo json_encode(["message" => "Contact created successfully.", "contactId" => $contactId]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to create contact."]);
        }
    }


    // Добавьте другие методы, например, для получения списка контрагентов, обновления контрагента и других операций.

    // Пример метода для получения списка контактов
    public function getContacts()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        // Проверка разрешений пользователя для получения списка контактов
        if (!$this->userModel->hasPermission($userId, 'get_contacts')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        // Получите список контактов из модели и верните его в формате JSON
        $contacts = $this->contactModel->getContactsByCompany($companyId);

        // Проверка на пустой результат
        if (empty($contacts)) {
            http_response_code(404);
            echo json_encode(["error" => "No records found."]);
            return;
        }

        http_response_code(200);
        echo json_encode($contacts);
    }


    // Получить инфу по ид контр агента только можно в своей компании
    public function getContactById()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        // Проверка разрешений пользователя для получения информации о контакте
        if (!$this->userModel->hasPermission($userId, 'get_contacts')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        // Получение идентификатора контакта из запроса
        $contactId = $_GET['contact_id'] ?? null;

        if (!$contactId) {
            http_response_code(400);
            echo json_encode(["error" => "Missing contact ID parameter."]);
            return;
        }

        // Получение информации о контакте по его идентификатору для указанной компании
        $contactInfo = $this->contactModel->getContactByCompanyAndId($companyId, $contactId);

        if ($contactInfo) {
            http_response_code(200);
            echo json_encode($contactInfo);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Contact not found for this company or invalid contact ID."]);
        }
    }


    // Удалить контакт по ИД
    public function delContactById()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        if (!$this->userModel->hasPermission($userId, 'del_contact')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        $contactIds = $input['contact_id'] ?? [];

        if (empty($contactIds) || !$userId) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid request parameters."]);
            return;
        }

        $successfullyDeleted = [];
        $failedToDelete = [];

        foreach ($contactIds as $contactId) {
            $accountInfo = $this->contactModel->getContactByCompanyAndId($companyId, $contactId);
            if ($accountInfo) {
                $deleted = $this->contactModel->deleteContactById($companyId, $contactId);

                if ($deleted) {
                    $successfullyDeleted[] = $contactId;
                } else {
                    $failedToDelete[] = $contactId;
                }
            } else {
                $failedToDelete[] = $contactId;
            }
        }
        if (empty($failedToDelete)) {
            $this->contactModel->clearCompanyContactsCache($companyId);

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

    // Восстановить контакт по ИД
    public function restoreContactById()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        if (!$this->userModel->hasPermission($userId, 'del_contact')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        $contactIds = $input['contact_id'] ?? [];

        if (empty($contactIds) || !$userId) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid request parameters."]);
            return;
        }

        $successfullyRestored = [];
        $failedToRestore = [];

        foreach ($contactIds as $contactId) {
            $accountInfo = $this->contactModel->getContactForRestoration($companyId, $contactId);
            if ($accountInfo) {
                $restored = $this->contactModel->restoreContactById($companyId, $contactId);

                if ($restored) {
                    $successfullyRestored[] = $contactId;
                } else {
                    $failedToRestore[] = $contactId;
                }
            } else {
                $failedToRestore[] = $contactId;
            }
        }
        if (empty($failedToRestore)) {
            $this->contactModel->clearCompanyContactsCache($companyId);

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


    public function deleteContactByAccountId()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        if (!$this->userModel->hasPermission($userId, 'del_contact')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        $accountIds = $input['account_ids'] ?? [];

        if (empty($accountIds) || !$userId) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid request parameters."]);
            return;
        }

        // Get all contacts related to accounts
        $contactsIdsForDeleting = [];
        foreach ($accountIds as $accountId) {
            $accountsContact = $this->contactModel->getContactByCompanyAndAccountId($accountId);
            if ($accountsContact) {
                $contactsIdsForDeleting = array_merge($contactsIdsForDeleting, $accountsContact);
            }
        }

        $successfullyDeleted = [];
        $failedToDelete = [];

        foreach ($contactsIdsForDeleting as $contactId) {
            $deleted = $this->contactModel->deleteContactById($companyId, $contactId['contact_id']);

            if ($deleted) {
                $successfullyDeleted[] = $contactId;
            } else {
                $failedToDelete[] = $contactId;
            }
        }
        if (empty($failedToDelete)) {
            $this->contactModel->clearCompanyContactsCache($companyId);

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


    // Обновить информацию о контрагенте по его ID
    public function updateContactById()
    {
        $userId = $this->userModel->getUserIdFromToken();
        $companyInfo = $this->userModel->getCompanyInfo($userId);
        $companyId = $companyInfo['details']['id'];

        if (!$this->userModel->hasPermission($userId, 'update_contact')) {
            http_response_code(403);
            echo json_encode(["error" => "Permission denied."]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $contactId = $data['contact_id'] ?? null;


        if (!$contactId) {
            http_response_code(400);
            echo json_encode(["error" => "Missing contact ID parameter."]);
            return;
        }

        $contactInfo = $this->contactModel->getContactByCompanyAndId($companyId, $contactId);

        if (!$contactInfo) {
            http_response_code(404);
            echo json_encode(["error" => "Contact not found for this company or invalid contact ID."]);
            return;
        }

        // Check if birthdate exists and is not empty
        if (isset($data['birthdate']) && !empty($data['birthdate']) && $data['birthdate'] !== null) {
            // Convert birthdate to DateTime object
            $birthdateDateTime = new DateTime($data['birthdate']);
            // Add one day
            // $birthdateDateTime->modify('+1 day');
            // Format the DateTime object to MySQL format
            $birthdate = $birthdateDateTime->format('Y-m-d H:i:s');
            // Now $birthdate contains the date in MySQL format with one day added
            $data['birthdate'] = $birthdate;
        } else {
            // If birthdate is empty, set it to NULL
            $data['birthdate'] = NULL;
        }


        if (isset($data['first_name'])) {
            $contactInfo['first_name'] = $data['first_name'];
        }
        if (isset($data['second_name'])) {
            $contactInfo['second_name'] = $data['second_name'];
        }
        if (isset($data['last_name'])) {
            $contactInfo['last_name'] = $data['last_name'];
        }
        if (isset($data['title'])) {
            $contactInfo['title'] = $data['title'];
        }
        if (isset($data['department'])) {
            $contactInfo['department'] = $data['department'];
        }
        if (isset($data['account_id'])) {
            $contactInfo['account_id'] = $data['account_id'];
        }
        if (isset($data['email'])) {
            $contactInfo['email'] = $data['email'];
        }
        if (isset($data['phone_mobile'])) {
            $contactInfo['phone_mobile'] = $data['phone_mobile'];
        }
        if (isset($data['phone_work'])) {
            $contactInfo['phone_work'] = $data['phone_work'];
        }
        if (isset($data['phone_other'])) {
            $contactInfo['phone_other'] = $data['phone_other'];
        }
        if (isset($data['birthdate'])) {
            $contactInfo['birthdate'] = $data['birthdate'];
        }
        if (isset($data['description'])) {
            $contactInfo['description'] = $data['description'];
        }
        if (isset($data['created_by'])) {
            $contactInfo['created_by'] = $data['created_by'];
        }
        if (isset($data['assigned_to_user_id'])) {
            $contactInfo['assigned_to_user_id'] = $data['assigned_to_user_id'];
        }

        // Вызываем метод модели для обновления информации о контакте
        $updated = $this->contactModel->updateContact(
            $contactId,
            $contactInfo['first_name'],
            $contactInfo['second_name'],
            $contactInfo['last_name'],
            $contactInfo['title'],
            $contactInfo['department'],
            $contactInfo['account_id'],
            $contactInfo['email'],
            $contactInfo['phone_mobile'],
            $contactInfo['phone_work'],
            $contactInfo['phone_other'],
            $contactInfo['birthdate'],
            $contactInfo['description'],
            $contactInfo['assigned_to_user_id'],
            $contactInfo['update_user'],
            $companyId
        );

        if ($updated) {
            http_response_code(200);
            echo json_encode(["message" => "Contact updated successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update contact."]);
        }
    }
}
