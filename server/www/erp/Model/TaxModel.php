<?php
namespace Model;

use PDO;
use DateTime;

class TaxModel extends Database
{
    public function getFOP2_TaxesByEnterprise($eGRPOUId, $startDate = null, $endDate = null)
    {
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);

        $startYear = $startDateTime->format('Y');
        $startMonth = $startDateTime->format('n');
        $endYear = $endDateTime->format('Y');
        $endMonth = $endDateTime->format('n');

        // Calculate the start and end quarter values
        $startQuarter = ceil($startMonth / 3);
        $endQuarter = ceil($endMonth / 3);

        $query = "SELECT * FROM FOP2_Taxes WHERE eGRPOUId = :eGRPOUId 
                  AND Year BETWEEN :startYear AND :endYear 
                  AND Quarter BETWEEN :startQuarter AND :endQuarter";

        $params = [
            ":eGRPOUId" => intval($eGRPOUId),
            ":startYear" => $startYear,
            ":endYear" => $endYear,
            ":startQuarter" => $startQuarter,
            ":endQuarter" => $endQuarter
        ];

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val, PDO::PARAM_INT);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function insertOrUpdateDocumentTax($document)
    {
        $tableName = 'FOP2_Taxes';
        $cacheKey = "FOP2_Taxes_id_" . $document['FOP2_Taxes_id'];
        // Проверяем наличие FOP2_Taxes_id
        if (isset ($document['FOP2_Taxes_id']) && !empty ($document['FOP2_Taxes_id'])) {
            // Выполняем обновление
            $updateQuery = "UPDATE $tableName SET 
                        Month = :Month, Quarter = :Quarter, Year = :Year, 
                        Default_ZP = :Default_ZP, eGRPOUId  = :eGRPOUId, 
                        EN_Tax_Rate = :EN_Tax_Rate, ESV_Rate = :ESV_Rate
                        WHERE FOP2_Taxes_id = :FOP2_Taxes_id";

            $stmt = $this->conn->prepare($updateQuery);
            // Привязка параметров запроса к полям таблицы
            $stmt->bindParam(':FOP2_Taxes_id', $document['FOP2_Taxes_id']);
        } else {
            // Выполняем вставку новой записи
            $insertQuery = "INSERT INTO $tableName 
                        (Month, Quarter, Year, Default_ZP, eGRPOUId, EN_Tax_Rate, ESV_Rate) 
                        VALUES (:Month, :Quarter, :Year, :Default_ZP, :eGRPOUId, :EN_Tax_Rate, :ESV_Rate)";

            $stmt = $this->conn->prepare($insertQuery);
        }

        // Привязка параметров запроса к полям таблицы
        $stmt->bindParam(':Month', $document['Month']);
        $stmt->bindParam(':Quarter', $document['Quarter']);
        $stmt->bindParam(':Year', $document['Year']);
        $stmt->bindParam(':Default_ZP', $document['Default_ZP']);
        $stmt->bindParam(':eGRPOUId', $document['eGRPOUId']);
        $stmt->bindParam(':EN_Tax_Rate', $document['EN_Tax_Rate']);
        $stmt->bindParam(':ESV_Rate', $document['ESV_Rate']);

        // Если это обновление, то еще раз привязываем параметр
        if (isset ($document['FOP2_Taxes_id']) && !empty ($document['FOP2_Taxes_id'])) {
            $stmt->bindParam(':FOP2_Taxes_id', $document['FOP2_Taxes_id']);
        }

        // Выполнение запроса и обработка результатов
        $result = $stmt->execute();

        if ($result) {
            // Обновляем кеш, если запрос успешен
            $this->redis->setex($cacheKey, 3600, json_encode($document));
        }
        $errorInfo = $stmt->errorInfo();
        print_r($errorInfo);
        return $result;
    }

    public function clearDocumentTaxCache($document)
    {
        if (isset ($document['FOP2_Taxes_id'])) {

            $cacheKey = "FOP2_Taxes_id_" . $document['FOP2_Taxes_id'];
            $this->redis->del($cacheKey);
        }
    }

    public function getFOP3_TaxesByEnterprise($eGRPOUId, $startDate = null, $endDate = null)
    {
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);

        $startYear = $startDateTime->format('Y');
        $startMonth = $startDateTime->format('n');
        $endYear = $endDateTime->format('Y');
        $endMonth = $endDateTime->format('n');

        // Calculate the start and end quarter values
        $startQuarter = ceil($startMonth / 3);
        $endQuarter = ceil($endMonth / 3);

        $query = "SELECT * FROM FOP3_Taxes WHERE eGRPOUId = :eGRPOUId 
                    AND Year BETWEEN :startYear AND :endYear 
                    AND Quarter BETWEEN :startQuarter AND :endQuarter";

        $params = [
            ":eGRPOUId" => $eGRPOUId,
            ":startYear" => $startYear,
            ":endYear" => $endYear,
            ":startQuarter" => $startQuarter,
            ":endQuarter" => $endQuarter
        ];

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val, PDO::PARAM_INT);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function insertOrUpdateDocumentTax3($document)
    {
        $tableName = 'FOP3_Taxes';
        $cacheKey = "FOP3_Taxes_id_" . $document['FOP3_Taxes_id'];
        // Проверяем наличие FOP3_Taxes_id
        if (isset ($document['FOP3_Taxes_id']) && !empty ($document['FOP3_Taxes_id'])) {
            // Выполняем обновление
            $updateQuery = "UPDATE $tableName SET 
                        Month = :Month, Quarter = :Quarter, Year = :Year, 
                        Default_ZP = :Default_ZP, eGRPOUId = :eGRPOUId, 
                        EN_Tax_Rate = :EN_Tax_Rate, ESV_Rate = :ESV_Rate
                        WHERE FOP3_Taxes_id = :FOP3_Taxes_id";

            $stmt = $this->conn->prepare($updateQuery);
            // Привязка параметров запроса к полям таблицы
            $stmt->bindParam(':FOP3_Taxes_id', $document['FOP3_Taxes_id']);
        } else {
            // Выполняем вставку новой записи
            $insertQuery = "INSERT INTO $tableName 
                        (Month, Quarter, Year, Default_ZP, eGRPOUId, EN_Tax_Rate, ESV_Rate) 
                        VALUES (:Month, :Quarter, :Year, :Default_ZP, :eGRPOUId, :EN_Tax_Rate, :ESV_Rate)";

            $stmt = $this->conn->prepare($insertQuery);
        }

        // Привязка параметров запроса к полям таблицы
        $stmt->bindParam(':Month', $document['Month']);
        $stmt->bindParam(':Quarter', $document['Quarter']);
        $stmt->bindParam(':Year', $document['Year']);
        $stmt->bindParam(':Default_ZP', $document['Default_ZP']);
        $stmt->bindParam(':eGRPOUId', $document['eGRPOUId']);
        $stmt->bindParam(':EN_Tax_Rate', $document['EN_Tax_Rate']);
        $stmt->bindParam(':ESV_Rate', $document['ESV_Rate']);

        // Если это обновление, то еще раз привязываем параметр
        if (isset ($document['FOP3_Taxes_id']) && !empty ($document['FOP3_Taxes_id'])) {
            $stmt->bindParam(':FOP3_Taxes_id', $document['FOP3_Taxes_id']);
        }

        // Выполнение запроса и обработка результатов
        $result = $stmt->execute();
        // Перевірка на помилки
        if ($result === false) {
            $errorInfo = $stmt->errorInfo();
            http_response_code(500);
            echo json_encode(['error' => 'Error executing query', 'details' => $errorInfo]);
            return;
        }

        // Перевірка на кількість змінених рядків (для UPDATE та INSERT)
        $affectedRows = $stmt->rowCount();
        if ($affectedRows === 0) {
            http_response_code(500);
            echo json_encode(['error' => 'No rows affected']);
            return;
        }

        if ($result) {
            // Обновляем кеш, если запрос успешен
            $this->redis->setex($cacheKey, 3600, json_encode($document));
        }

        return $result;
    }

    public function clearDocumentTaxCache3($document)
    {
        if (isset ($document['FOP3_Taxes_id'])) {

            $cacheKey = "FOP3_Taxes_id_" . $document['FOP3_Taxes_id'];
            $this->redis->del($cacheKey);
        }
    }
}