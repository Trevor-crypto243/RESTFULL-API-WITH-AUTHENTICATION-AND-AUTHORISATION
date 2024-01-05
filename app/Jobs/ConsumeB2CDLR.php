<?php

namespace App\Jobs;

use App\BulkDisbursement;
use App\Escrow;
use App\MpesaCharge;
use App\PaybillBalance;
use App\WalletTransaction;
use App\MpesaWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class ConsumeB2CDLR implements ShouldQueue
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

    public function handle()
    {
        $payload = $this->payload;

        Log::info($payload['ResultDesc']);

        $convID = $payload['ConversationID'];


        if ($payload['ResultCode'] == 0){
            $ResultParameter = $payload['ResultParameters']['ResultParameter'];

            $amount = 0;
            $receipt = "";
            $date_time = "";
            $name = "";
            $recipient_registered = false;
            $utility_balance = 0;
            $mmf_balance = 0;

            foreach ($ResultParameter as $ResultParam) {
                $key = $ResultParam['Key'];
                $value = $ResultParam['Value'];

                switch ($key) {
                    case "TransactionAmount":
                        $amount = $value;
                        break;
                    case "TransactionReceipt":
                        $receipt = $value;
                        break;
                    case "B2CRecipientIsRegisteredCustomer":
                        $recipient_registered = $value == "Y" ? true : false;
                        break;
                    case "ReceiverPartyPublicName":
                        $name = $value;
                        break;

                    case "TransactionCompletedDateTime":
                        $date_time = $value;
                        break;

                    case "B2CUtilityAccountAvailableFunds":
                        $utility_balance = $value;
                        break;

                    case "B2CWorkingAccountAvailableFunds":
                        $mmf_balance = $value;
                        break;

                }
            }


            $pb = PaybillBalance::where('shortcode','3028315')->first();
            if (!is_null($pb)){
                $pb->mmf = $mmf_balance;
                $pb->utility = $utility_balance;
                $pb->update();
            }



            $mpesaWithdrawal = new MpesaWithdrawal();
            $mpesaWithdrawal->amount = $amount;
            $mpesaWithdrawal->receipt = $receipt;
            $mpesaWithdrawal->msisdn = explode(" - ",$name)[0];
            $mpesaWithdrawal->date_time = $date_time;
            $mpesaWithdrawal->name = explode(" - ",$name)[1];
            $mpesaWithdrawal->recipient_registered = $recipient_registered;
            $mpesaWithdrawal->utility_balance = $utility_balance;
            $mpesaWithdrawal->mmf_balance = $mmf_balance;
            $mpesaWithdrawal->saveOrFail();

            Log::info("WITHDRAWAL CREATED::::: ");

            $msisdn = explode(" - ",$name)[0];

            $firstCharacter = substr($msisdn, 0, 1);

            if ($firstCharacter == "0"){
                $str = ltrim($msisdn, '0');
                $msisdn = "254".$str;
            }


            //deal with escrow
            $escrow = Escrow::where('conversation_id', $convID)->first();
            if ($escrow!=null){
                $escrow->complete=true;
                $escrow->description=$payload['ResultDesc'];
                $escrow->update();


                $chargeRslt = MpesaCharge::where('min', '<=',$amount)->where('max', '>=',$amount)->first();
                $charge = is_null($chargeRslt) ? 22.4 : $chargeRslt->charge;


                $wallet = $escrow->wallet;
                $prevBal = $wallet->current_balance+$amount+$charge;

                //save to wallet transactions
                $walletTransaction = new WalletTransaction();
                $walletTransaction->wallet_id = $wallet->id;
                $walletTransaction->amount = $amount;
                $walletTransaction->previous_balance = $prevBal;
                $walletTransaction->transaction_type = 'DR';
                $walletTransaction->source = 'Quicksava Wallet';
                $walletTransaction->trx_id = $receipt;
                $walletTransaction->narration = "Payment to ".$name;
                $walletTransaction->saveOrFail();


                $walletTransaction2 = new WalletTransaction();
                $walletTransaction2->wallet_id = $wallet->id;
                $walletTransaction2->amount = $charge;
                $walletTransaction2->previous_balance = $prevBal-$charge;
                $walletTransaction2->transaction_type = 'DR';
                $walletTransaction2->source = 'Quicksava Wallet';
                $walletTransaction2->trx_id = $receipt;
                $walletTransaction2->narration = "Withdrawal charge";
                $walletTransaction2->saveOrFail();

                Log::info("ESCROW UPDATED::::: ");
            }else{
                Log::info("ESCROW NOT FOUND::::: ");
            }


            //deal with bulk disbursement
            $bulkDisbursement = BulkDisbursement::where('conversation_id', $convID)->first();
            if ($bulkDisbursement!=null){
                $bulkDisbursement->status = "SUCCEEDED";
                $bulkDisbursement->receipt = $payload['TransactionID'];
                $bulkDisbursement->name = explode(" - ",$name)[1];
                $bulkDisbursement->description=$payload['ResultDesc'];
                $bulkDisbursement->update();

                Log::info("BULK DISBURSEMENT MARKED AS SUCCEEDED:::::>>> ".$bulkDisbursement->msisdn.":::".$bulkDisbursement->amount);

            }else{
                Log::info("BULK DISBURSEMENT NOT FOUND::::: ");
            }

        }else{
            //failed.
            Log::info("Transaction failed");
            //$query = $request->all();

            //deal with escrow
            $escrow = Escrow::where('conversation_id', $convID)->where('complete',false)->first();
            if ($escrow!=null){

                $escrow->complete=true;
                $escrow->status="FAILED";
                $escrow->description=$payload['ResultDesc'];
                $escrow->update();

                Log::info("REVERTING WALLET...");
                //update wallet and insert into escrow

                $chargeRslt = MpesaCharge::where('min', '<=',$escrow->amount)->where('max', '>=',$escrow->amount)->first();
                $charge = is_null($chargeRslt) ? 22.4 : $chargeRslt->charge;

                $wallet = $escrow->wallet;
                Log::info("CURRENT WALLET BALANCE...".$wallet->current_balance);
                $prevBal = $wallet->current_balance;
                $newBal = $prevBal + $escrow->amount + $charge;

                $wallet->current_balance = $newBal;
                $wallet->previous_balance = $prevBal;
                $wallet->update();

                Log::info("WALLET UPDATED::::: New wallet balance: ".$newBal);
                Log::info("ESCROW UPDATED::::: Transaction failed ");

            }else{
                Log::info("ESCROW NOT FOUND::::: ");
            }


            //deal with bulk disbursement
            $bulkDisbursement = BulkDisbursement::where('conversation_id', $convID)->first();
            if ($bulkDisbursement!=null){
                $bulkDisbursement->status = "FAILED";
                $bulkDisbursement->receipt = $payload['TransactionID'];
                $bulkDisbursement->description=$payload['ResultDesc'];
                $bulkDisbursement->update();

                Log::info("BULK DISBURSEMENT MARKED AS FAILED:::::>>> ".$bulkDisbursement->msisdn.":::".$bulkDisbursement->amount);


            }else{
                Log::info("BULK DISBURSEMENT NOT FOUND::::: ");
            }

        }

    }



}
