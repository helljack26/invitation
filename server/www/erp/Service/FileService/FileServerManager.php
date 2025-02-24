<?php

namespace Service\FileService;

use PDO;
use Exception;

class FileServerManager
{
    protected PDO $conn;

    public function __construct(PDO $conn)
    {
        if (!$conn) {
            throw new Exception("PDO instance is required for FileServerManager.");
        }
        $this->conn = $conn;
    }

    /**
     * Получить список доступных серверов (status = 'active').
     */
    public function getAvailableServers(): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM file_servers WHERE status = 'active'");
        $stmt->execute();
        $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $servers;
    }

    /**
     * Назначить компании файловый сервер.
     */
    public function assignCompanyToServer(int $companyId): array
    {
        // Получаем все активные серверы
        $servers = $this->getAvailableServers();
        if (empty($servers)) {
            throw new Exception("Нет доступных файловых серверов для назначения.");
        }

        // Сортируем по количеству компаний на каждом сервере
        usort($servers, function ($a, $b) {
            $countA = $this->getCompanyCountOnServer($a['id']);
            $countB = $this->getCompanyCountOnServer($b['id']);
            return $countA <=> $countB;
        });

        // Берём первый сервер (с наименьшим количеством компаний)
        $selectedServer = $servers[0];

        // Проверяем, назначена ли уже данная компания этому серверу
        $stmt = $this->conn->prepare("
            SELECT * FROM company_file_server_mapping 
            WHERE company_id = :company_id AND file_server_id = :file_server_id
        ");
        $stmt->execute([
            ':company_id' => $companyId,
            ':file_server_id' => $selectedServer['id']
        ]);

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            // Записываем в таблицу связи
            $stmt = $this->conn->prepare("
                INSERT INTO company_file_server_mapping (company_id, file_server_id)
                VALUES (:company_id, :file_server_id)
            ");
            $stmt->execute([
                ':company_id' => $companyId,
                ':file_server_id' => $selectedServer['id']
            ]);
        }

        return $selectedServer;
    }

    /**
     * Получить все сервера, назначенные конкретной компании.
     */
    public function getServersForCompany(int $companyId): array
    {
        $stmt = $this->conn->prepare("
            SELECT fs.*
            FROM file_servers fs
            INNER JOIN company_file_server_mapping cfsm 
                ON fs.id = cfsm.file_server_id
            WHERE cfsm.company_id = :company_id
        ");
        $stmt->execute([':company_id' => $companyId]);
        $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $servers;
    }

    /**
     * Получить количество компаний на сервере.
     */
    protected function getCompanyCountOnServer(int $serverId): int
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count
            FROM company_file_server_mapping
            WHERE file_server_id = :server_id
        ");
        $stmt->execute([':server_id' => $serverId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Обновить статус сервера (active/inactive и т.д.).
     */
    public function updateServerStatus(int $serverId, string $status): void
    {
        $stmt = $this->conn->prepare("
            UPDATE file_servers
            SET status = :status
            WHERE id = :server_id
        ");
        $stmt->execute([
            ':status' => $status,
            ':server_id' => $serverId
        ]);
    }
}
