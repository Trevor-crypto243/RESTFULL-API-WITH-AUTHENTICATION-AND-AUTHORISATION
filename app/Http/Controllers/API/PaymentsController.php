<?php

namespace App\Http\Controllers\API;

use App\Alert;
use App\BulkDisbursement;
use App\Company;
use App\CustomerProfile;
use App\Escrow;
use App\Http\Controllers\Controller;
use App\Jobs\WithdrawMoney;
use App\MpesaCharge;
use App\MpesaPayment;
use App\MpesaWithdrawal;

use App\PaybillBalance;
use App\SuspenseAmount;
use App\User;
use App\Wallet;
use App\WalletTransaction;
use Bschmitt\Amqp\Amqp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PaymentsController extends Controller
{
    public function ack_payment (Request $request){
        Log::info("ACKNOWLEDGE PAYMENT REQUEST RECEIVED ::::: ");
        //Log::info($request->all());

        DB::transaction(function() use($request) {

            $payment = new MpesaPayment();
            $payment->trans_id = $request->TransID;
            $payment->msisdn = $request->MSISDN;
            $payment->amount = $request->TransAmount;
            $payment->org_account_balance = $request->OrgAccountBalance;
            $payment->ref = $request->BillRefNumber;
            $payment->first_name = $request->FirstName;
            $payment->middle_name = $request->MiddleName;
            $payment->last_name = $request->LastName;
            $payment->saveOrFail();


            $amount = $request->TransAmount;
            $receipt = $request->TransID;
            $msisdn = $request->MSISDN;

            $ref = $request->BillRefNumber;


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
                    $suspense->name = $request->FirstName.' '.$request->LastName;
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

    public function ack_disbursement (Request $request){
        Log::info("ACKNOWLEDGE DISBURSEMENT REQUEST SENT ::::: ");
        Log::info($request->ResultDesc);

        $convID = $request->ConversationID;

        //Log::info($request->all());

        if ($request->ResultCode == 0){
            $ResultParameter = $request->ResultParameters['ResultParameter'];

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
                $escrow->description=$request->ResultDesc;
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
                $bulkDisbursement->receipt = $request->TransactionID;
                $bulkDisbursement->name = explode(" - ",$name)[1];
                $bulkDisbursement->description=$request->ResultDesc;
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

//                DB::transaction(function() use($escrow,$request) {
                    $escrow->complete=true;
                    $escrow->status="FAILED";
                    $escrow->description=$request->ResultDesc;
                    $escrow->update();

                    Log::info("REVERTING WALLET...");
                    //update wallet and insert into escrow

                    $chargeRslt = MpesaCharge::where('min', '<=',$escrow->amount)->where('max', '>=',$escrow->amount)->first();
                    $charge = is_null($chargeRslt) ? 22.4 : $chargeRslt->charge;

                    $wallet = $escrow->wallet;
                    Log::info("CURRENT WALLET BALANCE...".$wallet->current_balance);
//                    $wallet = Wallet::find($escrow->wallet_id);
                    $prevBal = $wallet->current_balance;
                    $newBal = $prevBal + $escrow->amount + $charge;

                    $wallet->current_balance = $newBal;
                    $wallet->previous_balance = $prevBal;
                    $wallet->update();

                    Log::info("WALLET UPDATED::::: New wallet balance: ".$newBal);
                    Log::info("ESCROW UPDATED::::: Transaction failed ");
//                });

            }else{
                Log::info("ESCROW NOT FOUND::::: ");
            }


            //deal with bulk disbursement
            $bulkDisbursement = BulkDisbursement::where('conversation_id', $convID)->first();
            if ($bulkDisbursement!=null){
                $bulkDisbursement->status = "FAILED";
                $bulkDisbursement->receipt = $request->TransactionID;
                $bulkDisbursement->description=$request->ResultDesc;
                $bulkDisbursement->update();

                Log::info("BULK DISBURSEMENT MARKED AS FAILED:::::>>> ".$bulkDisbursement->msisdn.":::".$bulkDisbursement->amount);


            }else{
                Log::info("BULK DISBURSEMENT NOT FOUND::::: ");
            }

            //Log::info($query);
        }

    }

    public function top_up (Request $request){

        $this->validate($request, [
            'amount' => 'required',
            'msisdn' => 'required',
            'account_no' => 'required',
        ]);

        $user = User::where('phone_no', $request->msisdn)->first();

        if (is_null($user))
            return response()->json([
                'success' => false,
                'message' => 'Top up not allowed. Invalid phone number. Please contact system admin',
            ], 200);

        $customer = CustomerProfile::where('user_id', $user->id)->first();

        if (is_null($customer))
            return response()->json([
                'success' => false,
                'message' => 'Invalid user type. Top up not allowed. Please contact system admin',
            ], 200);

        if ($customer->is_checkoff == false)
            return response()->json([
                'success' => false,
                'message' => 'Top up not allowed. Please contact system admin',
            ], 200);



        if (optional($user->wallet)->active == false)
            return response()->json([
                'success' => false,
                'message' => 'Your wallet is frozen, Please contact system admin on 0114496184 for more information.',
            ], 200);


        $ENDPOINT = 'http://pay.localhost/cash_stk.php';
        $headers = array(
            'Content-type: application/json',
        );


        $payload = array(
            "shortcode"=>"4071991",
            "msisdn"=>$request->msisdn,
            "amount"=>$request->amount,
            "account_no"=>$request->account_no,
        );

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

        Log::info("STK PUSH. See response below...");
        Log::info($result);
        Log::info("end of STK PUSH");



        return response()->json([
            'success' => true,
            'message' => 'Check your phone to enter M-Pesa PIN and complete the transaction',
        ], 200);
    }

    public function withdraw (Request $request){

        $this->validate($request, [
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required',
            'msisdn' => 'required',
        ]);


//        $wallet = Wallet::find(1);
        $wallet = Wallet::find($request->wallet_id);


        if ($wallet->active == false)
            return response()->json([
                'success' => false,
                'message' => 'Your wallet is frozen, Please contact system admin on 0114496184 for more information.',
            ], 200);


        if ($request->amount < 10)
            return response()->json([
                'success' => false,
                'message' => 'You can not withdraw less than Ksh. 10 per transaction',
            ], 200);

        if ($request->amount > 150000)
            return response()->json([
                'success' => false,
                'message' => 'You can not withdraw more than Ksh. 150,000 per transaction',
            ], 200);


        $charge = MpesaCharge::where('min', '<=',$request->amount)->where('max', '>=',$request->amount)->first();

        if (is_null($charge))
            return response()->json([
                'success' => false,
                'message' => 'Unable to determine transaction charge. Please contact system admin.',
            ], 200);

        $total = $request->amount + $charge->charge;
        if ($wallet->current_balance < $total)
            return response()->json([
                'success' => false,
                'message' => 'Your wallet does not have enough balance to perform this transaction including transaction charges. You need a minimum of Ksh '.number_format($total,2).' to make this withdrawal',
            ], 200);

        $timestamp = Carbon::now()->getTimestamp();

        //insert into queue
        //WithdrawMoney will be called by artisan command, and it's data passed from the queue

        $payload = array(
            "wallet_id"=>$wallet->id,
            "recipient"=>$request->msisdn,
            "amount"=>$request->amount,
            "randomID"=>$timestamp."M-PESA withdrawal",
        );

        //Amqp::publish('Quicksava_B2C_QUEUE', $payload , ['queue' => 'Quicksava_B2C_QUEUE']);

        $connection = new AMQPStreamConnection('localhost', 5672,
            config('app.AMQP_USER'), config('app.AMQP_PASSWORD'));
        $channel = $connection->channel();
        $channel->queue_declare('Quicksava_B2C_QUEUE', false, true, false, false);
        $msg = new AMQPMessage(json_encode($payload), array('delivery_mode' => 2)
        );
        $channel->basic_publish($msg, '', 'Quicksava_B2C_QUEUE');
        $channel->close();
        $connection->close();
        //return TRUE;



        //send alert for any amount >15k
       // if ($request->amount >= 15000){
            $alerts = Alert::where('type','DAILY_DISBURSEMENT')->get();

            $user = User::where('wallet_id', $request->wallet_id)->first();
            if (is_null($user)){
                $company = Company::where('wallet_id', $request->wallet_id)->first();

                $name = is_null($company) ? "" : $company->business_name;
            }else{
                $name = $user->surname.' '.$user->name;
            }

            $message = "Wallet withdrawal alert. Amount withdrawn: ".number_format($request->amount,2).
               ". Recipient: ".$request->msisdn.". ".$name;

            foreach ($alerts as $alert){
                send_sms($alert->recipient, $message);
            }
       // }

       // WithdrawMoney::dispatch($wallet,$request->msisdn, $request->amount, $timestamp."M-PESA withdrawal");


        return response()->json([
            'success' => true,
            'message' => 'Transaction has been initiated. Please wait for M-Pesa message',
        ], 200);
    }

    public function update_b2c_balance(Request $request){
        Log::info("RECEIVED B2C BALANCE. SEE PAYLOAD BELOW ::::: ");
//        Log::info($request->all());
        Log::info(" :::::  :::::  ::::: ");
        Log::info($request->Result['ResultParameters']['ResultParameter'][1]['Value']);
        //sample
        //'Working Account|KES|700005.00|700005.00|0.00|0.00&Utility Account|KES|44473.70|44473.70|0.00|0.00&Charges Paid Account|KES|0.00|0.00|0.00|0.00'
        //yes, safaricom is this dumb! WTF is this shit? Cunts!!
        $value = $request->Result['ResultParameters']['ResultParameter'][1]['Value'];

        //split this shit by pipe |

        $str_arr = explode ("|", $value);

        $mmf = $str_arr[2];
        $utility = $str_arr[7];
        Log::info("MMF==>".$mmf."UTILITY=>".$utility);

        $pb = PaybillBalance::where('shortcode','3028315')->first();
        if (!is_null($pb)){
            $pb->mmf = $mmf;
            $pb->utility = $utility;
            $pb->update();
        }

        Log::info("B2C BALANCE has been updated ::::: ");
    }


}
