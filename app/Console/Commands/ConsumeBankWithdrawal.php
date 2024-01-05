<?php

namespace App\Console\Commands;

use App\Jobs\ConsumeB2CJob;
use App\Jobs\ConsumeBankWithdrawJob;
use Bschmitt\Amqp\Amqp;
use Bschmitt\Amqp\Consumer;
use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class ConsumeBankWithdrawal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank:withdraw';

    protected $description = 'Consume Bank Queue from RabbitMQ';

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
        $logger->info('Listening for BANK messages...');

        $consumer->consume(
            'Quicksava_BANK_QUEUE',
            function (AMQPMessage $message, Consumer $resolver) use ($logger): void {
                $logger->info('Consuming message...');

                $payload = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);
                $logger->info('Message received:::', $payload);
                dispatch(new ConsumeBankWithdrawJob($payload));
                $logger->info('Message handled.');
                $resolver->acknowledge($message);
            },
            [
//                'routing' => ['ingest.pending'],
                'persistent' => true
            ]
        );

        $logger->info('BANK Consumer exited.');

        return true;
    }
}
