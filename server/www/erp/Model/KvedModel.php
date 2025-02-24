<?php
namespace Model;

use PDO;
use Exception;

class KvedModel extends Database
{
    public function __construct($conn, $redis)
    {
        parent::__construct($conn, $redis);
    }
    public function addKved($number, $name, $main)
    {
        $query = "INSERT INTO Kveds (number, name, main) VALUES (:number, :name, :main)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':number', $number);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':main', $main);

        $result = $stmt->execute();

        // Получение последнего вставленного ID
        $lastInsertedId = $this->conn->lastInsertId();

        return [$result, $lastInsertedId];
    }

    public function getKvedsByEnterprise($eGRPOUId)
    {
        $query = "
        SELECT K.* 
        FROM Kveds K
        JOIN KvedsEnterprises KE ON K.id = KE.Kved
        WHERE KE.Enterprises = :eGRPOUId ORDER BY K.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':eGRPOUId', $eGRPOUId);
        $stmt->execute();
        $kveds = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $kveds;
    }

    public function addKvedToEnterprise($enterpriseId, $kvedId)
    {
        $query = "INSERT INTO KvedsEnterprises (Enterprises, Kved) VALUES (:enterpriseId, :kvedId)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':enterpriseId', $enterpriseId);
        $stmt->bindParam(':kvedId', $kvedId);
        return $stmt->execute();
    }

    public function updateKvedForEnterprise($enterpriseId, $kvedId)
    {
        $this->deleteKvedsForEnterprise($enterpriseId);

        foreach ($kvedId as $kved) {
            $this->addKvedToEnterprise($enterpriseId, $kved);
        }
    }

    public function deleteKvedFromEnterprise($eGRPOUId, $kvedId)
    {
        try {
            $this->conn->beginTransaction();

            // Удаление связи с предприятием
            $this->deleteKvedAssociation($eGRPOUId, $kvedId);

            // Удаление самого KVED
            $this->deleteKved($kvedId);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Ошибка транзакции, откатываем изменения
            $this->conn->rollBack();
            return false;
        }
    }

    // Добавьте методы deleteKved и deleteKvedAssociation

    public function deleteKved($kvedId)
    {
        $query = "DELETE FROM Kveds WHERE id = :kvedId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':kvedId', $kvedId);

        return $stmt->execute();
    }

    public function deleteKvedAssociation($eGRPOUId, $kvedId)
    {
        $query = "DELETE FROM KvedsEnterprises WHERE Enterprises = :eGRPOUId AND Kved = :kvedId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':eGRPOUId', $eGRPOUId);
        $stmt->bindParam(':kvedId', $kvedId);

        return $stmt->execute();
    }
    // Add a new method to check if KVED exists
    public function kvedExists($kvedId)
    {
        $query = "SELECT COUNT(*) FROM Kveds WHERE id = :kvedId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':kvedId', $kvedId);
        $stmt->execute();

        $count = $stmt->fetchColumn();
        return ($count > 0);
    }

    // If define new main kved, set zero to current main kved
    public function updateMainKvedToZero($eGRPOUId)
    {
        try {
            $this->conn->beginTransaction();

            // Update main KVED to 0 where eGRPOUId matches
            $query = "UPDATE Kveds 
                    SET main = 0 
                    WHERE id IN (
                        SELECT Kved 
                        FROM KvedsEnterprises 
                        WHERE Enterprises = :eGRPOUId
                    ) 
                    AND main = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':eGRPOUId', $eGRPOUId);
            $stmt->execute();

            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollBack();
            return false;
        }
    }
}