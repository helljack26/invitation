<?php

namespace Model;

use PDO;
use Service\CacheService;

class CompanyModel
{
    /**
     * Подключение к базе данных.
     *
     * @var PDO
     */
    protected $conn;

    /**
     * Сервис кеширования.
     *
     * @var CacheService
     */
    protected $cacheService;

    /**
     * Конструктор класса CompanyModel.
     *
     * @param PDO $conn Подключение к базе данных.
     * @param CacheService $cacheService Сервис кеширования.
     */
    public function __construct(PDO $conn, CacheService $cacheService)
    {
        $this->conn = $conn;
        $this->cacheService = $cacheService;
    }

    /**
     * Создание новой компании
     */
    public function createCompany($name, $address, $createdBy)
    {
        $query = "INSERT INTO Companies (name, address, company_created_user_id) VALUES (:name, :address, :createdBy)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':createdBy', $createdBy);
        $stmt->execute();
        $companyId = $this->conn->lastInsertId();

        $this->clearCompanyCache($companyId);
        return $companyId;
    }

    /**
     * Получение информации о компании по ID компании
     */
    public function getCompanyInfo($companyId)
    {
        $cacheKey = "company_info_{$companyId}";
        $cachedInfo = $this->cacheService->get($cacheKey);

        if ($cachedInfo) {
            return unserialize($cachedInfo);
        } else {
            $query = "SELECT * FROM Companies WHERE id = :companyId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':companyId', $companyId);
            $stmt->execute();
            $companyInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($companyInfo) {
                $this->cacheService->set($cacheKey, serialize($companyInfo), 3600);
            }

            return $companyInfo;
        }
    }

    /**
     * Получение списка сотрудников компании по ID компании
     */
    public function getCompanyEmployees($companyId)
    {
        $cacheKey = "company_employees_{$companyId}";
        $cachedEmployees = $this->cacheService->get($cacheKey);

        if ($cachedEmployees) {
            return unserialize($cachedEmployees);
        } else {
            $query = "SELECT u.id, u.username, u.email, u.phone_number, u.first_name, u.second_name, u.last_name
                      FROM Users u
                      INNER JOIN UserCompanies uc ON u.id = uc.user_id
                      WHERE uc.company_id = :companyId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':companyId', $companyId);
            $stmt->execute();
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($employees) {
                $this->cacheService->set($cacheKey, serialize($employees));
            }

            return $employees;
        }
    }

    /**
     * Очистка кеша компании и сотрудников
     */
    public function clearCompanyCache($companyId)
    {
        $this->cacheService->del("company_info_{$companyId}");
        $this->cacheService->del("company_employees_{$companyId}");
    }


    /**
     * Получение ID компании по ID пользователя
     */
    public function getUserCompanyId($userId)
    {
        $query = "SELECT company_id FROM UserCompanies WHERE user_id = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($result['company_id']) ?? null;
    }
}
