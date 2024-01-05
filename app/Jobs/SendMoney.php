<?php

namespace App\Jobs;

use App\BulkDisbursement;
use App\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMoney implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $recipient;
    protected $amount;
    protected $randomID;
    protected $narration;

    /**
     * Create a new job instance.
     *
     * @param string $userId
     * @param string $recipient
     * @param string $amount
     * @param string $randomID
     * @param string $narration
     */
    public function __construct($userId, string $recipient, string $amount, string $randomID, string  $narration)
    {
        $this->userId = $userId;
        $this->recipient = $recipient;
        $this->amount = $amount;
        $this->randomID = $randomID;
        $this->narration = $narration;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $recipientNumber = $this->recipient;
        $amount = $this->amount;
        $userId = $this->userId;
        $narration = $this->narration;

        Log::info("saving b2c transaction...");
        Log::info(":::::::::::::::::::::::::::::::::");
        Log::info("Recipient...: " . $recipientNumber);
        Log::info("Amount...: " . $amount);
        Log::info(":::::::::::::::::::::::::::::::::");




        $ENDPOINT = 'http://pay.localhost/cash_b2c.php';
        $headers = array(
            'Content-type: application/json',
        );


        $payload = array(
            "msisdn"=>$recipientNumber,
            "amount"=>$amount,
            "reference"=>$this->randomID,
        );
        $reqParamArray = array();
        $reqParamArray['amount'] = $amount;
        $reqParamArray['msisdn'] = $recipientNumber;
        $reqParamArray['reference'] = $this->randomID;


        $body = json_encode($payload, JSON_PRETTY_PRINT);

        //dd($encBody);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $ENDPOINT); // point to endpoint
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_VERBOSE, true);
        // curl_setopt($ch, CURLOPT_STDERR, $fp);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);  //data
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);// request time out
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '0'); // no ssl verifictaion
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, '0');


        $result=curl_exec($ch);
        curl_close($ch);

        Log::info("See response below...");
        Log::info($result);


        Log::info("creating bulk disbursement...");
        //creating bulk disbursement

        $bulkDisbursement = new BulkDisbursement();
        $bulkDisbursement->created_by = $userId;
        $bulkDisbursement->conversation_id = json_decode($result)->Details->ConversationID;
        $bulkDisbursement->amount = $amount;
        $bulkDisbursement->msisdn = $recipientNumber;
        $bulkDisbursement->narration = $narration;
        $bulkDisbursement->save();

        Log::info("Disbursement CREATED. WAITING FOR M-PESA CALLBACK:::::");


    }
}
