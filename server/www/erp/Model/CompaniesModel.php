<?php
namespace Model;

use PDO;

class CompaniesModel extends Database
{
    /**
     * Создание новой компании
     */
    public function createCompany($companyName, $address, $userId)
    {
        try {
            $query = "INSERT INTO Companies (company_name, address, company_created_user_id) 
                      VALUES (:companyName, :address, :userId)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':companyName', $companyName, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address, PDO::PARAM_STR);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return $this->conn->lastInsertId();
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Связь пользователя с компанией
     */
    public function linkUserToCompany($userId, $companyId)
    {
        try {
            $query = "INSERT INTO UserCompanies (user_id, company_id) VALUES (:userId, :companyId)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Проверка, имеет ли пользователь уже компанию
     */
    public function checkIfUserHasCompany($userId)
    {
        try {
            $query = "SELECT COUNT(*) FROM Companies WHERE company_created_user_id = :userId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Получение информации о компании по ID
     */
    public function getCompanyInfo($companyId)
    {
        try {
            $query = "SELECT * FROM Companies WHERE id = :companyId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Получение сотрудников компании с их ролями
     */
    public function getCompanyEmployeesWithRoles($companyId)
    {
        try {
            $query = "SELECT 
                        u.id, 
                        u.username, 
                        u.email, 
                        u.phone_number, 
                        u.first_name, 
                        u.last_name, 
                        r.role_name 
                      FROM Users u
                      INNER JOIN UserCompanies uc ON u.id = uc.user_id
                      LEFT JOIN UserCompanyRoles ur ON u.id = ur.user_id
                      LEFT JOIN CompanyRoles r ON ur.role_id = r.id
                      WHERE uc.company_id = :companyId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Обновление информации о компании
     */
    public function updateCompanyInfo($companyId, $companyName, $address)
    {
        try {
            $query = "UPDATE Companies SET company_name = :companyName, address = :address WHERE id = :companyId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':companyName', $companyName, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address, PDO::PARAM_STR);
            $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Удаление компании по ID
     */
    public function deleteCompany($companyId)
    {
        try {
            $query = "DELETE FROM Companies WHERE id = :companyId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Очистка кеша компании
     */
    public function clearCompanyCache($companyId)
    {
        if ($this->redis->exists("company_info_{$companyId}")) {
            $this->redis->del("company_info_{$companyId}");
        }
        if ($this->redis->exists("company_employees_{$companyId}")) {
            $this->redis->del("company_employees_{$companyId}");
        }
    }

    /**
     * Получение ID компании по ID пользователя
     */
    public function getUserCompanyId($userId)
    {
        try {
            $query = "SELECT company_id FROM UserCompanies WHERE user_id = :userId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['company_id'] ?? null;
        } catch (\PDOException $e) {
            return null;
        }
    }
}
