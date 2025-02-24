<?php
namespace Model;

use PDO;

class IntegrationModel extends Database
{

    // Получение всех интеграций для компании
    public function getIntegrationsByCompanyId($companyId)
    {
        $query = "SELECT * FROM Integrations WHERE companyId = :companyId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':companyId', $companyId);
        $stmt->execute();
        $integrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($integrations as $key => $integration) {
            $integrations[$key]['settings'] = json_decode($integration['settings'], true);
        }

        return $integrations;
    }

    // Добавление новой интеграции
    public function addIntegration($companyId, $integrationType, $settings)
    {
        $query = "INSERT INTO Integrations (companyId, integrationType, settings) VALUES (:companyId, :integrationType, :settings)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':companyId', $companyId);
        $stmt->bindParam(':integrationType', $integrationType);
        $stmt->bindParam(':settings', json_encode($settings));
        return $stmt->execute();
    }

    // Обновление интеграции
    public function updateIntegration($integrationId, $settings)
    {
        $query = "UPDATE Integrations SET settings = :settings WHERE id = :integrationId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':settings', json_encode($settings));
        $stmt->bindParam(':integrationId', $integrationId);
        return $stmt->execute();
    }

    // Удаление интеграции
    public function deleteIntegration($integrationId)
    {
        $query = "DELETE FROM Integrations WHERE id = :integrationId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':integrationId', $integrationId);
        return $stmt->execute();
    }

    // Если уже есть интеграция с таким типу то выбрасиваем ошибку одной компании доступна только один тип одновермено
    public function isIntegrationTypeExists($companyId, $integrationType)
    {
        $query = "SELECT COUNT(*) FROM Integrations WHERE companyId = :companyId AND integrationType = :integrationType";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':companyId', $companyId);
        $stmt->bindParam(':integrationType', $integrationType);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        return $count > 0;
    }
}
class TorgsoftService
{
    private $host;
    private $port;
    private $username;
    private $password;

    public function __construct($host, $port, $username = null, $password = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function getAllEnterprises()
    {
        $url = "http://{$this->host}:{$this->port}/api/Enterprise/GetAllEnterprises";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([]));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    public function getInvoices($date1, $date2, $enterpriseId, $isPayVAT)
    {
        $url = "http://{$this->host}:{$this->port}/api/Invoce/GetInvoices";
        $data = [
            'date1' => $date1,
            'date2' => $date2,
            'enterpriseId' => $enterpriseId,
            'isPayVAT' => $isPayVAT
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    public function getFinancialDocuments($enterpriseId, $date1, $date2)
    {
        $url = "http://{$this->host}:{$this->port}/api/Fin/GetFiscalFinDocs";
        $data = [
            'enterpriseId' => $enterpriseId,
            'date1' => $date1,
            'date2' => $date2,
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    public function getDeletedFinancialDocuments($date1, $date2)
    {
        $url = "http://{$this->host}:{$this->port}/api/Fin/GetDeletedFinancialDocuments";
        $data = [
            'date1' => $date1,
            'date2' => $date2
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }
}