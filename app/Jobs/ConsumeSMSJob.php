<?php

namespace App\Jobs;

use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConsumeSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $payload = $this->payload;
        $recipientNumber = $payload['recipient'];
        $recipientNumber = array($recipientNumber);
        $message = $payload['message'];


        Log::info("sending message from queue...");
        Log::info(":::::::::::::::::::::::::::::::::");
        Log::info("Recipient...: ".implode($recipientNumber));
        Log::info("Message...: ".$message);
        Log::info(":::::::::::::::::::::::::::::::::");

        info(config('app.SMS_API_KEY'));


        $result = Http::accept('application/json')
            ->withHeaders([
                'Content-Type' => 'application/json'
            ])
            ->post('https://api.beyondintochsoftware.com/services/PushSMS', [
                'recipients' => $recipientNumber,
                'message' => $message,
                'short_code' => 'QUICKSAVA',
                'link_id' => '',
                'callback' => '',
                'client_code' => 'quicksava',
                'key' => config('app.SMS_API_KEY'),
                'service_id' =>'',
                'service_type' => 'TRANSACTIONAL'
            ]);

        Log::info("message sent. See response below...");


        Log::info($result);

    }
}
