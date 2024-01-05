<?php

namespace App\Console\Commands;

use App\Jobs\ConsumeC2bJob;
use Bschmitt\Amqp\Amqp;
use Bschmitt\Amqp\Consumer;
use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class ConsumeC2B extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consume:c2b';

    protected $description = 'Consume C2B Queue from RabbitMQ';

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
        $logger->info('Listening for C2B messages...');

        $consumer->consume(
            'Quicksava_C2B_QUEUE',
            function (AMQPMessage $message, Consumer $resolver) use ($logger): void {
                $logger->info('Consuming message...');

                $payload = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);
                $logger->info('Message received:::', $payload);
                //$this->dispatch(new IngestDataJob($payload['filepath']));
                dispatch(new ConsumeC2bJob($payload));
                $logger->info('Message handled.');
                $resolver->acknowledge($message);
            },
            [
//                'routing' => ['ingest.pending'],
                'persistent' => true
            ]
        );

        $logger->info('C2B Consumer exited.');

        return true;
    }

}
