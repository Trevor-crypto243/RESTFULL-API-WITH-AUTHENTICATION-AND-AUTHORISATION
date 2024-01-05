<?php

namespace App\Jobs;

use App\MpesaPayment;
use App\SuspenseAmount;
use App\User;
use App\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsumeC2bJob implements ShouldQueue
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

        DB::transaction(function() use($payload) {

            $payment = new MpesaPayment();
            $payment->trans_id = $payload['TransID'];
            $payment->msisdn = $payload['MSISDN'];
            $payment->amount = $payload['TransAmount'];
            $payment->org_account_balance = $payload['OrgAccountBalance'];
            $payment->ref = $payload['BillRefNumber'];
            $payment->first_name = $payload['FirstName'];
            $payment->middle_name = $payload['MiddleName'];
            $payment->last_name = $payload['LastName'];
            $payment->saveOrFail();


            $amount = $payload['TransAmount'];
            $receipt = $payload['TransID'];
            $msisdn = $payload['MSISDN'];

            $ref = $payload['BillRefNumber'];


            //check refnumber and see if you get a profile match of phone number, credit account if registered
            //if no match, check if sending number is registered, credit account if registered
            //if sending number not registered, send to suspense


            $user = User::where('phone_no',"254".substr($ref, -9))->first();

            if (is_null($user)){
                //check if sending number is registered,
                $user = User::where('phone_no',"254".substr($msisdn, -9))->first();

                if (is_null($user)){
                    //send to suspense
                    Log::info("InVALID ref number and INVALID sending MSISDN. REF IS::".$ref." SAVING TO SUSPENSE::::: ");

                    //put on suspense
                    $suspense = new SuspenseAmount();
                    $suspense->phone_no = $msisdn;
                    $suspense->name = $payload['FirstName'].' '.$payload['LastName'];
                    $suspense->transaction_code = $receipt;
                    $suspense->amount = $amount;
                    $suspense->saveOrFail();
                }else{
                    //credit sending number
                    $wallet = $user->wallet;

                    //credit wallet
                    $prevBal = $wallet->current_balance;

                    $walletTransaction = new WalletTransaction();
                    $walletTransaction->wallet_id = $wallet->id;
                    $walletTransaction->amount = $amount;
                    $walletTransaction->previous_balance = $prevBal;
                    $walletTransaction->transaction_type = 'CR';
                    $walletTransaction->source = 'MPESA Payment';
                    $walletTransaction->trx_id = $receipt;
                    $walletTransaction->narration = "Top up via MPESA";
                    $walletTransaction->saveOrFail();


                    $newBal = $prevBal+$amount;

                    $wallet->current_balance = $newBal;
                    $wallet->previous_balance = $prevBal;
                    $wallet->update();

                    Log::info("WALLET ID ".$wallet->id." UPDATED. NEW BALANCE::::: ".$newBal);

                    send_sms($user->phone_no, "Your wallet at Quicksava credit has been credited with KES ".$amount.
                        " from MPESA transaction ".$receipt.". Your new Quicksava wallet balance is KES ".$newBal);
                }
            }else{
                //credit account specified in account number
                $wallet = $user->wallet;

                //credit wallet
                $prevBal = $wallet->current_balance;

                $walletTransaction = new WalletTransaction();
                $walletTransaction->wallet_id = $wallet->id;
                $walletTransaction->amount = $amount;
                $walletTransaction->previous_balance = $prevBal;
                $walletTransaction->transaction_type = 'CR';
                $walletTransaction->source = 'MPESA Payment';
                $walletTransaction->trx_id = $receipt;
                $walletTransaction->narration = "Top up via MPESA";
                $walletTransaction->saveOrFail();


                $newBal = $prevBal+$amount;

                $wallet->current_balance = $newBal;
                $wallet->previous_balance = $prevBal;
                $wallet->update();

                Log::info("WALLET ID ".$wallet->id." UPDATED. NEW BALANCE::::: ".$newBal);

                send_sms($user->phone_no, "Your wallet at Quicksava credit has been credited with KES ".$amount.
                    " from MPESA transaction ".$receipt.". Your new Quicksava wallet balance is KES ".$newBal);
            }
        });


    }

}
