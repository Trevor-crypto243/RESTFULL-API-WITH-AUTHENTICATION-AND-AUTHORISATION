<?php

namespace App\Console\Commands;

use App\Jobs\ConsumeSMSJob;
use Bschmitt\Amqp\Amqp;
use Bschmitt\Amqp\Consumer;
use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class ConsumeSMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consume:sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume send SMS queue';

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
    public function  handle(Amqp $consumer, LoggerInterface $logger) : bool
    {
        $logger->info('Listening for outbox SMS queue...');
        $consumer->consume(
            'SMS_OUTBOX_QUEUE',
            function (AMQPMessage $message, Consumer $resolver) use ($logger): void {
                $logger->info('Consuming message...');

                $payload = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);
                $logger->info('Message received:::', $payload);
                dispatch(new ConsumeSMSJob($payload));
                $logger->info('Message handled.');
                $resolver->acknowledge($message);
            },
            [
//                'routing' => ['ingest.pending'],
                'persistent' => true
            ]
        );

        $logger->info('SMS Consumer exited.');

        return true;

    }
}
