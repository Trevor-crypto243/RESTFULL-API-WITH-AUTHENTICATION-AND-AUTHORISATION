<?php

namespace App\Jobs;

use App\Alert;
use App\Company;
use App\Escrow;
use App\MpesaCharge;
use App\User;
use App\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ConsumeB2CJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    /**
     * Create a new job instance.
     *
     * @param array $payload
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

        $walletID = $payload['wallet_id'];
        $recipientNumber = $payload['recipient'];
        $amount = $payload['amount'];
        $randomID = $payload['randomID'];

        $wallet = Wallet::find($walletID);

        if (!is_null($wallet)){

            Log::info("saving b2c transaction...");
            Log::info(":::::::::::::::::::::::::::::::::");
            Log::info("Recipient...: " . $recipientNumber);
            Log::info("Amount...: " . $amount);
            Log::info(":::::::::::::::::::::::::::::::::");



            //check if correct account is withdrawing
            $user = User::where('wallet_id', $walletID)->first();

            if (is_null($user)){
                $company = Company::where('wallet_id', $walletID)->first();
                $companyRegNumber = optional($company->owner)->phone_no;

                if ("254".substr($companyRegNumber, -9) == "254".substr($recipientNumber, -9)){
                    //continue
                    $isFraud = false;
                }else{
                    //check company managers

                    //fraud, do not continue
                    $isFraud = true;
                }

            }else{
                $regNumber = $user->phone_no;

                if ("254".substr($regNumber, -9) == "254".substr($recipientNumber, -9)){
                    //continue
                    $isFraud = false;
                }else{
                    //fraud, do not continue
                    $isFraud = true;
                }
            }


            if ($isFraud){
                Log::info("FRAUD: WALLET ID ".$wallet->id." RECIPIENT::::: ".$recipientNumber." AMOUNT::::: ".$amount);

                $alerts = Alert::where('type','FRAUD_ALERT')->get();

                $message = "Fraud alert! Withdrawal attempt of Ksh. ".number_format($amount)." to ".$recipientNumber." from wallet ID ".$walletID.". Transaction flagged as fraudulent, not processed. Please check.";

                foreach ($alerts as $alert){
                    send_sms($alert->recipient, $message);
                }


            }else{
                $chargeRslt = MpesaCharge::where('min', '<=',$amount)->where('max', '>=',$amount)->first();
                $charge = is_null($chargeRslt) ? 22.4 : $chargeRslt->charge;

                Log::info("UPDATING WALLET...");
                //update wallet and insert into escrow

                $totalAmount = $amount +$charge;
                if ($wallet->current_balance >= $totalAmount){

                    $ENDPOINT = 'http://pay.localhost/cash_b2c.php';
                    $headers = array(
                        'Content-type: application/json',
                    );

                    $payload = array(
                        "msisdn"=>$recipientNumber,
                        "amount"=>$amount,
                        "reference"=>$randomID,
                    );
//                $reqParamArray = array();
//                $reqParamArray['amount'] = $amount;
//                $reqParamArray['msisdn'] = $recipientNumber;
//                $reqParamArray['reference'] = $randomID;


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

                    if(isset(json_decode($result)->Details->ConversationID)) {

                        $prevBal = $wallet->current_balance;
                        $newBal = $prevBal - $amount - $charge;

                        $wallet->current_balance = $newBal;
                        $wallet->previous_balance = $prevBal;
                        $wallet->update();

                        //deduct wallet
                        Log::info("WALLET ID ".$wallet->id." UPDATED. PREV BALANCE::::: ".$prevBal);
                        Log::info("WALLET ID ".$wallet->id." UPDATED. NEW BALANCE::::: ".$newBal);
                        Log::info("ESCROW CREATED. WAITING FOR M-PESA CALLBACK:::::");

                        //create escrow
                        $escrow = new Escrow();
                        $escrow->wallet_id = $wallet->id;
                        $escrow->amount = $amount;
                        $escrow->msisdn = $recipientNumber;
                        $escrow->conversation_id = json_decode($result)->Details->ConversationID;
                        $escrow->save();
                    }else{
                        Log::info("FAILED TO PROCESS M-PESA WITH ERROR::: ".json_decode($result)->Details->errorMessage);
                    }

                }else{
                    Log::info("INSUFFICIENT WALLET BALANCE::::: ");
                }

            }
        }
    }
}
