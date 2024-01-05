<?php

namespace App\Console\Commands;

use App\Jobs\ConsumeB2CJob;
use App\Jobs\ConsumeC2bJob;
use Bschmitt\Amqp\Amqp;
use Bschmitt\Amqp\Consumer;
use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class ConsumeB2C extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consume:b2c';

    protected $description = 'Consume B2C Queue from RabbitMQ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Amqp $consumer, LoggerInterface $logger) : bool
    {
        $logger->info('Listening for B2C messages...');

        $consumer->consume(
            'Quicksava_B2C_QUEUE',
            function (AMQPMessage $message, Consumer $resolver) use ($logger): void {
                $logger->info('Consuming message...');

                $payload = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);
                $logger->info('Message received:::', $payload);
                //$this->dispatch(new IngestDataJob($payload['filepath']));
                dispatch(new ConsumeB2CJob($payload));
                $logger->info('Message handled.');
                $resolver->acknowledge($message);
            },
            [
//                'routing' => ['ingest.pending'],
                'persistent' => true
            ]
        );

        $logger->info('B2C Consumer exited.');

        return true;
    }

}
