<?php

namespace App\Http\Controllers;

use App\AuditTrail;
use App\BulkSms;
use App\CustomerProfile;
use App\AdvanceApplication;
use App\InvoiceDiscount;
use App\Jobs\SendSms;
use App\LoanRequest;
use App\LoanSchedule;
use App\User;
use App\UserGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class SmsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function bulk_sms() {
        $userGroups = UserGroup::all();

        return view('sms.bulk_sms')->with([
            'userGroups' => $userGroups,

        ]);
    }

    public function bulkSmsDT() {
        $sms = BulkSms::all();
        return DataTables::of($sms)

            ->editColumn('created_at', function ($sms) {
                return Carbon::parse($sms->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })
            ->editColumn('created_by', function ($sms) {
                return optional($sms->creator)->name;
            })

            ->make(true);

    }

    public  function create_group_bulk_sms(Request  $request){
        $data = request()->validate([
            'user_group'  => 'required',
            'message'  => 'required',
        ]);


        DB::transaction(function() use ($request) {

            $bulkSms = new BulkSms();
            $bulkSms->recipients = UserGroup::find($request->user_group)->name;
            $bulkSms->message = $request->message;
            $bulkSms->created_by = auth()->user()->id;
            $bulkSms->saveOrFail();

            $targetGroup = User::where('user_group', $request->user_group)->get();

            foreach ($targetGroup as $target){
                SendSms::dispatch($target->phone_no, $request->message);
            }

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Sent bulk SMS to '.optional(UserGroup::find($request->user_group))->name,
            ]);

            request()->session()->flash('success', 'Bulk SMS to target group has been scheduled successfully');
        });
        return redirect()->back();
    }

    public  function create_upload_bulk_sms(Request  $request){
        $data = request()->validate([
            'file'  => 'required',
            'message'  => 'required',
        ]);


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


                $recipients = "";
//                dd($importData_arr);
                // Insert to MySQL database
                foreach($importData_arr as $importData){

                    $phone = $importData[0];

                    $firstCharacter = substr($phone, 0, 1);
                    if ($firstCharacter == "0"){
                        $str = ltrim($phone, '0');
                        $phone = "254".$str;
                    }

                    $recipients .= $phone . ", ";

                    SendSms::dispatch($phone, $request->message);

                }


                $bulkSms = new BulkSms();
                $bulkSms->recipients = $recipients;
                $bulkSms->message = $request->message;
                $bulkSms->created_by = auth()->user()->id;
                $bulkSms->saveOrFail();


                AuditTrail::create([
                    'created_by' => auth()->user()->id,
                    'action' => 'Sent bulk SMS ==> '.$request->message,
                ]);


                Session::flash('success','Message have been queued to send successfully.');
            }else{
                Session::flash('warning','File too large. File must be less than 2MB.');
            }

        }else{
            Session::flash('warning','Invalid File Extension.');
        }

        return redirect()->back();

    }

    public  function create_specified_bulk_sms(Request  $request){
        $data = request()->validate([
            'recipients'  => 'required',
            'message'  => 'required',
        ]);


        DB::transaction(function() use ($request) {

            $bulkSms = new BulkSms();
            $bulkSms->recipients = $request->recipients;
            $bulkSms->message = $request->message;
            $bulkSms->created_by = auth()->user()->id;
            $bulkSms->saveOrFail();

            $targetGroup = explode (",", $request->recipients);


            foreach ($targetGroup as $target){
                SendSms::dispatch($target, $request->message);
            }

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Sent bulk SMS to: '.$request->recipients,
            ]);

            request()->session()->flash('success', 'Bulk SMS to specified recipients has been scheduled successfully');
        });
        return redirect()->back();
    }

    public  function create_custom_bulk_sms(Request  $request){
        $data = request()->validate([
            'user_group'  => 'required',
            'message'  => 'required',
        ]);


        DB::transaction(function() use ($request) {

            switch ($request->user_group){
                case 1:
                    //Checkoff Customers
                    $bulkSms = new BulkSms();
                    $bulkSms->recipients = 'Checkoff Customers';
                    $bulkSms->message = $request->message;
                    $bulkSms->created_by = auth()->user()->id;
                    $bulkSms->saveOrFail();

                    $custs = CustomerProfile::where('is_checkoff', true)->get();

                    foreach ($custs as $cust){
                        SendSms::dispatch(optional($cust->user)->phone_no, $request->message);
                    }

                    AuditTrail::create([
                        'created_by' => auth()->user()->id,
                        'action' => 'Sent bulk SMS to Checkoff Customers'
                    ]);

                    request()->session()->flash('success', 'Bulk SMS to Checkoff Customers has been scheduled successfully');

                    break;

                case 2:

                    //Customers with Active Loans
                    $bulkSms = new BulkSms();
                    $bulkSms->recipients = 'Customers with Active Loans';
                    $bulkSms->message = $request->message;
                    $bulkSms->created_by = auth()->user()->id;
                    $bulkSms->saveOrFail();


                    $lrqsts = LoanRequest::whereIn('repayment_status',['PENDING','PARTIALLY_PAID'])->get();

                    foreach ($lrqsts as $lrqst){
                        SendSms::dispatch(optional($lrqst->user)->phone_no, $request->message);
                    }

                    AuditTrail::create([
                        'created_by' => auth()->user()->id,
                        'action' => 'Sent bulk SMS to Customers with Active Loans'
                    ]);

                    request()->session()->flash('success', 'Bulk SMS to Customers with Active Loans has been scheduled successfully');

                    break;

                case 3:

                    //Customers with amendment loans
                    $bulkSms = new BulkSms();
                    $bulkSms->recipients = 'Customers with amendment loans';
                    $bulkSms->message = $request->message;
                    $bulkSms->created_by = auth()->user()->id;
                    $bulkSms->saveOrFail();


                    //inua
                    $inuaAmends = AdvanceApplication::where('Quicksava_status','AMENDMENT')
                        ->orWhere('hr_status','AMENDMENT')
                        ->get();

                    foreach ($inuaAmends as $inuaAmend){
                        SendSms::dispatch(optional($inuaAmend->user)->phone_no, $request->message);
                    }


                    AuditTrail::create([
                        'created_by' => auth()->user()->id,
                        'action' => 'Sent bulk SMS to Customers with amendment loans'
                    ]);

                    request()->session()->flash('success', 'Bulk SMS to Customers with amendment loans has been scheduled successfully');

                    break;

                case 4:

                    //Customers with arrears
                    $bulkSms = new BulkSms();
                    $bulkSms->recipients = 'Customers with arrears';
                    $bulkSms->message = $request->message;
                    $bulkSms->created_by = auth()->user()->id;
                    $bulkSms->saveOrFail();

                    $loanSchedules = LoanSchedule::whereDate('payment_date','<', Carbon::today())
                        ->whereIn('status',['UNPAID','PARTIALLY_PAID'])
                        ->get();

                    foreach ($loanSchedules as $loanSchedule){
                        SendSms::dispatch(optional(optional($loanSchedule->loan)->user)->phone_no, $request->message);
                    }

                    AuditTrail::create([
                        'created_by' => auth()->user()->id,
                        'action' => 'Sent bulk SMS to Customers with arrears'
                    ]);

                    request()->session()->flash('success', 'Bulk SMS to Customers with arrears has been scheduled successfully');

                    break;

            }
        });
        return redirect()->back();
    }


}
