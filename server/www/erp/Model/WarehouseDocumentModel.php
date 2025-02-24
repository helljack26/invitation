<?php
namespace Model;

use PDO;

class WarehouseDocumentModel extends Database
{
    public function __construct()
    {
        parent::__construct(); // Вызываем конструктор родительского класса Database
    }

    public function getWarehouseDocumentsByEnterprise($enterpriseId, $startDate = null, $endDate = null)
    {
        $query = "SELECT * FROM WarehouseDocuments WHERE enterpriseId = :enterpriseId";
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    //Получения инвойс по ИД  и по дате создания
    public function getInvoiceByIdAndCreateTime($id, $createTime)
    {
        $redisKey = "invoice_" . $id;

        // Проверяем, есть ли данные в кеше Redis
        $cachedInvoice = $this->redis->get($redisKey);
        if ($cachedInvoice) {
            $cachedInvoice = json_decode($cachedInvoice, true);
            // Проверяем, соответствует ли время создания
            if ($cachedInvoice['theDate'] == $createTime) {
                return $cachedInvoice;
            }
        }

        // Если данных нет в кеше, проверяем в базе данных
        $query = "SELECT * FROM WarehouseDocuments WHERE id = :id AND theDate = :theDate";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':theDate', $createTime);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function insertOrUpdateInvoice($invoice)
    {
        // Проверяем, существует ли уже инвойс в кеше
        $cacheKey = "invoice_" . $invoice['id'];
        if ($this->redis->exists($cacheKey)) {
            // Если инвойс есть в кеше, возвращаем его данные
            $cachedData = json_decode($this->redis->get($cacheKey), true);
            // Здесь может быть логика обработки данных из кеша, если необходимо
            return $cachedData;
        }

        // Проверяем, существует ли уже инвойс в базе данных
        $stmt = $this->conn->prepare("SELECT id FROM WarehouseDocuments WHERE id = :id");
        $stmt->bindParam(':id', $invoice['id']);
        $stmt->execute();

        if ($stmt->fetch()) {
            // Если инвойс уже существует в базе данных, обновляем его
            $updateQuery = "UPDATE WarehouseDocuments SET 
                                enterpriseId = :enterpriseId, theDate = :theDate, number = :number, 
                                theForm = :theForm, fromPartnerId = :fromPartnerId, toPartnerId = :toPartnerId, 
                                total = :total, vatTotal = :vatTotal, exciseTaxTotal = :exciseTaxTotal, 
                                currencyId = :currencyId WHERE id = :id";

            $stmt = $this->conn->prepare($updateQuery);
        } else {
            // Если инвойса нет, вставляем новый
            $insertQuery = "INSERT INTO WarehouseDocuments 
                                (id, enterpriseId, theDate, number, theForm, fromPartnerId, toPartnerId, 
                                total, vatTotal, exciseTaxTotal, currencyId) 
                                VALUES (:id, :enterpriseId, :theDate, :number, :theForm, :fromPartnerId, 
                                :toPartnerId, :total, :vatTotal, :exciseTaxTotal, :currencyId)";

            $stmt = $this->conn->prepare($insertQuery);
        }

        // Заполняем параметры и выполняем запрос
        $stmt->bindParam(':id', $invoice['id']);
        $stmt->bindParam(':enterpriseId', $invoice['enterpriseId']);
        $stmt->bindParam(':theDate', $invoice['theDate']);
        $stmt->bindParam(':number', $invoice['number']);
        $stmt->bindParam(':theForm', $invoice['theForm']);
        $stmt->bindParam(':fromPartnerId', $invoice['fromPartner']['partnerId']);
        $stmt->bindParam(':toPartnerId', $invoice['toPartner']['partnerId']);
        $stmt->bindParam(':total', $invoice['total']);
        $stmt->bindParam(':vatTotal', $invoice['vatTotal']);
        $stmt->bindParam(':exciseTaxTotal', $invoice['exciseTaxTotal']);
        $stmt->bindParam(':currencyId', $invoice['currencyId']);

        $result = $stmt->execute();

        if ($result) {
            // Сохраняем данные в кеше на определенное время (например, 1 час)
            $this->redis->setex($cacheKey, 3600, json_encode($invoice));
        }

        return $result;
    }
}