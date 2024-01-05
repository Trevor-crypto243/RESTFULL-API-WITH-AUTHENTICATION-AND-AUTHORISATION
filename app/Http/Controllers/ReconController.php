<?php

namespace App\Http\Controllers;

use App\AuditTrail;
use App\BulkDisbursement;
use App\C2bRecon;
use App\Escrow;
use App\Jobs\SendMoney;
use App\LoanProduct;
use App\MpesaCharge;
use App\MpesaWithdrawal;
use App\Repositories\Data;
use App\SuspenseAmount;
use App\User;
use App\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;
use function Symfony\Component\VarDumper\Dumper\esc;

class ReconController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function suspense() {
        return view('recon.suspense');
    }
    public function suspenseDT() {
        $suspense = SuspenseAmount::all();
        return DataTables::of($suspense)

            ->editColumn('created_at', function ($suspense) {
                return Carbon::parse($suspense->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('amount', function ($suspense) {
                return 'KES '. number_format($suspense->amount);
            })

            ->editColumn('refunded', function ($suspense) {
                return $suspense->refunded ? '<span class="badge pill badge-success">YES</span>' : '<span class="badge pill badge-danger">NO</span>';
            })


            ->addColumn('actions', function($suspense){ // add custom column
                $actions = '<div class="align-content-center">';

                if (!$suspense->refunded){
                    $actions .= '<a href="' . route('suspense-refund' ,  $suspense->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">autorenew</i> Refund</a>';
                }




//                $actions .= '<a href="' . url('/users/details' ,  $user->id) . '"
//                    class="btn btn-info btn-link btn-sm" >
//                    <i class="material-icons">preview</i> View Merchant</a>';
//
//
//                if (auth()->user()->role->has_perm([8])) {
//                    $actions .= '<form action="'. route('delete-merchant',  $merchants->id) .'" style="display: inline;" method="post" class="del_merchant_form">';
//                    $actions .= method_field('DELETE');
//                    $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';
//                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','refunded'])

            ->make(true);

    }
    public function refundSuspense($id) {
        return redirect()->back();
    }



    public function c2b() {
        return view('recon.c2b');
    }
    public function c2bDT() {
        $c2brecon = C2bRecon::all();
        return DataTables::of($c2brecon)

            ->editColumn('created_at', function ($c2brecon) {
                return Carbon::parse($c2brecon->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('created_by', function ($c2brecon) {
                return optional($c2brecon->creator)->name;
            })

            ->editColumn('amount', function ($c2brecon) {
                return 'KES '. number_format($c2brecon->amount);
            })

            ->make(true);

    }
    public function create_c2b(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required',
            'phone_no' => 'required|max:255',
            'type' => 'required',
            'description' => 'required',
        ],[
//            'phone_no.exists' => 'The phone number is not registered to any Quicksava account',
        ]);


        $user = User::where('phone_no',ltrim($request->phone_no,"+"))->first();

        DB::transaction(function() use($request, $user) {


            $trxCode = is_null($request->transaction_code) ? 'N/A' : $request->transaction_code;

            $wallet = $user->wallet;

            $prevBal = $wallet->current_balance;
            $newBal = $prevBal+$request->amount;

            $wallet->current_balance = $newBal;
            $wallet->previous_balance = $prevBal;
            $wallet->update();

            //save to wallet transactions
            $walletTransaction = new WalletTransaction();
            $walletTransaction->wallet_id = $wallet->id;
            $walletTransaction->amount = $request->amount;
            $walletTransaction->previous_balance = $prevBal;
            $walletTransaction->transaction_type = 'CR';
            $walletTransaction->source = $request->type;
            $walletTransaction->trx_id = $trxCode;
            $walletTransaction->narration = $request->description;
            $walletTransaction->saveOrFail();

            //create c2bRecon
            $c2bRecon = new C2bRecon();
            $c2bRecon->created_by = auth()->user()->id;
            $c2bRecon->phone_no = $request->phone_no;
            $c2bRecon->type = $request->type;
            $c2bRecon->transaction_code =$trxCode;
            $c2bRecon->amount = $request->amount;
            $c2bRecon->description = $request->description;
            $c2bRecon->saveOrFail();



            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Manual C2B credit to user #'.$user->id. ' ('.$user->name.') of KES'.$request->amount,
            ]);

            send_sms($user->phone_no,"Your account on Quicksava Credit has been credited with KES ".number_format($request->amount)." for a ".$request->type.
                " transaction. Your new Quicksava wallet balance is KES ".number_format($user->wallet->current_balance));

            Session::flash("success", "C2B credit has been completed successfully");

        });

        return redirect()->back();
    }




    public function bulk_disburse() {

        $amountToday = BulkDisbursement::where('status', 'SUCCEEDED')->whereDate('created_at', Carbon::now())->sum('amount') ;
        $amountTotal = BulkDisbursement::where('status', 'SUCCEEDED')->sum('amount') ;

        $recipientsToday = BulkDisbursement::where('status', 'SUCCEEDED')->whereDate('created_at', Carbon::now())->distinct()->count('msisdn') ;
        $recipientsTotal = BulkDisbursement::where('status', 'SUCCEEDED')->distinct()->count('msisdn') ;


        return view('recon.bulk_disburse')->with(['amountToday'=>$amountToday,'amountTotal'=>$amountTotal, 'recipientsToday'=>$recipientsToday,'recipientsTotal'=>$recipientsTotal]);
    }
    public function disburseDT() {

        if (Data::isValid($_GET, 'date_range')) {
            $date_range = $_GET['date_range'];
            Log::info($date_range);
            $dates = explode(' - ', $date_range);
            $from_date = date('Y/m/d 00:00:00', strtotime($dates[0]));
            $to_date = date('Y/m/d 23:59:59', strtotime($dates[1]));


            $disbursements = BulkDisbursement::whereBetween('created_at', [$from_date, $to_date])->get();
        }else{
            $disbursements = BulkDisbursement::whereDate('created_at', Carbon::now())->get();
        }

        return DataTables::of($disbursements)

            ->editColumn('created_at', function ($disbursements) {
                return Carbon::parse($disbursements->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('created_by', function ($disbursements) {
                return optional( $disbursements->creator)->name;
            })

            ->editColumn('amount', function ($disbursements) {
                return 'KES '. number_format($disbursements->amount);
            })

            ->editColumn('status', function ($disbursements) {

                if ($disbursements->status == "SUCCEEDED")
                    $pill = '<span class="badge pill badge-success">'.$disbursements->status.'</span>';
                elseif ($disbursements->status == "FAILED")
                    $pill = '<span class="badge pill badge-danger">'.$disbursements->status.'</span>';
                else
                    $pill = '<span class="badge pill badge-info">'.$disbursements->status.'</span>';

                return $pill;
            })
            ->rawColumns(['status'])

            ->make(true);

    }
    public function create_single_bulk_disburse(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required',
            'narration' => 'required',
            'phone_no' => 'required|max:255',

        ]);

        $timestamp = Carbon::now()->getTimestamp();

        SendMoney::dispatch(auth()->user()->id,ltrim($request->phone_no,"+"), $request->amount, auth()->user()->id.'DISBURSE'.$timestamp, $request->narration);

        return redirect()->back();
    }
    public function create_upload_bulk_disburse(Request $request)
    {

        $file = $request->file('file');

        // File Details
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $tempPath = $file->getRealPath();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        // Valid File Extensions
        $valid_extension = array("csv");

        // 2MB in Bytes
        $maxFileSize = 2097152;

        // Check file extension
        if(in_array(strtolower($extension),$valid_extension)){

            // Check file size
            if($fileSize <= $maxFileSize){

                // File upload location
                $location = 'public/uploads';

                // Upload file
                $file->move($location,$filename);

                // Import CSV to Database
                $filepath = public_path($location."/".$filename);

                // Reading file
                $file = fopen($filepath,"r");

                $importData_arr = array();
                $i = 0;

                while (($filedata = fgetcsv($file, 10000, ",")) !== FALSE) {
                    $num = count($filedata );

                    if($i == 0){
                        $i++;
                        continue;
                    }
                    for ($c=0; $c < $num; $c++) {

//                        if($c == 0){
//                            $c++;
//                            continue;
//                        }
                        $importData_arr[$i][] = $filedata [$c];
                    }
                    $i++;
                }
                fclose($file);

//                dd($importData_arr);
                // Insert to MySQL database
                foreach($importData_arr as $importData){

                    $phone = $importData[0];
                    $amount = $importData[1];
                    $narration = $importData[2];

                    $timestamp = Carbon::now()->getTimestamp().rand(1000,9999);

                    $firstCharacter = substr($phone, 0, 1);
                    if ($firstCharacter == "0"){
                        $str = ltrim($phone, '0');
                        $phone = "254".$str;
                    }

                    SendMoney::dispatch(auth()->user()->id,$phone, $amount, auth()->user()->id.'DISBURSE'.$timestamp, $narration);
                }

                Session::flash('success','recipients have been uploaded successfully. Disbursement in progress.');
            }else{
                Session::flash('warning','File too large. File must be less than 2MB.');
            }

        }else{
            Session::flash('warning','Invalid File Extension.');
        }


        return redirect()->back();
    }


    public function b2c() {
        return view('recon.b2c');
    }
    public function b2cDT() {
        $escrows = Escrow::where('complete',false)->get();
        return DataTables::of($escrows)

            ->editColumn('created_at', function ($escrows) {
                return Carbon::parse($escrows->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('amount', function ($escrows) {
                return number_format($escrows->amount,2);
            })

            ->editColumn('wallet', function ($escrows) {

                $user = User::where('wallet_id', $escrows->wallet_id)->first();

                if (!is_null($user)){
                    return '<a href="' . url('wallet/customer/'.$escrows->wallet_id).'"
                            class="btn btn-primary btn-link btn-sm" target="_blank">
                    <i class="material-icons">account_balance_wallet</i> View Wallet</a>';
                }else{
                    return '<a href="' . url('wallet/company/'.$escrows->wallet_id).'"
                            class="btn btn-primary btn-link btn-sm" target="_blank">
                    <i class="material-icons">account_balance_wallet</i> View Wallet</a>';
                }
            })


            ->addColumn('actions', function($escrows){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<button
                    class="btn btn-primary btn-link btn-sm recon-b2c-btn" acs-id="' . $escrows->id . '"
                    acs-msisdn="' . $escrows->msisdn . '">
                    <i class="material-icons">autorenew</i> Reconcile</button>';


                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','wallet'])

            ->make(true);

    }
    public function reconcile_b2c(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:escrows,id',
            'status' => 'required',
        ]);


        $escrow = Escrow::find($request->id);

        if ($request->status == "SUCCEEDED"){
            if (!$request->filled('transaction_code')){
                Session::flash("warning", "Transaction code MUST be provided if transaction SUCCEEDED");
                return redirect()->back();
            }

            if (!$request->filled('recipient_name')){
                Session::flash("warning", "Recipient name MUST be provided if transaction SUCCEEDED");
                return redirect()->back();
            }


            //proceed with success

            $mpesaWithdrawal = new MpesaWithdrawal();
            $mpesaWithdrawal->amount = $escrow->amount;
            $mpesaWithdrawal->receipt = $request->transaction_code;
            $mpesaWithdrawal->msisdn = $escrow->msisdn;
            $mpesaWithdrawal->date_time = $escrow->created_at;
            $mpesaWithdrawal->name = $request->recipient_name;
            $mpesaWithdrawal->recipient_registered = 1;
            $mpesaWithdrawal->utility_balance = 0;
            $mpesaWithdrawal->mmf_balance = 0;
            $mpesaWithdrawal->created_at = $escrow->created_at;
            $mpesaWithdrawal->updated_at = $escrow->updated_at;
            $mpesaWithdrawal->saveOrFail();

            $chargeRslt = MpesaCharge::where('min', '<=',$escrow->amount)->where('max', '>=',$escrow->amount)->first();
            $charge = is_null($chargeRslt) ? 22.4 : $chargeRslt->charge;


            $wallet = $escrow->wallet;
            $prevBal = $wallet->current_balance+$escrow->amount+$charge;

            //save to wallet transactions
            $walletTransaction = new WalletTransaction();
            $walletTransaction->wallet_id = $wallet->id;
            $walletTransaction->amount = $escrow->amount;
            $walletTransaction->previous_balance = $prevBal;
            $walletTransaction->transaction_type = 'DR';
            $walletTransaction->source = 'Quicksava Wallet';
            $walletTransaction->trx_id = $request->transaction_code;
            $walletTransaction->narration = "Payment to ".$request->recipient_name;
            $walletTransaction->created_at = $escrow->created_at;
            $walletTransaction->updated_at = $escrow->updated_at;
            $walletTransaction->saveOrFail();


            $walletTransaction2 = new WalletTransaction();
            $walletTransaction2->wallet_id = $wallet->id;
            $walletTransaction2->amount = $charge;
            $walletTransaction2->previous_balance = $prevBal-$charge;
            $walletTransaction2->transaction_type = 'DR';
            $walletTransaction2->source = 'Quicksava Wallet';
            $walletTransaction2->trx_id = $request->transaction_code;
            $walletTransaction2->narration = "Withdrawal charge";
            $walletTransaction2->created_at = $escrow->created_at;
            $walletTransaction2->updated_at = $escrow->updated_at;
            $walletTransaction2->saveOrFail();

            $escrow->complete = true;
            $escrow->description = "Transaction succeeded, Reconciled manually";
            $escrow->status = "SUCCEEDED";
            $escrow->update();

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Reconciled transaction of Ksh '.number_format($escrow->amount). ' as SUCCEEDED. Transaction code:'.$request->transaction_code.' Recipient:'.$request->recipient_name,
            ]);


            Session::flash("success", "Transaction has been reconciled as SUCCEEDED");
            return redirect()->back();
        }else{
            //proceed with failure
            $escrow->complete = true;
            $escrow->description = "Transaction failed, No callback sent";
            $escrow->status = "FAILED";
            $escrow->update();

            $chargeRslt = MpesaCharge::where('min', '<=',$escrow->amount)->where('max', '>=',$escrow->amount)->first();
            $charge = is_null($chargeRslt) ? 22.4 : $chargeRslt->charge;

            $wallet = $escrow->wallet;
            Log::info("CURRENT WALLET BALANCE...".$wallet->current_balance);
            $prevBal = $wallet->current_balance;
            $newBal = $prevBal + $escrow->amount + $charge;

            $wallet->current_balance = $newBal;
            $wallet->previous_balance = $prevBal;
            $wallet->update();

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Reconciled transaction of Ksh '.number_format($escrow->amount). ' as FAILED. Wallet credited with same amount. Wallet ID:'.$wallet->id,
            ]);

            Session::flash("success", "Transaction has been reconciled as FAILED. Ksh. ".number_format($escrow->amount + $charge,2)." has been credited back to the wallet.");
            return redirect()->back();
        }
    }





}
