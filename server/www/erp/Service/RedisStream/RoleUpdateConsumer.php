<?php

namespace Service\RedisStream;

use PDO;
use PDOException;

class RoleUpdateConsumer
{
    private $redis;
    private $mysql;

    public function __construct($redis, $mysql)
    {
        $this->redis = $redis;
        $this->mysql = $mysql;

        // Включаем режим исключений для PDO, чтобы получить детальные ошибки при сбоях в запросах
        $this->mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function consume()
    {
        $streamName = 'role_update_stream';
        $groupName = 'role_update_group';
        $consumerName = 'role_update_consumer'; // фиксированное имя потребителя

        // Создаём группу, если не существует
        try {
            $this->redis->xGroup('CREATE', $streamName, $groupName, '$', true);
        } catch (\Exception $e) {
            // Если группа уже существует, ничего страшного
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
                        $this->updateRolePermissions($data);
                        $this->redis->xAck($streamName, $groupName, [$messageId]);
                    } catch (\Exception $e) {
                        echo "Error processing message $messageId in stream $streamName: " . $e->getMessage() . "\n";
                    }
                }
            }

            // Небольшая задержка перед следующим чтением
            sleep(1);
        }
    }

    private function updateRolePermissions($data)
    {
        // Для отладки выведем входящие данные:
        echo "Processing update for role_id: {$data['role_id']} with permissions: {$data['permissions']} at {$data['updated_at']}\n";

        $query = "UPDATE Roles SET permissions = :permissions, updated_at = :updatedAt WHERE id = :roleId";
        try {
            $stmt = $this->mysql->prepare($query);
            $stmt->bindParam(':permissions', $data['permissions']);
            $stmt->bindParam(':updatedAt', $data['updated_at']);
            $stmt->bindParam(':roleId', $data['role_id']);
            $stmt->execute();

            $rowCount = $stmt->rowCount();
            if ($rowCount > 0) {
                echo "Updated role permissions in MySQL for role_id: " . $data['role_id'] . ", affected rows: $rowCount\n";
            } else {
                echo "No rows updated for role_id: " . $data['role_id'] . ". Possibly role_id doesn't exist or permissions already identical.\n";
            }
        } catch (PDOException $e) {
            echo "PDOException while updating role_id: {$data['role_id']}: " . $e->getMessage() . "\n";
        }
    }
}
