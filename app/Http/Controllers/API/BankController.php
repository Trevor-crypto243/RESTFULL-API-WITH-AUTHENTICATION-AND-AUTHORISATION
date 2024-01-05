<?php

namespace App\Http\Controllers\API;

use App\Bank;
use App\BankAccount;
use App\BankBranch;
use App\BankToken;
use App\Http\Controllers\Controller;
use App\Http\Resources\AccountsCollection;
use App\Http\Resources\GenericCollection;
use App\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class BankController extends Controller
{

    public function banks()
    {
        return new GenericCollection(Bank::all());
    }

    public function bank_branches($bank_id)
    {
        return new GenericCollection(BankBranch::where('bank_id',$bank_id)->get());
    }

    public function bank_accounts()
    {
        return new AccountsCollection(BankAccount::where('user_id',auth()->user()->id)->where('type','INDIVIDUAL')->orderBy('id', 'desc')->get());
    }

    public function company_bank_accounts($company_id)
    {
        return new AccountsCollection(BankAccount::where('company_id',$company_id)->where('type','COMPANY')->orderBy('id', 'desc')->get());
    }

    public function create_bank_account(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'bank_id' => 'required|exists:banks,id',
            'bank_branch_id' => 'required|exists:bank_branches,id',
            'account_name' => 'required',
            'account_number' => 'required',
            'atm_file' => 'required|file',
        ]);


        $atmFilePath = $request->file('atm_file')->storePublicly('atm_photos', 's3');


        $acc = new BankAccount();
        $acc->user_id = auth()->user()->id;
        $acc->type = $request->type;
        $acc->bank_id = $request->bank_id;
        $acc->bank_branch_id = $request->bank_branch_id;
        $acc->account_name = $request->account_name;
        $acc->account_number = $request->account_number;
        $acc->company_id = $request->company_id;
        $acc->atm_url = Storage::disk('s3')->url($atmFilePath);

        $acc->saveOrFail();


        return response()->json([
            'success' => true,
            'message' => 'Bank account has been created successfully. Please wait for verification before using it for transactions'
        ], 200);

    }

    public function update_bank_account(Request $request)
    {
        $request->validate([
            'account_id' => 'required',
            'type' => 'required',
            'bank_id' => 'required|exists:banks,id',
            'bank_branch_id' => 'required|exists:bank_branches,id',
            'account_name' => 'required',
            'account_number' => 'required',
        ]);


        $acc = BankAccount::find($request->account_id);

        if (is_null($acc))
            return response()->json([
                'success' => false,
                'message' => 'Bank account does not exist'
            ], 200);

        if ($acc->user_id != auth()->user()->id)
            return response()->json([
                'success' => false,
                'message' => 'You do not have permissions to access this resource'
            ], 200);


        //check bank and branch
        $branch = BankBranch::find($request->bank_branch_id);

        if ($branch->bank_id != $request->bank_id)
            return response()->json([
                'success' => false,
                'message' => 'The selected Branch does not belong to the selected Bank. Please check and correct'
            ], 200);




        $acc->type = $request->type;
        $acc->bank_id = $request->bank_id;
        $acc->bank_branch_id = $request->bank_branch_id;
        $acc->account_name = $request->account_name;
        $acc->account_number = $request->account_number;
        $acc->company_id = $request->company_id;
        $acc->approved = false;

        if ($request->has('atm_file')){
            $atmFilePath = $request->file('atm_file')->storePublicly('atm_photos', 's3');
            $acc->atm_url = Storage::disk('s3')->url($atmFilePath);
        }
        $acc->update();


        return response()->json([
            'success' => true,
            'message' => 'Bank account has been updated successfully, Please wait for verification before using it for transactions'
        ], 200);

    }

    public function delete_bank_account(Request $request)
    {
        $request->validate([
            'account_id' => 'required',
        ]);


        $acc = BankAccount::find($request->account_id);

        if (is_null($acc))
            return response()->json([
                'success' => false,
                'message' => 'Bank account does not exist'
            ], 200);

        if ($acc->user_id != auth()->user()->id)
            return response()->json([
                'success' => false,
                'message' => 'You do not have permissions to access this resource'
            ], 200);


        $acc->delete();


        return response()->json([
            'success' => true,
            'message' => 'Bank account has been deleted successfully'
        ], 200);

    }

    public function withdraw_to_bank_account(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required',
            'account_id' => 'required',
            'amount' => 'required',
            'type' => 'required',
        ]);


        $acc = BankAccount::find($request->account_id);

        if (is_null($acc))
            return response()->json([
                'success' => false,
                'message' => 'Bank account does not exist'
            ], 200);

        if ($acc->user_id != auth()->user()->id)
            return response()->json([
                'success' => false,
                'message' => 'You do not have permissions to access this resource'
            ], 200);

        if ($acc->approved == false)
            return response()->json([
                'success' => false,
                'message' => 'Your account has not been approved. Withdrawals are disabled.'
            ], 200);


        $wallet = Wallet::find($request->wallet_id);


        if ($wallet->active == false)
            return response()->json([
                'success' => false,
                'message' => 'Your wallet is frozen, Please contact system admin on 0114496184 for more information.',
            ], 200);

        if ($wallet->current_balance < $request->amount)
            return response()->json([
                'success' => false,
                'message' => 'Your wallet does not have enough balance to perform this transaction.',
            ], 200);

        if ($request->amount < 10)
            return response()->json([
                'success' => false,
                'message' => 'You can not withdraw less than Ksh. 10 per transaction',
            ], 200);



        $bt = BankToken::orderBy('id','desc')->first();

        if (is_null($bt))
            $sessID = "";
        else
            $sessID = $bt->token;


        //do withdrawal here
        $originatorRef = Carbon::now()->getTimestamp();
        $customerIdentifier = auth()->user()->phone_no;


        $payload = array(
            "wallet_id"=>$wallet->id,
            "account_id"=>$request->account_id,
            "type"=>$request->type,
            "amount"=>$request->amount,
            "ref"=>$originatorRef,
            "session_id"=>$sessID,
            "customer_identifier"=>$customerIdentifier,
            "narration"=>"Wallet withdrawal to bank account",
        );

        $connection = new AMQPStreamConnection('localhost', 5672,
            config('app.AMQP_USER'), config('app.AMQP_PASSWORD'));
        $channel = $connection->channel();
        $channel->queue_declare('Quicksava_BANK_QUEUE', false, true, false, false);
        $msg = new AMQPMessage(json_encode($payload), array('delivery_mode' => 2)
        );
        $channel->basic_publish($msg, '', 'Quicksava_BANK_QUEUE');
        $channel->close();
        $connection->close();
        //return TRUE;



        //update balance
        $prevBal = $wallet->current_balance;
        $newBal = $prevBal - $request->amount;

        $wallet->current_balance = $newBal;
        $wallet->previous_balance = $prevBal;
        $wallet->update();


        return response()->json([
            'success' => true,
            'message' => 'Withdrawal request has been received. We shall process the transaction shortly'
        ], 200);

    }
