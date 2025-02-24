<?php
namespace Model;

use PDO;

class EnterpriseAccountPlansModel extends Database
{
    public function AddEnterpriseAccountPlan($eGRPOUId, $code, $name, $type, $non_balance, $quantity, $currency, $accrued_or_recognized, $vat_purpose, $accrued_amount, $subaccount1, $subaccount2, $subaccount3, $is_deleted)
    {
        $query = "INSERT INTO EnterpriseAccountPlans (eGRPOUId, code, name, type, non_balance, quantity, currency, accrued_or_recognized, vat_purpose, accrued_amount, subaccount1, subaccount2, subaccount3, is_deleted) 
                  VALUES (:eGRPOUId, :code, :name, :type, :non_balance, :quantity, :currency, :accrued_or_recognized, :vat_purpose, :accrued_amount, :subaccount1, :subaccount2, :subaccount3, :is_deleted)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':eGRPOUId', $eGRPOUId);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':non_balance', $non_balance);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':currency', $currency);
        $stmt->bindParam(':accrued_or_recognized', $accrued_or_recognized);
        $stmt->bindParam(':vat_purpose', $vat_purpose);
        $stmt->bindParam(':accrued_amount', $accrued_amount);
        $stmt->bindParam(':subaccount1', $subaccount1);
        $stmt->bindParam(':subaccount2', $subaccount2);
        $stmt->bindParam(':subaccount3', $subaccount3);
        $stmt->bindParam(':is_deleted', $is_deleted);

        $result = $stmt->execute();
        $lastInsertedId = $this->conn->lastInsertId();

        // Очистка кэша при добавлении новой записи
        $this->clearCache($eGRPOUId);

        return [$result, $lastInsertedId];
    }


    public function GetEnterpriseAccountPlansByEGRPOU($eGRPOUId)
    {
        // Генерация ключа для кэширования
        $cacheKey = 'enterprise_account_plans_' . $eGRPOUId;

        // Проверка наличия кэшированных данных
        $cachedData = $this->redis->get($cacheKey);

        // Если данные есть в кэше, возвращаем их
        if ($cachedData !== null) {
            $cachedResult = json_decode($cachedData, true);

            // Проверяем, удалось ли корректно декодировать данные из JSON
            if ($cachedResult !== null) {
                // var_dump('Cache hit for key ' . $cacheKey, $cachedResult); // Дамп данных в консоль
                return $cachedResult;
            } else {
                error_log('Failed to decode cached data for key ' . $cacheKey); // Дебаг-сообщение
            }
        }

        // Если данных нет в кэше, выполняем запрос к базе данных
        $query = "SELECT * FROM EnterpriseAccountPlans WHERE eGRPOUId = :eGRPOUId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':eGRPOUId', $eGRPOUId);
        $stmt->execute();

        // Получение результата запроса
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // Сохранение результата в кэше
        $jsonResult = json_encode($result);
        $this->redis->set($cacheKey, $jsonResult);

        // Возвращаем результат
        return $result;
    }

    public function DeleteEnterpriseAccountPlan($id, $eGRPOUId)
    {
        $query = "DELETE FROM EnterpriseAccountPlans WHERE id = :id AND eGRPOUId = :eGRPOUId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':eGRPOUId', $eGRPOUId);

        $result = $stmt->execute();

        $this->clearCache($eGRPOUId);

        return $result;
    }

    public function UpdateEnterpriseAccountPlan($id, $eGRPOUId, $code, $name, $type, $non_balance, $quantity, $currency, $accrued_or_recognized, $vat_purpose, $accrued_amount, $subaccount1, $subaccount2, $subaccount3, $is_deleted)
    {
        $query = "UPDATE EnterpriseAccountPlans 
                  SET eGRPOUId = :eGRPOUId, 
                      code = :code, 
                      name = :name, 
                      type = :type, 
                      non_balance = :non_balance, 
                      quantity = :quantity, 
                      currency = :currency, 
                      accrued_or_recognized = :accrued_or_recognized, 
                      vat_purpose = :vat_purpose, 
                      accrued_amount = :accrued_amount, 
                      subaccount1 = :subaccount1, 
                      subaccount2 = :subaccount2, 
                      subaccount3 = :subaccount3, 
                      is_deleted = :is_deleted 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':eGRPOUId', $eGRPOUId);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':non_balance', $non_balance);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':currency', $currency);
        $stmt->bindParam(':accrued_or_recognized', $accrued_or_recognized);
        $stmt->bindParam(':vat_purpose', $vat_purpose);
        $stmt->bindParam(':accrued_amount', $accrued_amount);
        $stmt->bindParam(':subaccount1', $subaccount1);
        $stmt->bindParam(':subaccount2', $subaccount2);
        $stmt->bindParam(':subaccount3', $subaccount3);
        $stmt->bindParam(':is_deleted', $is_deleted);

        $result = $stmt->execute();

        $this->clearCache($eGRPOUId);

        return $result;
    }

    private function clearCache($eGRPOUId)
    {
        $cacheKey = 'enterprise_account_plans_' . $eGRPOUId;

        $this->redis->del($cacheKey);
    }
}