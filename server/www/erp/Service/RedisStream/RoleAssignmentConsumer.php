<?php

namespace Service\RedisStream;

class RoleAssignmentConsumer
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
        $streamName = 'role_assignment_stream';
        $groupName = 'user_role_assignment_group';
        $consumerName = 'role_assignment_consumer';

        try {
            $this->redis->xGroup('CREATE', $streamName, $groupName, '$', true);
        } catch (\Exception $e) {
            // Группа уже существует
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
                        $this->assignRoleToUser($data);
                        $this->redis->xAck($streamName, $groupName, [$messageId]);
                    } catch (\Exception $e) {
                        echo "Error processing message $messageId in stream $streamName: " . $e->getMessage() . "\n";
                    }
                }
            }

            sleep(1);
        }
    }

    private function assignRoleToUser($data)
    {
        $query = "UPDATE UserRoles SET role_id = :roleId WHERE user_id = :targetUserId";
        $stmt = $this->mysql->prepare($query);
        $stmt->bindParam(':roleId', $data['role_id']);
        $stmt->bindParam(':targetUserId', $data['target_user_id']);
        $stmt->execute();

        echo "Assigned role_id: " . $data['role_id'] . " to user_id: " . $data['target_user_id'] . " in MySQL.\n";
    }
}
