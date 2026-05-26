<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService
{
    public function publish($queue, $data)
    {
        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_USER'),
            env('RABBITMQ_PASSWORD')
        );

        $channel = $connection->channel();

        $channel->queue_declare(
            $queue,
            false,
            true,
            false,
            false
        );

        $message = new AMQPMessage(
            json_encode($data),
            [
                'delivery_mode' => 2
            ]
        );

        $channel->basic_publish(
            $message,
            '',
            $queue
        );

        $channel->close();
        $connection->close();
    }
}