//
//    public function session_request (){
//        $ENDPOINT = 'https://Quicksava.sidianbank.co.ke:9089/CreateSession';
//        $headers = array(
//            'Content-Type: application/json',
//        );
//
//        $payload = array("CreateSessionRequest" => array("Username"=>"SAMPLE_USER","Password"=>"0ffe1abd1a08215353c233d6e009613e95eec4253832a761af28ff37ac5a150c"));
//
//        $body = json_encode($payload, JSON_PRETTY_PRINT);
//
//        info("Sending to endpoint...");
//        info($ENDPOINT);
//
//        info("payload...");
//        info($body);
//
//
//        $ch = curl_init();
//
//        curl_setopt($ch, CURLOPT_URL, $ENDPOINT); // point to endpoint
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//
//        curl_setopt($ch, CURLOPT_VERBOSE, true);
//        // curl_setopt($ch, CURLOPT_STDERR, $fp);
//        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);  //data
//        curl_setopt($ch, CURLOPT_TIMEOUT, 60);// request time out
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '0'); // no ssl verifictaion
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, '0');
//
//
//        $result=curl_exec($ch);
//        curl_close($ch);
//
//        $status = json_decode($result,true)['CreateSessionResponse']['Status'];
//
//
//        Log::info("Request sent. See response below...");
//        Log::info($result);
//        Log::info("end of request");
//
//
//        if ($status == "SUCCESS"){
//            $sessID = json_decode($result,true)['CreateSessionResponse']['sessionId'];
//
//
//            return response()->json([
//                'success' => true,
//                'message' => $sessID,
//            ], 200);
//        }else{
//            $error = json_decode($result,true)['CreateSessionResponse']['ErrorDescription'];
//
//            return response()->json([
//                'success' => false,
//                'message' => "Bank API error occurred. ".$error,
//            ], 200);
//        }
//
//
//    }
//
//    public function pesalink_request (Request $request){
//
//        $request->validate([
//            'session_id' => 'required',
//        ]);
//
//        $ENDPOINT = 'https://Quicksava.sidianbank.co.ke:9089/TransactionService';
//        $headers = array(
//            'Content-Type: application/json',
//        );
//
//        $payload = array(
//            "TransactionRequest" => array(
//                "SessionInfo" => array(
//                    "SessionId" => $request->session_id
//                ),
//                "OriginatorReference"=>"20190414001",
//                "TransactionType"=>"PESALINK",
//                "TransactionAmount"=>"50",
//                "CustomerIdentifier"=>"2547245487178",
//                "TransactionNarration"=>"Sample",
//                "OriginatorAccount"=>"01001010005634",
//                "BeneficiaryAccount"=>"01001030024224",
//                "BeneficiaryName"=>"CATHERINE MUMBI MURAGA",
//                "ReceiverSortCode"=>"68000",
//            ),
//        );
//
//        $body = json_encode($payload, JSON_PRETTY_PRINT);
//
//        info("Sending to endpoint...");
//        info($ENDPOINT);
//
//        info("payload...");
//        info($body);
//
//        $ch = curl_init();
//
//        curl_setopt($ch, CURLOPT_URL, $ENDPOINT); // point to endpoint
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//
//        curl_setopt($ch, CURLOPT_VERBOSE, true);
//        // curl_setopt($ch, CURLOPT_STDERR, $fp);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);  //data
//        curl_setopt($ch, CURLOPT_TIMEOUT, 60);// request time out
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '0'); // no ssl verifictaion
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, '0');
//
//
//        $result=curl_exec($ch);
//        curl_close($ch);
//
//        Log::info("Request sent. See response below...");
//        Log::info(json_encode(json_decode($result,true)));
//        Log::info("end of request");
//
//
//        if ($result != null)
//            $status = optional(json_decode($result,true)['TransactionResponse'])['TransactionStatus'];
//        else
//            $status = "ERROR";
//
//        if ($status == "SUCCESS") {
//
//            $ref = json_decode($result,true)['TransactionResponse']['ProcessedTransactionReference'];
//
//            return response()->json([
//                'success' => true,
//                'message' => "Transaction has been processed successfully. Transaction reference: ".$ref,
//            ], 200);
//        } else{
//
//            if ($result != null)
//                $error = json_decode($result,true)['TransactionResponse']['ErrorDetail'];
//            else
//                $error = "External bank not reachable. Please try again later";
//
//
//            return response()->json([
//                'success' => false,
//                'message' => "Transaction could not be completed. An error occurred: ".$error,
//            ], 200);
//        }
//
//    }

}
