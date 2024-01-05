<?php

namespace App\Jobs;

use App\Escrow;
use App\MpesaCharge;
use App\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawMoney implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $wallet;
    protected $recipient;
    protected $amount;
    protected $randomID;


    /**
     * Create a new job instance.
     *
     * @param Wallet $wallet
     * @param string $recipient
     * @param string $amount
     * @param string $randomID
     */
    public function __construct(Wallet  $wallet, string $recipient, string $amount, string $randomID)
    {
        $this->wallet = $wallet;
        $this->recipient = $recipient;
        $this->amount = $amount;
        $this->randomID = $randomID;

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
        $wallet = $this->wallet;

        Log::info("saving b2c transaction...");
        Log::info(":::::::::::::::::::::::::::::::::");
        Log::info("Recipient...: " . $recipientNumber);
        Log::info("Amount...: " . $amount);
        Log::info(":::::::::::::::::::::::::::::::::");




        $chargeRslt = MpesaCharge::where('min', '<=',$amount)->where('max', '>=',$amount)->first();
        $charge = is_null($chargeRslt) ? 22.4 : $chargeRslt->charge;

        Log::info("UPDATING WALLET...");
        //update wallet and insert into escrow


        if ($wallet->current_balance >= $this->amount){

            $prevBal = $wallet->current_balance;
            $newBal = $prevBal - $amount - $charge;

            $wallet->current_balance = $newBal;
            $wallet->previous_balance = $prevBal;
            $wallet->update();

            Log::info("WALLET ID ".$wallet->id." UPDATED. PREV BALANCE::::: ".$prevBal);
            Log::info("WALLET ID ".$wallet->id." UPDATED. NEW BALANCE::::: ".$newBal);
            Log::info("ESCROW CREATED. WAITING FOR M-PESA CALLBACK:::::");



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


            $escrow = new Escrow();
            $escrow->wallet_id = $wallet->id;
            $escrow->amount = $amount;
            $escrow->msisdn = $recipientNumber;
            $escrow->conversation_id = json_decode($result)->Details->ConversationID;
            $escrow->save();

        }else{
            Log::info("INSUFFICIENT WALLET BALANCE::::: ");
        }
    }
}
