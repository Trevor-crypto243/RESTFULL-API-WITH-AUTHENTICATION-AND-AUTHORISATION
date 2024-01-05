<?php

namespace App\Jobs;

use App\BankAccount;
use App\MpesaCharge;
use App\Wallet;
use App\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ConsumeBankWithdrawJob implements ShouldQueue
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
        $accountId = $payload['account_id'];
        $type = $payload['type'];
        $amount = $payload['amount'];
        $originatorRef = $payload['ref'];
        $sessionId = $payload['session_id'];
        $customerIdentifier = $payload['customer_identifier'];
        $narration = $payload['narration'];

        $wallet = Wallet::find($walletID);
        $bankAccount = BankAccount::find($accountId);



        $ENDPOINT = 'https://Quicksava.sidianbank.co.ke:9089/TransactionService';
        $headers = array(
            'Content-Type: application/json',
        );

        if ($type=="PESALINK" || $type=="EFT"){
            $payload = array(
                "TransactionRequest" => array(
                    "SessionInfo" => array(
//                        "SessionId" => "En5vTFZEdp46s54YOf9UmQ=="
                        "SessionId" => stripcslashes($sessionId)
                    ),
                    "OriginatorReference"=>$originatorRef."",
                    "TransactionType"=>$type,
                    "TransactionAmount"=>$amount+0,
                    "CustomerIdentifier"=>$customerIdentifier,
                    "TransactionNarration"=>$narration,
                    "OriginatorAccount"=>"01036020028661",
                    "BeneficiaryAccount"=>$bankAccount->account_number,
                    "BeneficiaryName"=>$bankAccount->account_name,

                    "ReceiverSortCode"=>optional($bankAccount->branch)->sort_code,
                ),
            );

        }elseif ($type=="RTGS"){
            $payload = array(
                "TransactionRequest" => array(
                    "SessionInfo" => array(
//                        "SessionId" => "En5vTFZEdp46s54YOf9UmQ=="
                        "SessionId" => stripcslashes($sessionId)
                    ),
                    "OriginatorReference"=>$originatorRef."",
                    "TransactionType"=>$type,
                    "TransactionAmount"=>$amount+0,
                    "CustomerIdentifier"=>$customerIdentifier,
                    "TransactionNarration"=>$narration,
                    "OriginatorAccount"=>"01036020028661",
                    "BeneficiaryAccount"=>$bankAccount->account_number,
                    "BeneficiaryName"=>$bankAccount->account_name,

                    "DestinationSwiftCode"=>"SW-".optional($bankAccount->bank)->swift_code,
                    "BeneficiaryDetails"=>$narration,
                    "BeneficiaryAddress"=>optional($bankAccount->branch)->branch_name
                ),
            );

        }else{
            $payload = array();
        }


        $body = json_encode($payload, JSON_PRETTY_PRINT);

        info("Sending to banking endpoint...");
        info($ENDPOINT);

        info("payload...");
        info($body);

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

        Log::info("Request sent. See response below...");
        Log::info($result);
        Log::info("end of request");


        if ($result != null && array_key_exists('TransactionResponse', json_decode($result,true)))
            $status = optional(json_decode($result,true)['TransactionResponse'])['TransactionStatus'];
        else
            $status = "ERROR";

        if ($status == "SUCCESS") {

            $ref = json_decode($result,true)['TransactionResponse']['ProcessedTransactionReference'];

            //insert wallet transaction
            $prevBal = $wallet->current_balance+$amount;

            $walletTransaction = new WalletTransaction();
            $walletTransaction->wallet_id = $wallet->id;
            $walletTransaction->amount = $amount;
            $walletTransaction->previous_balance = $prevBal;
            $walletTransaction->transaction_type = 'DR';
            $walletTransaction->source = 'Quicksava Wallet';
            $walletTransaction->trx_id = $ref;
            $walletTransaction->narration = "Quicksava wallet withdrawal to Bank Account";
            $walletTransaction->saveOrFail();


            //send SMS to customer
            send_sms($customerIdentifier,"Your bank withdrawal of Ksh ".number_format($amount,2)." has been processed successfully via ".$type.
                '. Transaction reference: '.$ref);

        } else{
            if ($result != null && array_key_exists('TransactionResponse', json_decode($result,true)))
                $error = json_decode($result,true)['TransactionResponse']['ErrorDetail'];
            else
                $error = "External bank is not reachable";


            //revert wallet by incrementing with amount
            Log::info("REVERTING WALLET...");

            Log::info("CURRENT WALLET BALANCE...".$wallet->current_balance);
            $prevBal = $wallet->current_balance;
            $newBal = $prevBal + $amount;

            $wallet->current_balance = $newBal;
            $wallet->previous_balance = $prevBal;
            $wallet->update();

            Log::info("WALLET UPDATED::::: New wallet balance: ".$newBal);
            Log::info("WALLET UPDATED::::: Bank Transaction failed ");

            //send sms to customer.
            send_sms($customerIdentifier,"Your bank withdrawal of Ksh ".number_format($amount,2)." has failed. ".$error.". Please try again later ");
        }

    }
}
