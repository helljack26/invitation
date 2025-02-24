<?php

namespace Model;

use PDO;
use PDOException;

class AccountsModel extends Database
{

    public function __construct($conn, $redis)
    {
        parent::__construct($conn, $redis);
    }
    // Метод для создания нового Постачальника с кешированием
    public function createAccount(
        $name,
        $industry,
        $website,
        $phone_office,
        $billing_address_street,
        $billing_address_city,
        $billing_address_state,
        $billing_address_postalcode,
        $billing_address_country,
        $shipping_address_street,
        $shipping_address_city,
        $shipping_address_state,
        $shipping_address_postalcode,
        $shipping_address_country,
        $description,
        $email,
        $id_user_Created
    ) {
        try {
            $query = "INSERT INTO Accounts (
                name,
                industry,
                website,
                phone_office,
                billing_address_street,
                billing_address_city,
                billing_address_state,
                billing_address_postalcode,
                billing_address_country,
                shipping_address_street,
                shipping_address_city,
                shipping_address_state,
                shipping_address_postalcode,
                shipping_address_country,
                description,
                email,
                id_user_created
            ) VALUES (
                :name,
                :industry,
                :website,
                :phone_office,
                :billing_address_street,
                :billing_address_city,
                :billing_address_state,
                :billing_address_postalcode,
                :billing_address_country,
                :shipping_address_street,
                :shipping_address_city,
                :shipping_address_state,
                :shipping_address_postalcode,
                :shipping_address_country,
                :description,
                :email,
                :id_user_Created
            )";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':industry', $industry);
            $stmt->bindParam(':website', $website);
            $stmt->bindParam(':phone_office', $phone_office);
            $stmt->bindParam(':billing_address_street', $billing_address_street);
            $stmt->bindParam(':billing_address_city', $billing_address_city);
            $stmt->bindParam(':billing_address_state', $billing_address_state);
            $stmt->bindParam(':billing_address_postalcode', $billing_address_postalcode);
            $stmt->bindParam(':billing_address_country', $billing_address_country);
            $stmt->bindParam(':shipping_address_street', $shipping_address_street);
            $stmt->bindParam(':shipping_address_city', $shipping_address_city);
            $stmt->bindParam(':shipping_address_state', $shipping_address_state);
            $stmt->bindParam(':shipping_address_postalcode', $shipping_address_postalcode);
            $stmt->bindParam(':shipping_address_country', $shipping_address_country);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id_user_Created', $id_user_Created);
            $stmt->execute();

            $accountId = $this->conn->lastInsertId();

            // Кеширование информации о новом Постачальнике в Redis
            return $accountId;
        } catch (PDOException $e) {
            // Обработка ошибок при создании Постачальника
            echo "Ошибка: " . $e->getMessage();
            return null;
        }
    }

    // Метод для создания связи между Постачальником и компанией
    public function linkAccountToCompany($companyId, $accountId)
    {
        try {
            // Ваш код для создания связи между Постачальником и предприятием
            $query = "INSERT INTO CompanyAccounts (companyId, accountId) VALUES (:companyId, :accountId)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->bindParam(':accountId', $accountId, PDO::PARAM_INT);
            $stmt->execute();
            $this->clearCompanyAccountsCache($companyId);

            return true;
        } catch (PDOException $e) {
            // Обработка ошибки
            return false;
        }
    }

    public function linkUserToAccount($userId, $accountId)
    {
        try {
            // Ваш код для создания связи между пользователем и Постачальником
            $query = "INSERT INTO AccountsUsers (user_id, account_id) VALUES (:userId, :accountId)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':accountId', $accountId, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            // Обработка ошибки
            return false;
        }
    }

    public function getAccountsByCompany($companyId)
    {
        $cacheKey = "company_accounts_{$companyId}";
        $cachedAccounts = $this->redis->get($cacheKey);

        if ($cachedAccounts) {
            return unserialize($cachedAccounts);
        } else {
            $query = "SELECT A.*, U.username AS user_name 
                      FROM Accounts A
                      INNER JOIN CompanyAccounts CA ON A.id = CA.accountId
                      INNER JOIN Users U ON A.id_user_Created = U.id
                      WHERE CA.companyId = :companyId ORDER BY A.deleted DESC"; // Добавлено условие для исключения удаленных Постачальников

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':companyId', $companyId);
            $stmt->execute();
            $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($accounts) {
                $this->redis->setex($cacheKey, 3600, serialize($accounts));
                return $accounts;
            } else {
                echo "You don't have any records.";
            }
        }
    }

    // Получить инфо о Постачальнике по ид из своей компании
    public function getAccountByCompanyAndId($companyId, $accountId)
    {
        $query = "SELECT A.*, U.username AS user_name 
                  FROM Accounts A
                  INNER JOIN CompanyAccounts CA ON A.id = CA.accountId
                  INNER JOIN Users U ON A.id_user_Created = U.id
                  WHERE CA.companyId = :companyId AND A.id = :accountId ORDER BY A.deleted DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':companyId', $companyId);
        $stmt->bindParam(':accountId', $accountId);
        $stmt->execute();
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($account) {
            return $account;
        } else {
            return null;
        }
    }

    public function deleteAccountById($accountIds, $userId)
    {
        try {
            if (!is_array($accountIds)) {
                $accountIds = [$accountIds];
            }

            // Проверка, что $accountIds является массивом
            if (count($accountIds) > 0) {
                $placeholders = implode(',', array_fill(0, count($accountIds), '?'));

                // Обновляем записи в таблице Accounts, устанавливаем флаг deleted=1 и записываем идентификатор пользователя, который удалил
                $query = "UPDATE Accounts SET deleted = 1, user_delete = ? WHERE id IN ($placeholders)";
                $stmt = $this->conn->prepare($query);

                // Добавляем идентификатор пользователя как параметр для prepared statement
                $stmt->bindValue(1, $userId, PDO::PARAM_INT);
                foreach ($accountIds as $index => $accountId) {
                    $stmt->bindValue($index + 2, $accountId, PDO::PARAM_INT);
                }

                $stmt->execute();

                return true; // Успешно удалено
            } else {
                // Вернуть false, если $accountIds не является массивом
                return false;
            }
        } catch (PDOException $e) {
            // Обработка ошибок при удалении Постачальников
            return false;
        }
    }

    public function restoreAccountById($accountIds, $userId)
    {
        try {
            if (!is_array($accountIds)) {
                $accountIds = [$accountIds];
            }

            // Проверка, что $accountIds является массивом
            if (count($accountIds) > 0) {
                $placeholders = implode(',', array_fill(0, count($accountIds), '?'));

                // Обновляем записи в таблице Accounts, устанавливаем флаг deleted=1 и записываем идентификатор пользователя, который удалил
                $query = "UPDATE Accounts SET deleted = 0, user_restore = ? WHERE id IN ($placeholders)";
                $stmt = $this->conn->prepare($query);

                // Добавляем идентификатор пользователя как параметр для prepared statement
                $stmt->bindValue(1, $userId, PDO::PARAM_INT);
                foreach ($accountIds as $index => $accountId) {
                    $stmt->bindValue($index + 2, $accountId, PDO::PARAM_INT);
                }

                $stmt->execute();

                return true; // Успешно удалено
            } else {
                // Вернуть false, если $accountIds не является массивом
                return false;
            }
        } catch (PDOException $e) {
            // Обработка ошибок при удалении Постачальников
            return false;
        }
    }



    // Обнвление
    public function updateAccount(
        $accountId,
        $name,
        $industry,
        $website,
        $phone_office,
        $billing_address_street,
        $billing_address_city,
        $billing_address_state,
        $billing_address_postalcode,
        $billing_address_country,
        $shipping_address_street,
        $shipping_address_city,
        $shipping_address_state,
        $shipping_address_postalcode,
        $shipping_address_country,
        $description,
        $assigned_to_user_id,
        $email,
        $update_user // Новый параметр для отслеживания пользователя, внесшего изменения
    ) {
        try {
            $params = array(
                'name' => $name,
                'industry' => $industry,
                'website' => $website,
                'phone_office' => $phone_office,
                'billing_address_street' => $billing_address_street,
                'billing_address_city' => $billing_address_city,
                'billing_address_state' => $billing_address_state,
                'billing_address_postalcode' => $billing_address_postalcode,
                'billing_address_country' => $billing_address_country,
                'shipping_address_street' => $shipping_address_street,
                'shipping_address_city' => $shipping_address_city,
                'shipping_address_state' => $shipping_address_state,
                'shipping_address_postalcode' => $shipping_address_postalcode,
                'shipping_address_country' => $shipping_address_country,
                'description' => $description,
                'assigned_to_user_id' => $assigned_to_user_id,
                'email' => $email,
                'update_user' => $update_user
            );
            $query = "UPDATE Accounts SET ";
            foreach ($params as $key => $value) {
                $query .= "$key = :$key, ";
            }
            $query .= "updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->bindParam(':id', $accountId, PDO::PARAM_INT);

            $stmt->execute();

            return true; // Успешно обновлено
        } catch (PDOException $e) {
            error_log("Error updating account: " . $e->getMessage());
            // Обработка ошибок при обновлении
            return false;
        }
    }

    // Добавить методы для очистки кеша, если необходимо
    public function clearCompanyAccountsCache($companyId)
    {
        $cacheKey = "company_accounts_{$companyId}";
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