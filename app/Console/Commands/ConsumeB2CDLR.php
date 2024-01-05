<?php

namespace App\Console\Commands;

use App\Jobs\ConsumeB2CDLR as DlrJob;
use Bschmitt\Amqp\Amqp;
use Bschmitt\Amqp\Consumer;
use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class ConsumeB2CDLR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consume:b2cdlr';

    protected $description = 'Consume B2C DLR Queue from RabbitMQ';

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
        $logger->info('Listening for B2C DLR messages...');

        $consumer->consume(
            'MPESA_B2C_DLR',
            function (AMQPMessage $message, Consumer $resolver) use ($logger): void {
                $logger->info('Consuming message...');

                $payload = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);
                $logger->info('Message received:::', $payload);
                //$this->dispatch(new IngestDataJob($payload['filepath']));
                dispatch(new DlrJob($payload));
                $logger->info('Message handled.');
                $resolver->acknowledge($message);
            },
            [
//                'routing' => ['ingest.pending'],
                'persistent' => true
            ]
        );

        $logger->info('B2C DLR Consumer exited.');

        return true;
    }
}
