<?php
namespace Model;

use PDOException;
use PDO;

class EnterpriseModel extends Database
{

    // Метод для добавления нового предприятия
    public function addEnterprise($name, $eGRPOU, $individualTaxNumber)
    {
        $query = "INSERT INTO Enterprises (name, eGRPOU, individualTaxNumber) VALUES (:name, :eGRPOU, :individualTaxNumber)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':eGRPOU', $eGRPOU);
        $stmt->bindParam(':individualTaxNumber', $individualTaxNumber);
        $stmt->execute();
    }

    // Метод для получения информации о предприятии по ID
    public function getEnterpriseById($enterpriseId)
    {
        $query = "SELECT * FROM Enterprises WHERE enterpriseId = :enterpriseId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':enterpriseId', $enterpriseId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addOrUpdateEnterprise($name, $eGRPOU, $individualTaxNumber)
    {
        // Перевіряємо, чи існує предприємство з заданим eGRPOU
        $existingEnterprise = $this->getEnterpriseByEGRPOU($eGRPOU);

        if ($existingEnterprise) {
            // Якщо предприємство існує, виконуємо оновлення
            $this->updateEnterprise($existingEnterprise['enterpriseId'], $name, $individualTaxNumber);
        } else {
            // Якщо предприємство не існує, виконуємо вставку
            $this->addEnterprise($name, $eGRPOU, $individualTaxNumber);
        }
    }

    // Метод для оновлення інформації про предприємство
    public function updateEnterprise($enterpriseId, $name, $individualTaxNumber)
    {
        $query = "UPDATE Enterprises SET name = :name, individualTaxNumber = :individualTaxNumber WHERE enterpriseId = :enterpriseId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':enterpriseId', $enterpriseId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':individualTaxNumber', $individualTaxNumber);
        $stmt->execute();
    }

    // Метод для отримання інформації про предприємство за його eGRPOU
    public function getEnterpriseByEGRPOU($eGRPOU)
    {
        $query = "SELECT * FROM Enterprises WHERE eGRPOU = :eGRPOU";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':eGRPOU', $eGRPOU);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Метод для удаления предприятия
    public function deleteEnterprise($enterpriseId)
    {
        $query = "DELETE FROM Enterprises WHERE enterpriseId = :enterpriseId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':enterpriseId', $enterpriseId);
        return $stmt->execute();
    }
    // Получения всхе предприятий по ид из токена
    public function getEnterprisesByUserId($userId)
    {
        try {
            // Сначала получаем companyId для данного userId
            $companyQuery = "SELECT company_id FROM UserCompanies WHERE user_id = :userId";
            $companyStmt = $this->conn->prepare($companyQuery);
            $companyStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $companyStmt->execute();
            $companyResult = $companyStmt->fetch(PDO::FETCH_ASSOC);
            $companyId = $companyResult['company_id'] ?? null;

            if (!$companyId) {
                return null; // или обработать отсутствие companyId
            }

            // Теперь получаем все enterpriseId, связанные с этой компанией
            $enterpriseQuery = "SELECT enterpriseId FROM CompanyEnterprises WHERE companyId = :companyId";
            $enterpriseStmt = $this->conn->prepare($enterpriseQuery);
            $enterpriseStmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
            $enterpriseStmt->execute();
            $enterprises = $enterpriseStmt->fetchAll(PDO::FETCH_ASSOC);

            return $enterprises;
        } catch (PDOException $e) {
            // Обработка ошибки
            return ['error' => 'An error occurred while retrieving enterprises: ' . $e->getMessage()];
        }
    }


    public function isUserAssociatedWithEnterprise($userId, $enterpriseId)
    {
        $companyInfo = $this->getCompanyInfo($userId);
        if (!$companyInfo || !isset ($companyInfo['details']['id'])) {
            return false;
        }
        $companyId = $companyInfo['details']['id'];

        $query = "SELECT COUNT(*) FROM CompanyEnterprises WHERE companyId = :companyId AND enterpriseId = :enterpriseId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
        $stmt->bindParam(':enterpriseId', $enterpriseId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }
    // 
    public function getEnterpriseIdsByCompanyId($companyId)
    {
        $query = "SELECT enterpriseId FROM CompanyEnterprises WHERE companyId = :companyId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
}
?>