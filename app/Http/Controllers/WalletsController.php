<?php

namespace App\Http\Controllers;

use App\Alert;
use App\AuditTrail;
use App\CustomerProfile;
use App\Exports\AllWallets;
use App\Exports\TodayWalletTransactions;
use App\Exports\WalletTransactions;
use App\MpesaCharge;
use App\User;
use App\UserGroup;
use App\Wallet;
use App\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yajra\DataTables\Facades\DataTables;

class WalletsController extends Controller
{
    public function __construct() {
        $this->middleware(['auth']);
    }

    public function wallet($entity, $walletId) {

        $wallet = Wallet::find($walletId);

        if (is_null($wallet))
           abort(404,"Wallet not found");

        $owner = "Wallet owner not found";
        if ($entity == "customer"){
            $user = User::where('wallet_id',$walletId)->first();
            $owner = $user->name;
        }elseif ($entity == "company"){
            $company = Company::where('wallet_id',$walletId)->first();
            $owner = $company->business_name;
        }

        return view('wallet')->with([
            'wallet' => $wallet,
            'owner' => $owner,
        ]);
    }

    public function wallet_transactionsDT($walletId) {
        $transactions = WalletTransaction::where('wallet_id', $walletId)->get();
        return DataTables::of($transactions)

            ->editColumn('created_at', function ($transactions) {
                return Carbon::parse($transactions->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('amount', function ($transactions) {
                return number_format($transactions->getRawOriginal('amount'));
            })

            ->editColumn('previous_balance', function ($transactions) {
                return number_format($transactions->previous_balance);
            })

            ->editColumn('transaction_type', function ($transactions) {
                return $transactions->transaction_type == "CR" ? '<span class="badge pill badge-success">CREDIT</span>' : '<span class="badge pill badge-warning">DEBIT</span>';
            })


            ->rawColumns(['transaction_type'])

            ->make(true);

    }

    public function freeze_wallet(Request $request) {

        $this->validate($request, [
            'id' => 'required|exists:wallets,id',
        ]);

        $wallet = Wallet::find($request->id);

        if (is_null($wallet))
            abort(404);

        $wallet->active = false;
        $wallet->update();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Frozen wallet ID'.$request->id.' belonging to '.$request->owner,
        ]);

        Session::flash("success", "Wallet has been frozen. User CAN NOT withdraw funds.");

        return redirect()->back();
    }

    public function activate_wallet(Request $request) {

        $this->validate($request, [
            'id' => 'required|exists:wallets,id',
        ]);

        $wallet = Wallet::find($request->id);

        if (is_null($wallet))
            abort(404);

        $wallet->active = true;
        $wallet->update();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Activated wallet ID'.$request->id.' belonging to '.$request->owner,
        ]);

        Session::flash("success", "Wallet has been activated. User CAN withdraw funds.");

