<?php

namespace Service\RedisStream;

class RoleCreateConsumer
{
    private $redis;
    private $mysql;

    public function __construct($redis, $mysql)
    {
        $this->redis = $redis;
        $this->mysql = $mysql;
    }

    public function consume()
    {
        $streamName = 'role_creation_stream';
        $groupName = 'role_creation_group';
        $consumerName = 'role_creation_consumer'; // фиксированное имя потребителя

        // Создаём группу, если не существует
        try {
            $this->redis->xGroup('CREATE', $streamName, $groupName, '$', true);
        } catch (\Exception $e) {
            // Группа уже существует, игнорируем ошибку
        }

        while (true) {
            $messages = $this->redis->xReadGroup(
                $groupName,
                $consumerName,
                [$streamName => '>'],
                10,
                1000
            );

            if ($messages && isset($messages[$streamName])) {
                foreach ($messages[$streamName] as $messageId => $data) {
                    try {
                        $this->createRoleInMySQL($data);
                        // Подтверждаем обработку сообщения
                        $this->redis->xAck($streamName, $groupName, [$messageId]);
                    } catch (\Exception $e) {
                        echo "Error processing message $messageId in stream $streamName: " . $e->getMessage() . "\n";
                    }
                }
            }

            // Небольшая задержка перед следующим циклом
            sleep(1);
        }
    }

    private function createRoleInMySQL($data)
    {
        // Предполагается, что в таблице Roles есть поля:
        // id (INT), company_id (INT), role_name (VARCHAR), description (VARCHAR),
        // permissions (JSON), is_default (TINYINT), updated_at (DATETIME)
        
        // Преобразуем permissions обратно в JSON-строку, если надо (у нас уже строка)
        $query = "INSERT INTO Roles (id, company_id, role_name, description, permissions, is_default, updated_at)
                  VALUES (:id, :companyId, :roleName, :description, :permissions, 0, :updatedAt)";
        $stmt = $this->mysql->prepare($query);
        $stmt->bindParam(':id', $data['role_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':companyId', $data['company_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':roleName', $data['role_name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':permissions', $data['permissions']);
        $stmt->bindParam(':updatedAt', $data['updated_at']);
        $stmt->execute();

        echo "Created new role in MySQL for role_id: " . $data['role_id'] . "\n";
    }
}
