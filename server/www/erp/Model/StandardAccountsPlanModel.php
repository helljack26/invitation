<?php
namespace Model;

use PDO;

class StandardAccountsPlanModel extends Database
{
    public function AddStandardAccountsPlan($code, $name, $type, $nonBalance, $quantity, $currency, $accruedOrRecognized, $vatPurpose, $accruedAmount, $subaccount1, $subaccount2, $subaccount3, $isDeleted)
    {
        $query = "INSERT INTO StandardAccountsPlanTable 
            (code, name, type, non_balance, quantity, currency, accrued_or_recognized, vat_purpose, accrued_amount, subaccount1, subaccount2, subaccount3, is_deleted) 
            VALUES (:code, :name, :type, :nonBalance, :quantity, :currency, :accruedOrRecognized, :vatPurpose, :accruedAmount, :subaccount1, :subaccount2, :subaccount3, :isDeleted)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':nonBalance', $nonBalance);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':currency', $currency);
        $stmt->bindParam(':accruedOrRecognized', $accruedOrRecognized);
        $stmt->bindParam(':vatPurpose', $vatPurpose);
        $stmt->bindParam(':accruedAmount', $accruedAmount);
        $stmt->bindParam(':subaccount1', $subaccount1);
        $stmt->bindParam(':subaccount2', $subaccount2);
        $stmt->bindParam(':subaccount3', $subaccount3);
        $stmt->bindParam(':isDeleted', $isDeleted);

        $result = $stmt->execute();

        // Получение последнего вставленного ID
        $lastInsertedId = $this->conn->lastInsertId();

        // Очистка кэша при добавлении новой записи
        $this->clearCache();

        return [$result, $lastInsertedId];
    }


    public function GetAllStandardAccountsPlan()
    {
        // Проверка кэша
        $cacheKey = 'all_standard_account_plan';
        if ($this->redis->exists($cacheKey)) {
            $cachedData = json_decode($this->redis->get($cacheKey), true);
            return $cachedData;
        }

        // Запрос в базу данных, если кэш отсутствует
        $query = "SELECT * FROM StandardAccountsPlanTable";
        $stmt = $this->conn->query($query);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Сохранение результатов в кэше
        $this->redis->set($cacheKey, json_encode($result));

        return $result;
    }


    public function DeleteStandardAccountsPlan($id)
    {
        // Запрос на удаление записи с указанным идентификатором из таблицы StandardAccountsPlanTable
        $query = "DELETE FROM StandardAccountsPlanTable WHERE id = :id";

        // Подготовка запроса
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        // Выполнение запроса на удаление
        $result = $stmt->execute();

        // Очистка кэша при удалении записи
        $this->clearCache();

        return $result;
    }


    public function UpdateStandardAccountsPlan($id, $code, $name, $type, $nonBalance, $quantity, $currency, $accruedOrRecognized, $vatPurpose, $accruedAmount, $subaccount1, $subaccount2, $subaccount3, $isDeleted)
    {
        $query = "UPDATE StandardAccountsPlanTable 
              SET code = :code, 
                  name = :name, 
                  type = :type, 
                  non_balance = :nonBalance, 
                  quantity = :quantity, 
                  currency = :currency, 
                  accrued_or_recognized = :accruedOrRecognized, 
                  vat_purpose = :vatPurpose, 
                  accrued_amount = :accruedAmount, 
                  subaccount1 = :subaccount1, 
                  subaccount2 = :subaccount2, 
                  subaccount3 = :subaccount3, 
                  is_deleted = :isDeleted 
              WHERE id = :id";

        // Подготовка запроса
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':nonBalance', $nonBalance);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':currency', $currency);
        $stmt->bindParam(':accruedOrRecognized', $accruedOrRecognized);
        $stmt->bindParam(':vatPurpose', $vatPurpose);
        $stmt->bindParam(':accruedAmount', $accruedAmount);
        $stmt->bindParam(':subaccount1', $subaccount1);
        $stmt->bindParam(':subaccount2', $subaccount2);
        $stmt->bindParam(':subaccount3', $subaccount3);
        $stmt->bindParam(':isDeleted', $isDeleted);

        // Выполнение запроса на обновление
        $result = $stmt->execute();

        // Очистка кэша при обновлении записи
        $this->clearCache();

        return $result;
    }
    private function clearCache()
    {
        // Очистка кэша
        $this->redis->del('all_standard_account_plan');
    }
}