        return redirect()->back();
    }

    public function withdraw (Request $request){

        $this->validate($request, [
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required',
        ]);


        $wallet = Wallet::find($request->wallet_id);


        if ($wallet->active == false){
            Session::flash("warning", "The wallet is frozen. Please activate before withdrawing");
            return redirect()->back();
        }


        $owner = User::where('wallet_id', $request->wallet_id)->first();

        if (is_null($owner)){
            $company = Company::where('wallet_id', $request->wallet_id)->first();
            $owner = $company->owner;
        }

        if (is_null($owner)){
            Session::flash("warning", "Wallet owner not found. transaction cancelled, please contact system admin.");
            return redirect()->back();
        }



        if ($request->amount > $wallet->current_balance){
            Session::flash("warning", "Insufficient balance. Wallet doesn't have enough funds for this withdrawal");
            return redirect()->back();
        }

        if ($request->amount < 10){
            Session::flash("warning", "You can not withdraw less than Ksh. 10 per transaction");
            return redirect()->back();
        }

        if ($request->amount > 150000){
            Session::flash("warning", "You can not withdraw more than Ksh. 150,000 per transaction");
            return redirect()->back();
        }

        $charge = MpesaCharge::where('min', '<=',$request->amount)->where('max', '>=',$request->amount)->first();

        if (is_null($charge)){
            Session::flash("warning", "Unable to determine transaction charge. Please contact system admin.");
            return redirect()->back();
        }

        $actualWithdrawal = $request->amount - $charge->charge;

        $timestamp = Carbon::now()->getTimestamp();

        //insert into queue
        //WithdrawMoney will be called by artisan command, and it's data passed from the queue

        $payload = array(
            "wallet_id"=>$wallet->id,
            "recipient"=>$owner->phone_no,
            "amount"=>$actualWithdrawal,
            "randomID"=>$timestamp."FORCED-M-PESA-WITHDRAW",
        );


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

        $message = "Forced Wallet withdrawal alert. Amount withdrawn: ".number_format($request->amount,2).
            ". Recipient: ".$request->msisdn.". ".$name.'. Initiated by '.auth()->user()->name;

        foreach ($alerts as $alert){
            send_sms($alert->recipient, $message);
        }
        // }


        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Force Withdrew wallet ID'.$request->wallet_id.' of Ksh.'.number_format($actualWithdrawal,2).' belonging to '.$name,
        ]);

        Session::flash("success", "Transaction has been queued for processing");
        return redirect()->back();
    }



    public function today_transactions() {
        return view('wallet.today_wallet_transactions');
    }
    public function todayTransactionsDT() {
        $transactions = WalletTransaction::whereDate('created_at',Carbon::now())
            ->orderBy('id', 'desc')
            ->get();
        return DataTables::of($transactions)

            ->editColumn('name', function ($transactions) {
                $user = User::where('wallet_id',$transactions->wallet_id)->first();

                if (is_null($user)){
                    //get for company
                    $company = Company::where('wallet_id',$transactions->wallet_id)->first();

                    return is_null($company) ? "" : $company->business_name;

                }else{
                   return $user->surname.' '.$user->name;
                }
            })

            ->editColumn('created_at', function ($transactions) {
                return Carbon::parse($transactions->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('amount', function ($transactions) {
                return number_format($transactions->getRawOriginal('amount'));
            })

            ->editColumn('previous_balance', function ($transactions) {
                return number_format($transactions->previous_balance);
            })

            ->editColumn('transaction_type', function ($transactions) {
                return $transactions->transaction_type == "CR" ? '<span class="badge pill badge-success">CREDIT</span>' : '<span class="badge pill badge-warning">DEBIT</span>';
            })
            ->addColumn('actions', function($transactions){ // add custom column
                $actions = '<div class="align-content-center">';

                $user = User::where('wallet_id',$transactions->wallet_id)->first();
                if (is_null($user)){
                    //get for company
                    $company = Company::where('wallet_id',$transactions->wallet_id)->first();
                    if (!is_null($company)){
                        $actions .= '<a href="' . url('wallet/company' ,  $transactions->wallet_id) . '"
                                class="btn btn-primary btn-link btn-sm">
                                <i class="material-icons">visibility</i> View Wallet</a>';
                    }

                }else{
                    $actions .= '<a href="' . url('wallet/customer' ,  $transactions->wallet_id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View Wallet</a>';
                }


                $actions .= '</div>';
                return $actions;
            })


            ->rawColumns(['actions','transaction_type'])

            ->make(true);
    }
    public function exportTodayWalletTransactions() {
        return Excel::download(new TodayWalletTransactions(), 'todays_transactions.xlsx');
    }


    public function all_wallets() {
        return view('wallet.all_wallets');
    }
    public function allWalletsDT() {
        $wallet = Wallet::orderBy('current_balance', 'desc')
            ->get();
        return DataTables::of($wallet)

            ->editColumn('name', function ($wallet) {
                $user = User::where('wallet_id',$wallet->id)->first();

                if (is_null($user)){
                    //get for company
//                    $company = Company::where('wallet_id',$wallet->id)->first();
//
//                    return is_null($company) ? "" : $company->business_name;
                    return "";

                }else{
                   return $user->surname.' '.$user->name;
                }
            })

            ->editColumn('is_checkoff', function ($wallet) {
                $user = User::where('wallet_id',$wallet->id)->first();
                if (is_null($user)){
                    return 'NO';
                }else{
                    $custProfile = CustomerProfile::where('user_id',$user->id)->first();
                    if (is_null($custProfile))
                        return 'NO';
                    else
                        return $custProfile->is_checkoff ? 'YES' : 'NO';
                }
            })

            ->editColumn('current_balance', function ($wallet) {
                return 'KES'. number_format($wallet->current_balance,2);
            })

            ->editColumn('created_at', function ($wallet) {
                return Carbon::parse($wallet->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('status',function ($wallet) {
                if ($wallet->active == true){
                    return '<span class="badge pill badge-success">ACTIVE</span>';
                }else{
                    return '<span class="badge pill badge-warning">FROZEN</span>';
                }
            })

            ->addColumn('actions', function($wallet){ // add custom column
                $actions = '<div class="align-content-center">';

                $user = User::where('wallet_id',$wallet->id)->first();
                if (is_null($user)){
                    //get for company
//                    $company = Company::where('wallet_id',$wallet->id)->first();
//                    if (!is_null($company)){
//                        $actions .= '<a href="' . url('wallet/company' ,  $wallet->id) . '"
//                                class="btn btn-primary btn-link btn-sm">
//                                <i class="material-icons">visibility</i> View Wallet</a>';
//                    }

                }else{
                    $actions .= '<a href="' . url('wallet/customer' ,  $wallet->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View Wallet</a>';
                }


                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','status'])


            ->make(true);
    }
    public function exportAllWallets() {
        return Excel::download(new AllWallets(), 'all_wallets.xlsx');
    }


    public function all_wallet_transactions() {
        return view('wallet.all_wallet_transactions');
    }
    public function allWalletTransactionsDT() {
        $transactions = WalletTransaction::orderBy('id', 'desc')->get();
        return DataTables::of($transactions)

            ->editColumn('name', function ($transactions) {
                $user = User::where('wallet_id',$transactions->wallet_id)->first();

                if (is_null($user)){
                    //get for company
                    $company = Company::where('wallet_id',$transactions->wallet_id)->first();

                    return is_null($company) ? "" : $company->business_name;

                }else{
                   return $user->surname.' '.$user->name;
                }
            })

            ->editColumn('created_at', function ($transactions) {
                return Carbon::parse($transactions->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('amount', function ($transactions) {
                return number_format($transactions->getRawOriginal('amount'));
            })

            ->editColumn('previous_balance', function ($transactions) {
                return number_format($transactions->previous_balance);
            })

            ->editColumn('transaction_type', function ($transactions) {
                return $transactions->transaction_type == "CR" ? '<span class="badge pill badge-success">CREDIT</span>' : '<span class="badge pill badge-warning">DEBIT</span>';
            })
            ->addColumn('actions', function($transactions){ // add custom column
                $actions = '<div class="align-content-center">';

                $user = User::where('wallet_id',$transactions->wallet_id)->first();
                if (is_null($user)){
                    //get for company
                    $company = Company::where('wallet_id',$transactions->wallet_id)->first();
                    if (!is_null($company)){
                        $actions .= '<a href="' . url('wallet/company' ,  $transactions->wallet_id) . '"
                                class="btn btn-primary btn-link btn-sm">
                                <i class="material-icons">visibility</i> View Wallet</a>';
                    }

                }else{
                    $actions .= '<a href="' . url('wallet/customer' ,  $transactions->wallet_id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View Wallet</a>';
                }


                $actions .= '</div>';
                return $actions;
            })

            ->rawColumns(['actions','transaction_type'])

            ->make(true);
    }
    public function exportAllWalletTransactions() {
        return Excel::download(new WalletTransactions(), 'all_transactions.xlsx');
    }




}
