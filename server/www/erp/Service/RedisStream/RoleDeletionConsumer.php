<?php

namespace Service\RedisStream;

class RoleDeletionConsumer
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
        $streamName = 'role_deletion_stream';
        $groupName = 'role_deletion_group';
        $consumerName = 'role_deletion_consumer';

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
                        $this->finalizeRoleDeletion($data);
                        $this->redis->xAck($streamName, $groupName, [$messageId]);
                    } catch (\Exception $e) {
                        echo "Error processing message $messageId in stream $streamName: " . $e->getMessage() . "\n";
                    }
                }
            }

            sleep(1);
        }
    }

    private function finalizeRoleDeletion($data)
    {
        echo "Finalized deletion of role_id: " . $data['role_id'] . " at " . $data['deleted_at'] . "\n";
    }
}
