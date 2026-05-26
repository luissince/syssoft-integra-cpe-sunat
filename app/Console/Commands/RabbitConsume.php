<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;


//    /**
//      * rabbitmq publish
//      */

//     $rabbit = new RabbitMQService();

//     $rabbit->publish(
//         'factura.sunat.procesada',
//         [
//             'idFactura' => 1,
//             'estado' => 'ACEPTADA'
//         ]
//     );

//     return response()->json([
//         'ok' => true
//     ]);

//     /**
//      * end rabbitmq publish
//      */


class RabbitConsume extends Command
{
    protected $signature = 'rabbit:consume';

    protected $description = 'Consumir mensajes RabbitMQ';

    public function handle()
    {
        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_USER'),
            env('RABBITMQ_PASSWORD')
        );

        $channel = $connection->channel();

        $queue = 'factura.creada';

        $channel->queue_declare(
            $queue,
            false,
            true,
            false,
            false
        );

        echo "Esperando mensajes...\n";

        $callback = function ($msg) {

            $data = json_decode(
                $msg->body,
                true
            );

            print_r($data);

            echo "\n";

            /*
                AQUI HACES:
                - enviar sunat
                - generar pdf
                - guardar xml
            */

            $msg->ack();
        };

        $channel->basic_consume(
            $queue,
            '',
            false,
            false,
            false,
            false,
            $callback
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
