<?php

namespace Model;

use PDOException;
use PDO;

class ContactModel extends Database
{

    public function __construct($conn, $redis)
    {
        parent::__construct($conn, $redis);
    }
    // Метод для создания нового контрагента с кешированием
    public function createContact($first_name, $last_name, $second_name, $title, $department, $account_id, $email, $phone_mobile, $phone_work, $phone_other, $birthdate, $description, $created_by, $assigned_to_user_id)
    {
        try {
            $query = "INSERT INTO Contacts (first_name, last_name, second_name, title, department, account_id, email, phone_mobile, phone_work, phone_other, birthdate, description, created_by, assigned_to_user_id) 
            VALUES (:first_name, :last_name, :second_name, :title, :department, :account_id, :email, :phone_mobile, :phone_work, :phone_other, :birthdate, :description, :created_by, :assigned_to_user_id)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':second_name', $second_name);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':department', $department);
            $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone_mobile', $phone_mobile);
            $stmt->bindParam(':phone_work', $phone_work);
            $stmt->bindParam(':phone_other', $phone_other);
            $stmt->bindParam(':birthdate', $birthdate);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':created_by', $created_by, PDO::PARAM_INT);
            $stmt->bindParam(':assigned_to_user_id', $assigned_to_user_id, PDO::PARAM_INT);
            $stmt->execute();

            $contactId = $this->conn->lastInsertId();

            return $contactId;
        } catch (PDOException $e) {
            echo ($e);
            return false;
        }
    }

    // Метод для создания связи между контрагентом и компанией
    public function linkContactToCompany($companyId, $contactId)
    {
        try {
            $query = "INSERT INTO CompanyContacts (companyId, contact_id) VALUES (:companyId, :contactId)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->bindParam(':contactId', $contactId, PDO::PARAM_INT);
            $stmt->execute();
            $this->clearCompanyContactsCache($companyId);

            return true;
        } catch (PDOException $e) {
            // Обработка ошибки
            return false;
        }
    }

    public function linkContactToAccount($accountId, $contactId)
    {
        try {
            $query = "INSERT INTO AccountContacts (account_id, contact_id) VALUES (:accountId, :contactId)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':accountId', $accountId, PDO::PARAM_INT);
            $stmt->bindParam(':contactId', $contactId, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            // Обработка ошибки
            return false;
        }
    }

    // Метод для создания связи между контактов и пользвателей
    public function linkContactToUser($userId, $contactId)
    {
        try {
            $query = "INSERT INTO UserContacts (contact_id, user_id) VALUES (:contact_id, :user_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':contact_id', $contactId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            // Обработка ошибки
            return false;
        }
    }

    public function getContactsByCompany($companyId)
    {
        $cacheKey = "company_contacts_{$companyId}";
        $cachedContacts = $this->redis->get($cacheKey);

        if ($cachedContacts) {
            return unserialize($cachedContacts);
        } else {
            try {

                $query = "SELECT C.*, U.username AS assigned_to_user_name, A.name AS account_name
                            FROM Contacts C
                            INNER JOIN CompanyContacts CC ON C.id = CC.contact_id
                            INNER JOIN Users U ON C.assigned_to_user_id = U.id
                            INNER JOIN Accounts A ON C.account_id = A.id
                            WHERE CC.companyId = :companyId ORDER BY deleted DESC";

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
                $stmt->execute();
                $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($contacts) {
                    $this->redis->setex($cacheKey, 3600, serialize($contacts));
                    return $contacts;
                } else {
                    echo "You don't have any records.";
                    return []; // Return an empty array or handle as needed.
                }
            } catch (PDOException $e) {
                // Обработка ошибки
                echo ($e);
                return [];
            }
        }
    }

    // Получить инфо о контакте по ид из своей компании
    public function getContactByCompanyAndId($companyId, $contactId)
    {
        $query = "SELECT C.*, U.username AS user_name 
                  FROM Contacts C
                  INNER JOIN CompanyContacts CC ON C.id = CC.contact_id
                  INNER JOIN Users U ON C.assigned_to_user_id = U.id
               WHERE CC.companyId = :companyId AND C.id = :contactId ORDER BY deleted DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
        $stmt->bindParam(':contactId', $contactId, PDO::PARAM_INT);
        $stmt->execute();
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($contact) {
            return $contact;
        } else {
            return null;
        }
    }

    // Получить инфо о контакте по ид из своей компании перед возвратом с удаленних, 
    // нельзя вернуть если удален поставщик
    public function getContactForRestoration($companyId, $contactId)
    {
        $query = "SELECT C.*, U.username AS user_name 
                    FROM Contacts C
                    INNER JOIN CompanyContacts CC ON C.id = CC.contact_id
                    INNER JOIN Users U ON C.assigned_to_user_id = U.id
                    INNER JOIN Accounts A ON C.account_id = A.id AND A.deleted <> 1
                    WHERE CC.companyId = :companyId AND C.id = :contactId 
                    ORDER BY C.deleted DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
        $stmt->bindParam(':contactId', $contactId, PDO::PARAM_INT);
        $stmt->execute();
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($contact) {
            return $contact;
        } else {
            return null;
        }
    }

    public function getContactByCompanyAndAccountId($accountId)
    {
        $query = "SELECT C.id AS contact_id 
                  FROM Contacts C
                  INNER JOIN CompanyContacts CC ON C.id = CC.contact_id
                  INNER JOIN AccountContacts AC ON C.id = AC.contact_id
                  INNER JOIN Users U ON C.assigned_to_user_id = U.id
                  WHERE AC.account_id = :account_id AND C.deleted = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':account_id', $accountId, PDO::PARAM_INT);
        $stmt->execute();
        $contact = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($contact) {
            return $contact;
        } else {
            return null;
        }
    }

    public function deleteContactById($companyId, $contactId)
    {
        try {
            // Удаляем контрагента из таблицы Accounts по его идентификатору
            $query = "UPDATE Contacts SET deleted = 1 WHERE id = :contactId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':contactId', $contactId, PDO::PARAM_INT);
            $stmt->execute();

            // Очищаем кеш, если он используется для этого контрагента
            $this->clearCompanyContactsCache($companyId);

            return $contactId; // Успешно удалено
        } catch (PDOException $e) {
            // Обработка ошибок при удалении контрагента
            echo ($e);
            return false;
        }
    }

    public function restoreContactById($companyId, $contactId)
    {
        try {
            // Удаляем контрагента из таблицы Accounts по его идентификатору
            $query = "UPDATE Contacts SET deleted = 0 WHERE id = :contactId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':contactId', $contactId, PDO::PARAM_INT);
            $stmt->execute();

            // Очищаем кеш, если он используется для этого контрагента
            $this->clearCompanyContactsCache($companyId);

            return $contactId; // Успешно удалено
        } catch (PDOException $e) {
            // Обработка ошибок при удалении контрагента
            echo ($e);
            return false;
        }
    }

    // Обновить контакт
    public function updateContact(
        $contactId,
        $first_name,
        $second_name,
        $last_name,
        $title,
        $department,
        $account_id,
        $email,
        $phone_mobile,
        $phone_work,
        $phone_other,
        $birthdate,
        $description,
        $assigned_to_user_id,
        $update_user,
        $companyId
    ) {
        try {
            $query = "UPDATE Contacts SET 
                first_name = :first_name,
                second_name = :second_name,
                last_name = :last_name,
                title = :title,
                department = :department,
                account_id = :account_id,
                email = :email,
                phone_mobile = :phone_mobile,
                phone_work = :phone_work,
                phone_other = :phone_other,
                birthdate = :birthdate,
                description = :description,
                assigned_to_user_id = :assigned_to_user_id,
                update_user = :update_user,
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            $stmt->bindValue(':first_name', $first_name);
            $stmt->bindValue(':second_name', $second_name);
            $stmt->bindValue(':last_name', $last_name);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':department', $department);
            $stmt->bindValue(':account_id', $account_id, PDO::PARAM_INT);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':phone_mobile', $phone_mobile);
            $stmt->bindValue(':phone_work', $phone_work);
            $stmt->bindValue(':phone_other', $phone_other);
            $stmt->bindValue(':birthdate', $birthdate);
            $stmt->bindValue(':description', $description);
            $stmt->bindValue(':assigned_to_user_id', $assigned_to_user_id, PDO::PARAM_INT);
            $stmt->bindValue(':update_user', $update_user, PDO::PARAM_INT);
            $stmt->bindValue(':id', $contactId, PDO::PARAM_INT);

            $stmt->execute();
            $this->clearCompanyContactsCache($companyId);

            return true; // Успешно обновлено
        } catch (PDOException $e) {
            echo ($e);
            error_log("Error updating contact: " . $e->getMessage());
            // Обработка ошибок при обновлении
            return false;
        }
    }


    // Добавить методы для очистки кеша, если необходимо
    public function clearCompanyContactsCache($companyId)
    {
        $cacheKey = "company_contacts_{$companyId}";
        $this->redis->del($cacheKey);
    }
}
//     // Добавить методы для кеширования других данных, если необходимо
//     private function cacheAccountInfo($accountId)
//     {
//         $accountInfo = $this->getAccountById($accountId);

//         if ($accountInfo) {
//             $cacheKey = "account_info_{$accountId}";
//         }
//     }
// }