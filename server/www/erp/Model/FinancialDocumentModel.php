<?php
namespace Model;

use PDO;

class FinancialDocumentModel extends Database
{
    public function getFinancialDocumentsByEnterprise($enterpriseId, $startDate = null, $endDate = null)
    {
        $query = "SELECT * FROM FinancialDocuments WHERE enterpriseId = :enterpriseId";
        $conditions = [];
        $params = [":enterpriseId" => $enterpriseId];

        if ($startDate) {
            $conditions[] = "theDate >= :startDate";
            $params[':startDate'] = $startDate;
        }

        if ($endDate) {
            $conditions[] = "theDate <= :endDate";
            $params[':endDate'] = $endDate;
        }

        if (!empty ($conditions)) {
            $query .= " AND " . implode(' AND ', $conditions);
        }


        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);



        return $result;
    }


    public function insertOrUpdateFinancialDocument($financialDocument)
    {
        $cacheKey = "financialDocument_" . $financialDocument['financialDocumentId'];
        if ($this->redis->exists($cacheKey)) {
            $cachedData = json_decode($this->redis->get($cacheKey), true);
            return $cachedData;
        }

        $stmt = $this->conn->prepare("SELECT financialDocumentId FROM FinancialDocuments WHERE financialDocumentId = :financialDocumentId");
        $stmt->bindParam(':financialDocumentId', $financialDocument['financialDocumentId']);
        $stmt->execute();

        if ($stmt->fetch()) {
            $updateQuery = "UPDATE FinancialDocuments SET 
                                theDate = :theDate, number = :number, sumMoney = :sumMoney, 
                                moveCategory = :moveCategory, theType = :theType, 
                                partnerName = :partnerName, partnerId = :partnerId, 
                                currencyId = :currencyId,
                                analisysChipherId = :analisysChipherId,
                                analysisChipherName = :analysisChipherName,
                                cashRegisterId = :cashRegisterId,
                                accountId = :accountId,
                                enterpriseId = :enterpriseId, -- Добавлено поле для идентификатора предприятия
                                comment = :comment
                                WHERE financialDocumentId = :financialDocumentId";

            $stmt = $this->conn->prepare($updateQuery);
        } else {
            $insertQuery = "INSERT INTO FinancialDocuments 
                                (financialDocumentId, theDate, number, sumMoney, moveCategory, theType, partnerName, partnerId, currencyId, analisysChipherId, analysisChipherName, cashRegisterId, accountId, enterpriseId, comment) 
                                VALUES (:financialDocumentId, :theDate, :number, :sumMoney, :moveCategory, :theType, :partnerName, :partnerId, :currencyId, :analisysChipherId, :analysisChipherName, :cashRegisterId, :accountId, :enterpriseId, :comment)";

            $stmt = $this->conn->prepare($insertQuery);
        }

        $stmt->bindParam(':financialDocumentId', $financialDocument['financialDocumentId']);
        $stmt->bindParam(':theDate', $financialDocument['theDate']);
        $stmt->bindParam(':number', $financialDocument['number']);
        $stmt->bindParam(':sumMoney', $financialDocument['sumMoney']);
        $stmt->bindParam(':moveCategory', $financialDocument['moveCategory']);
        $stmt->bindParam(':theType', $financialDocument['theType']);
        $stmt->bindParam(':partnerName', $financialDocument['partner']['name']);
        $stmt->bindParam(':partnerId', $financialDocument['partner']['partnerId']);
        $stmt->bindParam(':currencyId', $financialDocument['currencyId']);
        $stmt->bindParam(':analisysChipherId', $financialDocument['analysisCipher']['analisysChipherId']);
        $stmt->bindParam(':analysisChipherName', $financialDocument['analysisCipher']['name']);
        $stmt->bindParam(':cashRegisterId', $financialDocument['cashRegisterId']);
        $stmt->bindParam(':accountId', $financialDocument['accountId']);
        $stmt->bindParam(':enterpriseId', $financialDocument['enterpriseId']); // Привязка к полю enterpriseId
        $stmt->bindParam(':comment', $financialDocument['comment']);

        $result = $stmt->execute();

        if ($result) {
            $this->redis->setex($cacheKey, 3600, json_encode($financialDocument));
        }

        return $result;
    }
}