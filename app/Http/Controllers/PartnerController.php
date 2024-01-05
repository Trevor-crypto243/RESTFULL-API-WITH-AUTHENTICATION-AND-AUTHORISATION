<?php

namespace App\Http\Controllers;

use App\AuditTrail;
use App\CustomerProfile;
use App\Employee;
use App\EmployeeIncome;
use App\Employer;
use App\HrManager;
use App\AdvanceApplication;
use App\AdvanceLoanPeriodMatrix;
use App\AdvanceRepayment;
use App\LoanRepayment;
use App\LoanRequest;
use App\LoanSchedule;
use App\MtdTarget;
use App\Notifications\UserCreated;
use App\User;
use App\Wallet;
use App\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class PartnerController extends Controller
{

    protected $random_pass;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function employers() {
        return view('partners.employers');
    }

    public function employersDT() {
        $employers = Employer::all();
        return DataTables::of($employers)
            ->editColumn('business_logo', function ($employers) {
                return '<img src="'.$employers->business_logo_url.'" width="75" height="75" />';
            })

            ->addColumn('actions', function($employers){ // add custom column
                $actions = '<div class="align-content-center">';

                if (auth()->user()->role->has_perm([19])){
                    $actions .= '<button source="' . route('edit-employer-details' ,  $employers->id) . '"
                    class="btn btn-primary btn-link btn-sm edit-employers-btn" acs-id="'.$employers->id .'">
                    <i class="material-icons">edit</i> Edit</button>';
                }

                $actions .= '<a href="' . route('employer-details' ,  $employers->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View</a>';


//                $actions .= '<form action="'. route('delete-matrix',  $employers->id) .'" style="display: inline;" method="POST" class="del-matrix-form">';
//                $actions .= method_field('DELETE');
//                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','business_logo'])

            ->make(true);
//'. route('delete-matrix',  $matrix->id) .'
    }

    public  function create_employer(Request  $request){
        $data = request()->validate([
            'business_logo'  => 'required',
            'name'  => 'required',
            'description'  => 'required',
            'address'  => 'required',
            'reg_no'  => 'required',
            'email'  => 'required',
            'phone_no'  => 'required',
        ]);

        $exists = Employer::where('business_name',$request->name)->first();

        if (!is_null($exists)){
            request()->session()->flash('warning', 'An employer with a similar name already exists.');
            return redirect()->back();
        }


        $employer = new Employer();
        DB::transaction(function() use ($request,$employer) {

            $wallet = new Wallet();
            $wallet->current_balance = 0.0;
            $wallet->previous_balance = 0.0;
            $wallet->saveOrFail();

            $employer->wallet_id = $wallet->id;
            $employer->business_name = $request->name;
            $employer->business_desc = $request->description;
            $employer->business_address = $request->address;
            $employer->business_reg_no = $request->reg_no;
            $employer->business_email = $request->email;
            $employer->business_phone_no = $request->phone_no;

            //upload image to s3 here
            $path = $request->file('business_logo')->storePublicly('business_logos', 's3');

            info("PATH::".$path);

            $employer->business_logo_filename = basename($path);
            $employer->business_logo_url = Storage::disk('s3')->url($path);

            $employer->saveOrFail();


            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Created new employer ==> '.$employer->business_name
            ]);

            request()->session()->flash('success', 'Employer has been created successfully');
        });
        return redirect()->back();
    }

    public function partner_details($id)
    {
        $employer = Employer::find($id);
        return $employer;
    }

    public function update_partner(Request $request)
    {
        $data = request()->validate([
            'id' => 'required',
            'name' => ['required','max:255',\Illuminate\Validation\Rule::unique('employers','business_name')->ignore($request->id)],
            'description'  => 'required',
            'address'  => 'required',
            'reg_no'  => 'required',
            'email'  => 'required',
            'phone_no'  => 'required',
        ]);

        $employer = Employer::find($request->id);

        if (is_null($employer)){
            request()->session()->flash('warning', 'Invalid employer. Please try again');
            return redirect()->back();
        }

        $employer->business_name = $request->name;
        $employer->business_desc = $request->description;
        $employer->business_address = $request->address;
        $employer->business_reg_no = $request->reg_no;
        $employer->business_email = $request->email;
        $employer->business_phone_no = $request->phone_no;

        if ($request->hasFile('business_logo')){
            //upload image to s3 here
            $path = $request->file('business_logo')->storePublicly('business_logos', 's3');

            info("PATH::".$path);

            $employer->business_logo_filename = basename($path);
            $employer->business_logo_url = Storage::disk('s3')->url($path);
        }

        $employer->update();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Edited employer ('.$request->name.') with ID '.$request->id,
        ]);

        request()->session()->flash('success', 'Employer has been updated.');

        return redirect()->back();
    }

    public function employer_details($id) {

        $employer = Employer::find($id);

        if (is_null($employer))
            abort(404, "Employer does not exist");

        return view('partners.employer_details')->with(['employer'=>$employer]);
    }

    public function partnerEmployeesDT($id) {
        $employees = Employee::where('employer_id',$id)->get();
        return DataTables::of($employees)
            ->editColumn('selfie', function ($employees) {

                return '<a href="' . $employees->passport_photo_url . '" target="_blank" > <img src="'.$employees->passport_photo_url.'" width="75" height="75" />';
            })

            ->addColumn('name', function ($employees) {
                return optional($employees->user)->name;
            })

            ->addColumn('id_no', function ($employees) {
                return optional($employees->user)->id_no;
            })

            ->editColumn('basic_salary', function ($employees) {
                return number_format($employees->basic_salary);
            })

            ->editColumn('net_salary', function ($employees) {
                return number_format($employees->net_salary);
            })

            ->editColumn('max_limit', function ($employees) {
                return number_format($employees->max_limit);
            })

            ->addColumn('actions', function($employees){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . url('employees/details',$employees->id) . '"
                    class="btn btn-primary btn-link btn-sm edit-matrix-btn" >View Employee</button>';

                $actions .= '<a href="' . $employees->latest_payslip_url . '" target="_blank"
                    class="btn btn-primary btn-link btn-sm edit-matrix-btn" >Payslip</button>';

//                $actions .= '<a href="' . $employees->id_url . '" target="_blank"
//                    class="btn btn-primary btn-link btn-sm" >
//                    <i class="material-icons">badge</i> ID Front</button>';
//
//                $actions .= '<a href="' . $employees->id_back_url . '" target="_blank"
//                    class="btn btn-primary btn-link btn-sm" >
//                    <i class="material-icons">badge</i> ID Back</button>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','selfie'])

            ->make(true);
    }

    public function partnerEmployeeIncomesDT($id) {
        $employeeIncomes = EmployeeIncome::where('employer_id',$id)->get();
        return DataTables::of($employeeIncomes)

            ->editColumn('gross_salary', function ($employeeIncomes) {
                return number_format($employeeIncomes->gross_salary);
            })

            ->editColumn('basic_salary', function ($employeeIncomes) {
                return number_format($employeeIncomes->basic_salary);
            })

            ->editColumn('net_salary', function ($employeeIncomes) {
                return number_format($employeeIncomes->net_salary);
            })

            ->editColumn('employment_date', function ($employeeIncomes) {
                return Carbon::parse($employeeIncomes->employment_date)->isoFormat('MMMM Do YYYY')." (".Carbon::parse($employeeIncomes->employment_date)->diffForHumans().")";
            })
            ->make(true);
    }

    public function upload_incomes(Request $request)
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

                    $payroll_no = $importData[0];
                    $id_no = $importData[1];
                    $gross_salary = $importData[2];
                    $basic_salary = $importData[3];
                    $net_salary = $importData[4];
                    $employment_date = $importData[5];


                    EmployeeIncome::updateOrCreate(
                        ['employer_id' => $request->employer_id, 'payroll_no' => $payroll_no],
                        [
                            'id_no' => $id_no,
                            'gross_salary' => $gross_salary,
                            'basic_salary' => $basic_salary,
                            'net_salary' => $net_salary,
                            'employment_date' => $employment_date
                        ]
                    );

                    //update employee limits if exists
                    $employee = Employee::where('employer_id',$request->employer_id)->where('payroll_no',$payroll_no)->first();

                    if (!is_null($employee)){

                        $limit = $net_salary - $basic_salary/3;

                        $employee->limit_expiry = Carbon::now()->addMonths(6);
                        $employee->max_limit = $limit >= 0 ? $limit : 0;
                        $employee->employment_date = $employment_date;
                        $employee->update();
                    }

                }

                Session::flash('success','Employees have been uploaded successfully');
            }else{
                Session::flash('warning','File too large. File must be less than 2MB.');
            }

        }else{
            Session::flash('warning','Invalid File Extension.');
        }


        return redirect()->back();
    }

    public function upload_repayments(Request $request)
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

                    $message = "";

                    $payroll_no = $importData[0];
                    $amount = $importData[1];

                    $employee = Employee::where('employer_id',$request->employer_id)->where('payroll_no',$payroll_no)->first();
                    $wallet = null;
                    $receipt = $this->randomID();

                    $userId = 0;
                    if (is_null($employee)){
                        $employeeExists = false;
                    }
                    else{
                        $employeeExists = true;
                        $userId = $employee->user_id;

                        $wallet = $employee->user->wallet;
                    }


                    $inuaRepayment = new AdvanceRepayment();
                    $inuaRepayment->created_by = auth()->user()->id;
                    $inuaRepayment->employer_id = $request->employer_id;
                    $inuaRepayment->payroll_no = $payroll_no;
                    $inuaRepayment->amount = $amount;
                    $inuaRepayment->employee_exists = $employeeExists;
                    $inuaRepayment->saveOrFail();



                    //select inuaapplication
                    $inuaApplication = AdvanceApplication::where('Quicksava_status','ACCEPTED')
                        ->where('hr_status','ACCEPTED')
                        ->where('user_id',$userId)
                        ->whereIn('payment_status',['PENDING','PARTIALLY_PAID'])
                        ->orderBy('id','desc')
                        ->first();

                    if (is_null($inuaApplication)){
                        if (!is_null($employee)){
                            //save to users wallet
                            //update wallet
                            $prevBal = $wallet->current_balance;

                            //save to wallet transactions
                            $walletTransaction = new WalletTransaction();
                            $walletTransaction->wallet_id = $wallet->id;
                            $walletTransaction->amount = $amount;
                            $walletTransaction->previous_balance = $prevBal;
                            $walletTransaction->transaction_type = 'CR';
                            $walletTransaction->source = 'Remittance from HR';
                            $walletTransaction->trx_id = $receipt;
                            $walletTransaction->narration = "Received remittance from HR. No active loan found";
                            $walletTransaction->saveOrFail();


                            $wallet->current_balance = $wallet->current_balance + $amount;
                            $wallet->previous_balance = $prevBal;
                            $wallet->update();

                            $message = "Dear ".optional($employee->user)->name.", your wallet at Quicksava credit has been credited with Ksh."
                                .number_format($amount)." from your HR remittance at  "
                                .optional($employee->employer)->business_name.". Your new wallet balance is Ksh.".number_format($wallet->current_balance);

                            send_sms(optional($employee->user)->phone_no,$message);
                        }
                    }
                    else{
                        $loanRequest = LoanRequest::where('user_id',$userId)
                            ->where('loan_product_id',$inuaApplication->loan_product_id)
                            ->where('approval_status','APPROVED')
                            ->whereIn('repayment_status',['PENDING','PARTIALLY_PAID'])
                            ->first();

                        if (is_null($loanRequest)){
                            //save to users wallet
                            $prevBal = $wallet->current_balance;

                            //save to wallet transactions
                            $walletTransaction = new WalletTransaction();
                            $walletTransaction->wallet_id = $wallet->id;
                            $walletTransaction->amount = $amount;
                            $walletTransaction->previous_balance = $prevBal;
                            $walletTransaction->transaction_type = 'CR';
                            $walletTransaction->source = 'Remittance from HR';
                            $walletTransaction->trx_id = $receipt;
                            $walletTransaction->narration = "Received remittance from HR. No active loan found";
                            $walletTransaction->saveOrFail();


                            $wallet->current_balance = $wallet->current_balance + $amount;
                            $wallet->previous_balance = $prevBal;
                            $wallet->update();

                            $message = "Dear ".optional($employee->user)->name.", your wallet at Quicksava credit has been credited with Ksh."
                                .number_format($amount)." from your HR remittance at  "
                                .optional($employee->employer)->business_name.". Your new wallet balance is Ksh.".number_format($wallet->current_balance);

                            send_sms(optional($employee->user)->phone_no,$message);
                        }
                        else{
                            //continue with loan repayment
                            //update $inuaApplication payment_status to either PARTIALLY_PAID or PAID
                            //credit overflow to users weallet

                            /*
                             * do payment
                             *
                             */

                            //get first payment schedule
                            $paymentSchedule = LoanSchedule::where('loan_request_id',$loanRequest->id)
                                ->whereIn('status',['UNPAID','PARTIALLY_PAID'])
                                ->orderBy('id', 'asc')
                                ->first();

                            $amountInstructed = $amount;
                            $amountPaid = 0;

                            //update payment schedules
                            while ($amountInstructed > 0 && !is_null($paymentSchedule)){
                                $due = $paymentSchedule->scheduled_payment - $paymentSchedule->actual_payment_done;

                                if ($amountInstructed >= $due){
                                    $amountPaid += $due;

                                    $paymentSchedule->actual_payment_done = $paymentSchedule->actual_payment_done + $due;
                                    $paymentSchedule->status = 'PAID';
                                    $paymentSchedule->update();

                                    $amountInstructed = $amountInstructed - $due;
                                }else{
                                    $amountPaid += $amountInstructed;

                                    $paymentSchedule->actual_payment_done = $paymentSchedule->actual_payment_done + $amountInstructed;
                                    $paymentSchedule->status = 'PARTIALLY_PAID';
                                    $paymentSchedule->update();

                                    $amountInstructed = 0;
                                }


                                $paymentSchedule = LoanSchedule::where('loan_request_id',$loanRequest->id)
                                    ->whereIn('status',['UNPAID','PARTIALLY_PAID'])
                                    ->orderBy('id', 'asc')
                                    ->first();
                            }

                            //get loan balance after payment
                            $paymentSchedules = LoanSchedule::where('loan_request_id',$loanRequest->id)->get();
                            $due = 0;
                            $paid = 0;
                            foreach ($paymentSchedules as $paymentSchedule){
                                $paid += $paymentSchedule->actual_payment_done;
                                $due += $paymentSchedule->scheduled_payment;
                            }
                            $loanBalance = $due-$paid;

                            if ($amountPaid > 0){

                                //create loan repayment
                                $loanRepayment = new LoanRepayment();
                                $loanRepayment->loan_request_id = $loanRequest->id;
                                $loanRepayment->amount_repaid = $amountPaid;
                                $loanRepayment->outstanding_balance = $loanBalance;
                                $loanRepayment->transaction_receipt_number = $receipt;
                                $loanRepayment->payment_channel = 'Remittance by HR';
                                $loanRepayment->description = 'Received remittance from HR';
                                $loanRepayment->saveOrFail();



                                //update loan if completely paid
                                if ($loanBalance == 0){
                                    $loanRequest->repayment_status = 'PAID';
                                    $inuaApplication->payment_status = 'PAID';

                                    if ($amount <= $amountPaid){

                                        $message = "Dear ".optional($employee->user)->name.", Ksh.".number_format($amountPaid)." has been remitted by your HR at "
                                            .optional($employee->employer)->business_name." for your loan payment. Your current outstanding loan balance is Ksh. "
                                            .number_format($loanBalance);

                                        send_sms(optional($employee->user)->phone_no,$message);
                                    }

                                }else{
                                    $loanRequest->repayment_status = 'PARTIALLY_PAID';
                                    $inuaApplication->payment_status = 'PARTIALLY_PAID';

                                    $message = "Dear ".optional($employee->user)->name.", Ksh.".number_format($amountPaid)." has been remitted by your HR at "
                                        .optional($employee->employer)->business_name." for your loan payment. Your current outstanding loan balance is Ksh. "
                                        .number_format($loanBalance);

                                    send_sms(optional($employee->user)->phone_no,$message);
                                }
                                $loanRequest->update();
                                $inuaApplication->update();



                                //credit overflow

                                if ($amount > $amountPaid){
                                    //update wallet
                                    $prevBal = $wallet->current_balance;

                                    $overFlow = $amount - $amountPaid;

                                    //save to wallet transactions
                                    $walletTransaction = new WalletTransaction();
                                    $walletTransaction->wallet_id = $wallet->id;
                                    $walletTransaction->amount = $overFlow;
                                    $walletTransaction->previous_balance = $prevBal;
                                    $walletTransaction->transaction_type = 'CR';
                                    $walletTransaction->source = 'Loan Overpayment';
                                    $walletTransaction->trx_id = $receipt;
                                    $walletTransaction->narration = "Loan overpayment received from HR for loan ID #".$loanRequest->id;
                                    $walletTransaction->saveOrFail();


                                    $wallet->current_balance = $wallet->current_balance + $overFlow;
                                    $wallet->previous_balance = $prevBal;
                                    $wallet->update();

                                    $message = "Dear ".optional($employee->user)->name.", Ksh.".number_format($amountPaid)." has been remitted by your HR at "
                                        .optional($employee->employer)->business_name." for your loan payment. Your current outstanding loan balance is Ksh. "
                                        .number_format($loanBalance).". An excess of the remittance of Ksh. ".number_format($overFlow)." has been credited to your wallet. Your new wallet balance is Ksh.".number_format($wallet->current_balance);

                                    send_sms(optional($employee->user)->phone_no,$message);
                                }

                            }



                        }

                    }

                }

                Session::flash('success','Repayments have been uploaded successfully');
            }else{
                Session::flash('warning','File too large. File must be less than 2MB.');
            }

        }else{
            Session::flash('warning','Invalid File Extension.');
        }


        return redirect()->back();
    }

    public function partnerAdvanceRepaymentsDT($id) {
        $inuaRepayments = AdvanceRepayment::where('employer_id',$id)->get();
        return DataTables::of($inuaRepayments)

            ->editColumn('employee_exists', function ($inuaRepayments) {
                return $inuaRepayments->employee_exists ? 'YES' : 'NO';
            })

            ->editColumn('amount', function ($inuaRepayments) {
                return number_format($inuaRepayments->amount);
            })

            ->editColumn('payroll_no', function ($inuaRepayments) {
                return $inuaRepayments->payroll_no;
            })

            ->editColumn('created_by', function ($inuaRepayments) {
                return User::find($inuaRepayments->created_by)->name;
            })

            ->editColumn('created_at', function ($inuaRepayments) {
                return Carbon::parse($inuaRepayments->created_at)->isoFormat('MMMM Do YYYY');
            })
            ->make(true);
    }

    public  function create_hr(Request  $request){
        $data = request()->validate([
            'employer_id'  => 'required',
            'email' => 'required|email|max:255|unique:users,email',
            'phone_no' => 'required|max:255|unique:users,phone_no',
            'id_no' => 'required|max:255|unique:users,id_no',
            'name' => 'required',
            'surname' => 'required',
        ]);

        $exists = User::where('phone_no', ltrim($request->phone_no,"+"))->first();

        if (!is_null($exists)){
            request()->session()->flash('warning', 'The phone number has already been taken');
            return redirect()->back();
        }


        $this->random_pass = $this->randomPassword();

        DB::transaction(function() use($request) {

            $wallet = new Wallet();
            $wallet->current_balance = 0.0;
            $wallet->previous_balance = 0.0;
            $wallet->saveOrFail();


            $user = new User();
            $user->name = $request->name;
            $user->surname = $request->surname;
            $user->wallet_id = $wallet->id;
            $user->user_group = 2;//HR
            $user->phone_no = ltrim($request->phone_no,"+");
            $user->email = $request->email;
            $user->id_no = $request->id_no;
            $user->password = bcrypt($this->random_pass);

            if ($user->saveOrFail()){

                $hr = new HrManager();
                $hr->user_id = $user->id;
                $hr->employer_id = $request->employer_id;
                $hr->saveOrFail();

                $user->notify(new UserCreated($this->random_pass));
                send_sms($user->phone_no,"Your HR account on Quicksava Credit has been created. Use your email to log in. Your password ".$this->random_pass);
                Session::flash("success", "HR user has been created");
            }
        });


        return redirect()->back();
    }

    public function randomPassword()
    {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    public function partnerEmployerHrDT($id) {

        $userIds = User::where('user_group', 2)->pluck('id');

        $hrManagers = HrManager::where('employer_id',$id)->whereIn('user_id', $userIds)->get();
        return DataTables::of($hrManagers)

            ->editColumn('name', function ($hrManagers) {
                return optional($hrManagers->user)->name;
            })

            ->editColumn('surname', function ($hrManagers) {
                return optional($hrManagers->user)->surname;
            })

            ->editColumn('email', function ($hrManagers) {
                return optional($hrManagers->user)->email;
            })
            ->editColumn('id_no', function ($hrManagers) {
                return optional($hrManagers->user)->id_no;
            })
            ->editColumn('phone_no', function ($hrManagers) {
                return optional($hrManagers->user)->phone_no;
            })

            ->addColumn('actions', function($hrManagers){ // add custom column
                $actions = '<div class="align-content-center">';


                $actions .= '<form action="'. route('delete-hr',  $hrManagers->id) .'" style="display: inline;" method="POST" class="del-hr-form">';
                $actions .= method_field('DELETE');
                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])


            ->make(true);
    }

    public function delete_hr($id)
    {

        $hr = HrManager::find($id);
        $user = $hr->user;
        $name = optional($hr->user)->name;
        $employer = optional($hr->employer)->business_name;

        if ($hr->delete()){
            $user->delete();
            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Deleted HR user ('.$name.') for Employer '.$employer,
            ]);
        }



        Session::flash("success", "HR manager has been deleted");


        return redirect()->back();
    }

    public  function enable_advance(Request  $request){

        $data = request()->validate([
            'id'  => 'required',
        ]);

        $employer = Employer::find($request->id);

        if (is_null($employer))
            abort(404,"Employer/partner not found");


        DB::transaction(function() use($request,$employer) {

            $employer->salary_advance = true;
            $employer->update();

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Enabled salary advance for ==> '.$employer->business_name
            ]);

            Session::flash("success", "Salary advance has been enabled");

        });


        return redirect()->back();
    }

    public  function disable_advance(Request  $request){

        $data = request()->validate([
            'id'  => 'required',
        ]);

        $employer = Employer::find($request->id);

        if (is_null($employer))
            abort(404,"Employer/partner not found");


        DB::transaction(function() use($request,$employer) {

            $employer->salary_advance = false;
            $employer->update();

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Disabled salary advance for ==> '.$employer->business_name
            ]);

            Session::flash("success", "Salary advance has been disabled");

        });


        return redirect()->back();
    }

    public function randomID()
    {
        $alphabet = "ABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";


        do{
            $pass = array();
            $alphaLength = strlen($alphabet) - 1;

            for ($i = 0; $i < 5; $i++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet[$n];
            }
            $id = implode($pass);

        }while(!is_null(WalletTransaction::where('trx_id', $id)->first()));


        return $id;

    }

    public  function create_advance_period_matrix(Request  $request){
        $data = request()->validate([
            'employer_id'  => 'required',
            'employment_period_from' => 'required|integer',
            'employment_period_to' => 'required|integer',
            'max_loan_period' => 'required|integer',
        ]);

        $periodMatrix = new AdvanceLoanPeriodMatrix();
        $periodMatrix->employer_id = $request->employer_id;
        $periodMatrix->employment_period_from = $request->employment_period_from;
        $periodMatrix->employment_period_to = $request->employment_period_to;
        $periodMatrix->max_loan_period = $request->max_loan_period;
        $periodMatrix->save();

        //create audit trail
        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Added new Advance loan period matrix: '.$request->employment_period_from.' - '.
                $request->employment_period_to.' months. Max loan period '.$request->max_loan_period.
                ' for employer: '.Employer::find($request->employer_id)->business_name,
        ]);

        Session::flash("success", "Loan period has been configured successfully.");


        return redirect()->back();
    }

    public function delete_advance_period_matrix(Request  $request)
    {
        $data = request()->validate([
            'id'  => 'required|exists:inua_loan_period_matrices,id',
        ]);

        $matrix = AdvanceLoanPeriodMatrix::find($request->id);
        $name = optional($matrix->employer)->business_name;
        $from = $matrix->employment_period_from;
        $to = $matrix->employment_period_to;

        if ($matrix->delete()){
            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Deleted Advacne Loan Period Matrix ('.$from .' - '. $to.' Months) for Employer ('.$name.')',
            ]);
        }

        Session::flash("success", "Loan period matrix has been deleted");


        return redirect()->back();
    }

    public  function new_mtd_target(Request  $request){
        $data = request()->validate([
            'employer_id'  => 'required|exists:employers,id',
            'year' => 'required|integer',
            'month' => 'required',
            'target_loans_value' => 'required',
            'target_loans' => 'required',
        ]);


        $employer = Employer::find($request->employer_id);


        MtdTarget::updateOrCreate(
            [
                'employer_id' =>  request('employer_id'),
                'year' =>  request('year'),
                'month' =>  request('month')
            ],
            [
                'target_loans' => request('target_loans'),
                'target_loans_value' => request('target_loans_value')
            ]
        );

        //create audit trail
        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Added new MTD target for: '.$employer->business_name.'. Target: Ksh. '.
                $request->target_loans_value.'. Period: '.$request->year.
                ' - '.$request->month,
        ]);

        Session::flash("success", "MTD target has been set successfully.");


        return redirect()->back();
    }

    public function mtdTargetsDT($id) {
        $targets = MtdTarget::where('employer_id',$id)->get();

        return DataTables::of($targets)

            ->editColumn('target_loans_value', function ($targets) {
                return number_format($targets->target_loans_value);
            })

            ->editColumn('month', function ($targets) {
                return Carbon::create()->day(1)->month($targets->month)->monthName;
            })

            ->addColumn('actions', function($targets){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<button source="' . route('mtd-details' ,  $targets->id) . '"
                    class="btn btn-primary btn-link btn-sm edit-mtd-btn" acs-id="'.$targets->id .'">
                    <i class="material-icons">edit</i> Edit</button>';


                $actions .= '<form action="'. route('delete-mtd',  $targets->id) .'" style="display: inline;" method="POST" class="delete-mtd-form">';
                $actions .= method_field('DELETE');
                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])

            ->make(true);
    }

    public function mtd_details($id)
    {
        $rslt = MtdTarget::find($id);
        return $rslt;
    }

    public function update_mtd(Request $request)
    {
        $data = request()->validate([
            'mtd_id' => 'required|exists:mtd_targets,id',
            'year' => 'required|integer',
            'month' => 'required',
            'target_loans' => 'required',
            'target_loans_value' => 'required',
        ]);

        $exists = MtdTarget::where('employer_id',$request->employer_id)
            ->where('year',$request->year)
            ->where('month',$request->month)
            ->where('id','!=' ,$request->mtd_id)
            ->first();

        if (is_null($exists)){

            $mtd = MtdTarget::find($request->mtd_id);
            $mtd->year = $request->year;
            $mtd->month = $request->month;
            $mtd->target_loans = $request->target_loans;
            $mtd->target_loans_value = $request->target_loans_value;
            $mtd->update();

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Edited MTD of id #'.$request->mtd_id.' to target Ksh. '.$request->target_loans_value,
            ]);

            request()->session()->flash('success', 'MTD has been updated.');

        }else{
            request()->session()->flash('warning', 'MTD with the same year and month exists.');
        }
        return redirect()->back();
    }

    public function delete_mtd($id)
    {

        $mtd = MtdTarget::find($id);
        $mtdTarget = $mtd->target_loans_value;
        $mtdEmployer =optional( $mtd->employer)->business_name;

        try {
            if ($mtd->delete()){
                AuditTrail::create([
                    'created_by' => auth()->user()->id,
                    'action' => 'Deleted MTD of target: '.$mtdTarget.' from employer: '.$mtdEmployer
                ]);
                Session::flash("success", "MTD target has been deleted");
            }
        }catch (\Exception $ex){
            Session::flash("warning", "Unable to delete MTD target because it's being used in the system");
        }

        return redirect()->back();
    }
}
