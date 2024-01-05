<?php

namespace App\Http\Controllers;

use App\AuditTrail;
use App\CustomerProfile;
use App\Leads;
use App\CustomerSuspension;
use App\Employee;
use App\EmployeeIncome;
use App\Employer;
use App\Exports\AllCustomers;
use App\Exports\CheckoffCustomers;
use App\Exports\CheckoffCustomersSummary;
use App\AdvanceApplication;
use App\AdvanceLoanPeriodMatrix;
use App\LoanProduct;
use App\LoanRequest;
use App\OTP;
use App\User;
use App\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;


class CustomerController extends Controller
{
    public function __construct() {
        $this->middleware(['auth']);
    }


    public function customers() {
        $customers = CustomerProfile::orderBy('id','desc')->paginate(20);

        return view('customers.customers')->with([
            'customers' =>$customers
        ]);
    }
    public function exportAllCustomers() {
        return Excel::download(new AllCustomers(), 'all_customers.xlsx');
    }

    public function checkoff_customers() {
        return view('customers.checkoff_customers');
    }

    public function new_leads(){
        return view('customers.new_leads');
    }
    public function exportCheckoffCustomers() {
        return Excel::download(new CheckoffCustomers(), 'check_off_customers.xlsx');
    }

    public function exportNewLeads() {
        return Excel::download(new Leads(), 'NewLeads.xlsx');
    }
    public function checkoffCustomersDT() {
        $customers = CustomerProfile::where('is_checkoff', true)->get();

        return DataTables::of($customers)
            ->addColumn('name', function ($customers) {
                return optional($customers->user)->name;
            })

            ->addColumn('surname', function ($customers) {
                return optional($customers->user)->surname;
            })

            ->addColumn('id_no', function ($customers) {
                return optional($customers->user)->id_no;
            })
            ->addColumn('phone_no', function ($customers) {
                return optional($customers->user)->phone_no;
            })
            ->editColumn('status',function ($customers) {
                if ($customers->status == 'active'){
                    return '<span class="badge pill badge-success">'.$customers->status.'</span>';
                }elseif ($customers->status == 'suspended'){
                    return '<span class="badge pill badge-warning">'.$customers->status.'</span>';
                }elseif ($customers->status == 'blocked'){
                    return '<span class="badge pill badge-danger">'.$customers->status.'</span>';
                }else{
                    return '<span class="badge pill badge-info">'.$customers->status.'</span>';
                }
            })
            ->addColumn('loans', function ($customers) {
                return optional($customers->user)->loans->count();
            })
            ->editColumn('is_checkoff',function ($customers) {
                return $customers->is_checkoff ? 'YES' : 'NO';
            })

//            ->editColumn('max_limit',function ($customers) {
//                return optional(optional($customers->user)->wallet)->currency.' '.number_format($customers->max_limit,2);
//            })
            ->addColumn('actions', function($customers){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . route('customer-details' ,  $customers->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">perm_identity</i> Profile</a>';

                if ($customers->is_checkoff ){
                    $emp = Employee::where('user_id',$customers->user_id)->first();
                    $actions .= '<a href="' . route('employee-details' , is_null($emp) ? 0 : $emp->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">badge</i> Employee</a>';
                }

                if (auth()->user()->role->has_perm([22]) ){
                    $actions .= '<a href="' . url('wallet/customer/'.optional($customers->user)->wallet_id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">account_balance_wallet</i> Wallet</a>';
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
            ->rawColumns(['actions','status'])
            ->make(true);

    }

    public function LeadsDT() {
        $new_leads = Leads::orderBy('id','desc')->get();
        Log::info("Leads data table hit");
        Log::info("The username is " . json_encode($new_leads));
        
        return DataTables::of($new_leads)
            ->addColumn('name', function ($new_leads) {
                return $new_leads->name;
            })

            ->addColumn('email', function ($new_leads) {
                return $new_leads->email;
            })

            ->addColumn('id_no', function ($new_leads) {
                return $new_leads->id_no;
            })
            ->addColumn('phone_no', function ($new_leads) {
                return $new_leads->msisdn;
            })
            ->rawColumns(['actions','status'])

            ->make(true);


    }


    public function checkoff_customers_summary() {
        return view('customers.checkoff_customers_summary');
    }
    public function exportCheckoffCustomersSummary() {
        return Excel::download(new CheckoffCustomersSummary(), 'check_off_customers_summary.xlsx');
    }
    public function checkoffCustomersSummaryDT() {
        $employers = Employer::get();

        return DataTables::of($employers)
            ->editColumn('business_logo', function ($employers) {
                return '<img src="'.$employers->business_logo_url.'" width="75" height="75" />';
            })


            ->addColumn('total_employees', function ($employers) {
                return $employers->employees->count();
            })


            ->addColumn('actions', function($employers){ // add custom column
                $actions = '<div class="align-content-center">';


                $actions .= '<a href="' . route('employer-details' ,  $employers->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View</a>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','business_logo'])
            ->make(true);

    }






    public function managed_customers() {
        return view('customers.managed_customers')->with(['filter'=>'all']);
    }

    public function managed_customers_idno($idNo) {
        return view('customers.managed_customers')->with(['filter'=>$idNo]);
    }
    public function managedCustomersDT($filter) {
        if ($filter == 'all'){
            $customers = CustomerProfile::where('managed', true)->get();
        }else{
            info("ID NO:".$filter);
            $users = User::where('id_no', $filter)->pluck('id');
            info($users);
            $customers = CustomerProfile::whereIn('user_id', $users)->get();
        }

        info($customers);
        return DataTables::of($customers)
            ->addColumn('name', function ($customers) {
                return optional($customers->user)->name;
            })

            ->addColumn('surname', function ($customers) {
                return optional($customers->user)->surname;
            })

            ->addColumn('id_no', function ($customers) {
                return optional($customers->user)->id_no;
            })
            ->addColumn('phone_no', function ($customers) {
                return optional($customers->user)->phone_no;
            })
            ->editColumn('status',function ($customers) {
                if ($customers->status == 'active'){
                    return '<span class="badge pill badge-success">'.$customers->status.'</span>';
                }elseif ($customers->status == 'suspended'){
                    return '<span class="badge pill badge-warning">'.$customers->status.'</span>';
                }elseif ($customers->status == 'blocked'){
                    return '<span class="badge pill badge-danger">'.$customers->status.'</span>';
                }else{
                    return '<span class="badge pill badge-info">'.$customers->status.'</span>';
                }
            })
            ->addColumn('loans', function ($customers) {
                return optional($customers->user)->loans->count();
            })
            ->editColumn('is_checkoff',function ($customers) {
                return $customers->is_checkoff ? 'YES' : 'NO';
            })

            ->addColumn('actions', function($customers){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . route('customer-details' ,  $customers->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">perm_identity</i> Profile</a>';

                if ($customers->is_checkoff ){
                    $emp = Employee::where('user_id',$customers->user_id)->first();
                    $actions .= '<a href="' . route('employee-details' , is_null($emp) ? 0 : $emp->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">badge</i> Employee</a>';
                }

                if (auth()->user()->role->has_perm([22]) ){
                    $actions .= '<a href="' . url('wallet/customer/'.optional($customers->user)->wallet_id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">account_balance_wallet</i> Wallet</a>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','status'])
            ->make(true);

    }
    public  function new_managed_customer(Request  $request){

        if ($request->query('id_no')){

            $idNo = $request->query('id_no');

            info($idNo);
            $user = User::where('id_no', $idNo)->first();

            //info($user);

            if (is_null($user)){
                return view('customers.create_managed_customer')->with(['id_no'=>$idNo]);

            }else{
                Session::flash("warning", "The ID number is already registered. Please see the customer below to view their profile and apply a loan for them");

                info('redirect here');
                info($idNo);
                return redirect('customers/managed/filter/'.$idNo);
            }
        }else{
            Session::flash("warning", "Please provide an ID number");
            return redirect()->back();
        }

    }
    public function managed_customer_otp($phone_no) {
        $code = $this->generateOtp();

        $actualPhoneNo = "254".substr($phone_no, -9);


        OTP::where('phone_no', '=', $actualPhoneNo)
            ->update(array('verified' => 'yes','verification_date'=>Carbon::now()));


        $otp = new OTP();
        $otp->phone_no = $actualPhoneNo;
        $otp->verification_code = $code;
        $otp->verified = "no";
        $otp->saveOrFail();

        $message = "Use this OTP to verify your phone number and account creation: ".$code;
        send_sms($actualPhoneNo, $message);

        return response()->json([
            'success' => true,
            'message' => 'OTP has been sent. Please enter below to verify'
        ], 200);
    }
    public function create_managed_customer(Request  $request) {
        $this->validate($request, [
            'firstname' => 'required|string',
            'surname' => 'required|string',
            'id_no' => 'required|string|unique:users',
            'phone_no' => 'required|string',
            'gender' => 'required',
            'dob' => 'required',
//            'email' => 'nullable|string|email|unique:users',
            'employer'  => 'required|exists:employers,id',
            'position'  => 'required',
            'nature_of_work'  => 'required',
            'payroll_no'  => 'required',
            'employment_date'  => 'required',
            'selfie'  => 'required|file',
            'id_front'  => 'required|file',
            'id_back'  => 'required|file',
            'latest_payslip'  => 'required',
            'location'  => 'required',
            'basic_salary'  => 'required',
            'gross_salary'  => 'required',
            'net_salary'  => 'required',
            'otp'  => 'required',
        ]);

        $actualPhoneNo = "254".substr($request->phone_no, -9);

        //validate phone no
        $exists = User::where('phone_no', $actualPhoneNo)->first();
        if (!is_null($exists)){
            Session::flash("warning", "The phone number is already in use by another user");
            return back()->withInput();
        }

        //check otp
        $otp = OTP::where('phone_no',$actualPhoneNo)->where('verification_code',$request->otp)->orderBy('id','desc')->first();

        if (is_null($otp)){
            Session::flash("warning", "Invalid OTP. User not created.");
            return back()->withInput();
        }else{
            if ($otp->verified == 'yes'){
                Session::flash("warning", "OTP has already been used. Please navigate to the 'FINISH' tab to generate another one");
                return redirect()->back();
            }else{
                $otp->verified = 'yes';
                $otp->verification_date = Carbon::now();
                $otp->update();
            }
        }


        //check if exists in employer's income (has been uploaded)
        $income = EmployeeIncome::where('employer_id', $request->employer)
            ->where('payroll_no',$request->payroll_no)
            ->where('id_no', $request->id_no)
            ->first();

        if (is_null($income)){
            Session::flash("warning", "Employee income could not be found under this employer with the details provided. Please upload the employee income first before creating their profile");
            return back()->withInput();
        }

        //do creation
        DB::transaction(function() use ($income, $actualPhoneNo, $request) {

            //create user - first legg

            $wallet = new Wallet();
            $wallet->current_balance = 0;
            $wallet->previous_balance = 0;
            $wallet->active = 1;
            $wallet->saveOrFail();

            $dummyPass = $this->generateOtp();

            if ($request->filled('email') )
                $email = $request->email;
            else
                $email = 'quicksava'.Carbon::now()->timestamp.'@gmail.com';

            $user = new User([
                'wallet_id' => $wallet->id,
                'user_group' => 4, //customer
                'surname' => $request->surname,
                'name' => $request->firstname,
                'phone_no' => $actualPhoneNo,
                'id_no' => $request->id_no,
                'email' => $email,
                'password' => bcrypt($dummyPass),
            ]);
            $user->save();

            $customerProfile = new CustomerProfile();
            $customerProfile->user_id = $user->id;
            $customerProfile->gender = $request->gender;
            $customerProfile->dob = $request->dob;
            $customerProfile->is_checkoff = true;
            $customerProfile->managed = true;
            $customerProfile->saveOrFail();

            send_sms($request->phone_no,  "Welcome to Quicksava!. Your account has been been successfully created");



            //create employee - second legg

            $selfie_imageFilePath = $request->file('selfie')->storePublicly('selfies', 's3');
            $id_imageFilePath = $request->file('id_front')->storePublicly('id_photos', 's3');
            $id_back_imageFilePath = $request->file('id_back')->storePublicly('id_photos', 's3');
            $payslipFilePath = $request->file('latest_payslip')->storePublicly('payslips', 's3');


            $employee = new Employee();
            $employee->user_id = $user->id;
            $employee->employer_id = $request->employer;
            $employee->payroll_no = $request->payroll_no;
            $employee->location = $request->location;

            $employee->id_url = Storage::disk('s3')->url($id_imageFilePath);
            $employee->id_filename = basename($id_imageFilePath);

            $employee->id_back_url = Storage::disk('s3')->url($id_back_imageFilePath);
            $employee->id_back_name = basename($id_back_imageFilePath);

            $employee->passport_photo_url = Storage::disk('s3')->url($selfie_imageFilePath);
            $employee->passport_photo_filename = basename($selfie_imageFilePath);

            $employee->latest_payslip_url = Storage::disk('s3')->url($payslipFilePath);
            $employee->latest_payslip_filename = basename($payslipFilePath);

            $employee->limit_expiry = Carbon::now()->addMonths(6);
            $employee->nature_of_work = $request->nature_of_work;
            $employee->position = $request->position;

            $limit = $income->net_salary - $income->basic_salary/3;

            $employee->employment_date = $income->employment_date;
            $employee->gross_salary = $income->gross_salary;
            $employee->basic_salary = $income->basic_salary;
            $employee->net_salary = $income->net_salary;
            $employee->max_limit = $limit >= 0 ? $limit : 0;
            $employee->saveOrFail();

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Created new managed customer:  '.$user->surname.' '.$user->name.'. For: '.optional($employee->employer)->business_name,
            ]);


        });


        Session::flash("success","Employee has been created and activated successfully. Please proceed to make a loan request for them.");
        return redirect('customers/managed');

    }
    public  function apply_managed_inua(Request  $request){

        $this->validate($request, [
            'employer_id' =>'required|exists:employers,id',
            'employee_id' =>'required|exists:employees,id',
            'loan_product_id' =>'required|exists:loan_products,id',
            'amount_requested' => 'required|numeric',
            'period_in_months' => 'required|numeric',
            'purpose' => 'required',
        ]);

        $employee = Employee::find($request->employee_id);
        $customerProfile = CustomerProfile::where('user_id',$employee->user_id)->first();


        if (is_null($customerProfile)){
            Session::flash("warning", "Customer profile not found. Please contact system admin for assistance.");
            return redirect()->back();
        }


        if ($customerProfile->status != 'active'){
            Session::flash("warning", "Unable to apply for an Salary Advance loan. Customer profile is ".$customerProfile->status);
            return redirect()->back();
        }


        $notPaidApprovedLoan = LoanRequest::where('user_id',$employee->user_id)
            ->where('loan_product_id',$request->loan_product_id)
            ->where('approval_status','APPROVED')
            ->whereIn('repayment_status',['PENDING','PARTIALLY_PAID'])
            ->first();

        if (!is_null($notPaidApprovedLoan)){
            Session::flash("warning", "Customer has an ACTIVE Salary Advance loan which has not been fully paid. Please ask them to pay it from the loans section or request the HR to remit the payment before applying again ");
            return redirect()->back();
        }

        $notPaidPendingLoan = LoanRequest::where('user_id',$employee->user_id)
            ->where('loan_product_id',$request->loan_product_id)
            ->where('approval_status','PENDING')
            ->first();

        if (!is_null($notPaidPendingLoan)){
            Session::flash("warning", "Customer has an an Salary Advance loan which is PENDING approval. Please wait until the request is approved or rejected before applying again");
            return redirect()->back();
        }


        //check if has pending/processing application for Salary Advance
        $ongoingAdvanceApplication = AdvanceApplication::whereIn('quicksava_status',['PENDING','PROCESSING','AMENDMENT'])
            ->where('user_id',$employee->user_id)
            ->first();

        if (!is_null($ongoingAdvanceApplication)){
            Session::flash("warning", "Customer has an ongoing Salary Advance application. Please wait until this application is processed to end. Current status: ".$ongoingAdvanceApplication->Quicksava_status);
            return redirect()->back();
        }

        $loanProduct = LoanProduct::find($request->loan_product_id);

        //check loan period as per loan product max period
        if ($request->period_in_months > $loanProduct->max_period_months){
            Session::flash("warning", "Invalid loan period. Loan period in months can not be greater than ".$loanProduct->max_period_months." months");
            return redirect()->back();

        }

        //check amount request as per loan product
        if ($request->amount_requested > $loanProduct->max_amount || $request->amount_requested < $loanProduct->min_amount){
            Session::flash("warning", "Invalid loan amount. Amount requested can not be less than KES  ".number_format($loanProduct->min_amount)." or more than KES ".number_format($loanProduct->max_amount));
            return redirect()->back();
        }


        //check age in employment
        $this_month = Carbon::now()->floorMonth(); // returns todays month
        $start_month = Carbon::parse($employee->employment_date)->floorMonth(); // returns first day of month of employment
        $diff = $start_month->diffInMonths($this_month);  // returns diff in months

        //if employed < 6 months, only give 1 month advance
        if ($diff < 6 && $request->period_in_months > 1){
            Session::flash("warning", "Only one month advance allowed. We can only advance this client a loan for 1 month only at this time");
            return redirect()->back();
        }


        //check other employer specific time bounds
        //$employer = Employer::find($request->employer_id);

        $matrix = AdvanceLoanPeriodMatrix::where('employer_id',$request->employer_id)
            ->where('employment_period_from','<=',$diff)
            ->where('employment_period_to','>=',$diff)
            ->first();

        if (!is_null($matrix)){
            if ($request->period_in_months > $matrix->max_loan_period){
                Session::flash("warning", "Only ".$matrix->max_loan_period." month advance allowed. We can only advance a loan for a maximum of ".$matrix->max_loan_period." months only at this time");
                return redirect()->back();
            }
        }


        //check limit

        $requested = $request->amount_requested;

        $limit = ($employee->max_limit*100)/113.33;
        $allowed = $limit*$request->period_in_months;

        if ($requested > $allowed){
            Session::flash("warning", "Requested amount not allowed. For ".$request->period_in_months." month(s), we can only give a maximum of ".optional(optional($employee->user)->wallet)->currency." ".number_format($allowed));
            return redirect()->back();
        }


        $advanceApplication = new AdvanceApplication();
        $advanceApplication->user_id = $employee->user_id;
        $advanceApplication->employer_id = $request->employer_id;
        $advanceApplication->loan_product_id = $request->loan_product_id;
        $advanceApplication->amount_requested = $request->amount_requested;
        $advanceApplication->period_in_months = $request->period_in_months;
        $advanceApplication->purpose = $request->purpose;
        $advanceApplication->created_by = auth()->user()->id;
        $advanceApplication->saveOrFail();

        if (!is_null($customerProfile)){
            if ($customerProfile->is_checkoff == false){
                $customerProfile->is_checkoff = true;
                $customerProfile->update();
            }
        }

        if ($request->hasFile('payslip_file')){
            //update payslip on employee
            $payslipFilePath = $request->file('payslip_file')->storePublicly('payslips', 's3');

            $url = Storage::disk('s3')->url($payslipFilePath);

            $employee->latest_payslip_url = $url;
            $employee->latest_payslip_filename = basename($payslipFilePath);
            $employee->update();

            $advanceApplication->payslip_url = $url;
            $advanceApplication->update();
        }

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Applied new Salary Advance loan for :'.$employee->user->name.' of ID Number '.strtoupper($employee->user->id_no).' and Payroll number: '.strtoupper($employee->payroll_no).' to employer '.strtoupper($employee->employer->business_name),
        ]);

        send_sms(optional($employee->user)->phone_no, "Your loan request of Ksh. ".number_format($request->amount_requested)." has been received successfully. You will be notified once the status of the application changes");



        Session::flash("success", "Loan application has been made successfully");
        return redirect()->back();

    }

    public  function amend_managed_inua(Request  $request){

        $this->validate($request, [
            'id' =>'required|exists:advance_applications,id',
            'loan_product_id' =>'required|exists:loan_products,id',
            'amount_requested' => 'required|numeric',
            'period_in_months' => 'required|numeric',
            'purpose' => 'required',
        ]);

        $advanceApplication = AdvanceApplication::find($request->id);
        $user = $advanceApplication->user;

        $loanProduct = LoanProduct::find($request->loan_product_id);

        //check loan period as per loan product max period
        if ($request->period_in_months > $loanProduct->max_period_months){
            Session::flash("warning", "Invalid loan period. Loan period in months can not be greater than ".$loanProduct->max_period_months." months");
            return redirect()->back();

        }

        //check amount request as per loan product
        if ($request->amount_requested > $loanProduct->max_amount || $request->amount_requested < $loanProduct->min_amount){
            Session::flash("warning", "Invalid loan amount. Amount requested can not be less than KES  ".number_format($loanProduct->min_amount)." or more than KES ".number_format($loanProduct->max_amount));
            return redirect()->back();
        }


        $employee = Employee::where('user_id', $user->id)->where('employer_id', $advanceApplication->employer_id)->first();

        //check age in employment
        $this_month = Carbon::now()->floorMonth(); // returns todays month
        $start_month = Carbon::parse($employee->employment_date)->floorMonth(); // returns first day of month of employment
        $diff = $start_month->diffInMonths($this_month);  // returns diff in months

        //if employed < 6 months, only give 1 month advance
        if ($diff < 6 && $request->period_in_months > 1){
            Session::flash("warning", "Only one month advance allowed. We can only advance this client a loan for 1 month only at this time");
            return redirect()->back();
        }


        //check other employer specific time bounds
        //$employer = Employer::find($request->employer_id);

        $matrix = AdvanceLoanPeriodMatrix::where('employer_id',$request->employer_id)
            ->where('employment_period_from','<=',$diff)
            ->where('employment_period_to','>=',$diff)
            ->first();

        if (!is_null($matrix)){
            if ($request->period_in_months > $matrix->max_loan_period){
                Session::flash("warning", "Only ".$matrix->max_loan_period." month advance allowed. We can only advance a loan for a maximum of ".$matrix->max_loan_period." months only at this time");
                return redirect()->back();
            }
        }


        //check limit

        $requested = $request->amount_requested;

        $limit = ($employee->max_limit*100)/113.33;
        $allowed = $limit*$request->period_in_months;

        if ($requested > $allowed){
            Session::flash("warning", "Requested amount not allowed. For ".$request->period_in_months." month(s), we can only give a maximum of ".optional(optional($employee->user)->wallet)->currency." ".number_format($allowed));
            return redirect()->back();
        }


        $advanceApplication->loan_product_id = $request->loan_product_id;
        $advanceApplication->amount_requested = $request->amount_requested;
        $advanceApplication->period_in_months = $request->period_in_months;
        $advanceApplication->purpose = $request->purpose;
        $advanceApplication->Quicksava_status = 'PROCESSING';
        $advanceApplication->update();

        send_sms($user->phone_no, "Your update for your loan request has been received successfully. You will be notified once the status of the application changes");


        if ($request->hasFile('payslip_file')){
            //update payslip on employee
            $payslipFilePath = $request->file('payslip_file')->storePublicly('payslips', 's3');

            $url = Storage::disk('s3')->url($payslipFilePath);

            $employee->latest_payslip_url = $url;
            $employee->latest_payslip_filename = basename($payslipFilePath);
            $employee->update();

            $advanceApplication->payslip_url = $url;
            $advanceApplication->update();
        }

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Amended Salary Advance loan for :'.$employee->user->name.' of ID Number '.strtoupper($employee->user->id_no).' and Payroll number: '.strtoupper($employee->payroll_no).' to employer '.strtoupper($employee->employer->business_name),
        ]);

        Session::flash("success", "Salary Advance application has been updated successfully");
        return redirect()->back();
    }


    public function customer_details($id) {
        $customer = CustomerProfile::find($id);

        if (is_null($customer))
            abort(404,"Customer profile does not exist");

        return view('customers.customer_profile')->with(['customer'=>$customer]);
    }

    public function customerLoansDT($_user_id) {
        $loanRequests = LoanRequest::where('user_id', $_user_id)->get();

        return DataTables::of($loanRequests)
            ->addColumn('product', function ($loanRequests) {
                return optional($loanRequests->product)->name;
            })
            ->editColumn('amount_requested', function ($loanRequests) {
                return optional(optional($loanRequests->user)->wallet)->currency.' '. number_format($loanRequests->amount_requested);
            })
            ->editColumn('period_in_months', function ($loanRequests) {
                return $loanRequests->period_in_months.' Months';
            })

            ->editColumn('approval_status',function ($loanRequests) {
                if ($loanRequests->approval_status == 'PENDING'){
                    return '<span class="badge pill badge-info">'.$loanRequests->approval_status.'</span>';
                }elseif ($loanRequests->approval_status == 'APPROVED'){
                    return '<span class="badge pill badge-success">'.$loanRequests->approval_status.'</span>';
                }elseif ($loanRequests->approval_status == 'REJECTED'){
                    return '<span class="badge pill badge-danger">'.$loanRequests->approval_status.'</span>';
                }else{
                    return '<span class="badge pill badge-info">'.$loanRequests->approval_status.'</span>';
                }
            })

            ->editColumn('repayment_status',function ($loanRequests) {
                if ($loanRequests->repayment_status == 'PENDING'){
                    return '<span class="badge pill badge-info">'.$loanRequests->repayment_status.'</span>';
                }elseif ($loanRequests->repayment_status == 'PARTIALLY_PAID'){
                    return '<span class="badge pill badge-primary">'.$loanRequests->repayment_status.'</span>';
                }elseif ($loanRequests->repayment_status == 'PAID'){
                    return '<span class="badge pill badge-success">'.$loanRequests->repayment_status.'</span>';
                }elseif ($loanRequests->repayment_status == 'CANCELLED'){
                    return '<span class="badge pill badge-warning">'.$loanRequests->repayment_status.'</span>';
                }else{
                    return '<span class="badge pill badge-info">'.$loanRequests->repayment_status.'</span>';
                }

            })

            ->addColumn('actions', function($loanRequests){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . route('loan-details' ,  $loanRequests->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View</a>';


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
            ->rawColumns(['actions','repayment_status','approval_status'])
            ->make(true);

    }

    public function employee_details($id) {

        $employee = Employee::find($id);

        if (is_null($employee))
            abort(404, "Employee does not exist");

        return view('customers.employee_details')->with(['employee'=>$employee]);
    }

    public  function update_employee_limit(Request  $request){
        $data = request()->validate([
            'basic_salary'  => 'required',
            'gross_salary'  => 'required',
            'net_salary'  => 'required',
            'max_limit'  => 'required',
            'employee_id'  => 'required',
        ]);

        $employee = Employee::find($request->employee_id);

        if (is_null($employee))
            abort(404);


        DB::transaction(function() use ($request, $employee) {

            $employee->basic_salary=$request->basic_salary;
            $employee->gross_salary=$request->gross_salary;
            $employee->net_salary=$request->net_salary;
            $employee->max_limit=$request->max_limit;
            $employee->update();


            $customerProfile = CustomerProfile::where('user_id',$employee->user_id)->first();

            if (!is_null($customerProfile)){
                $customerProfile->is_checkoff = true;
                $customerProfile->update();
            }

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Updated employee limit to ('.$request->max_limit.') with reason ==> '.$request->comments,
            ]);

            request()->session()->flash('success', 'Employee limit has been updated');

            send_sms(optional($employee->user)->phone_no, "Your Salary Advance limit for ".optional($employee->employer)->business_name." has been updated to Ksh. ".number_format(($request->max_limit*100)/113.33));
        });
        return redirect()->back();
    }

    public  function update_employer(Request  $request){
        $data = request()->validate([
            'position'  => 'required',
            'nature_of_work'  => 'required',
            'payroll_no'  => 'required',
            'employer_id'  => 'required|exists:employers,id',
            'employee_id'  => 'required',
        ]);

        $employee = Employee::find($request->employee_id);

        if (is_null($employee))
            abort(404);


        DB::transaction(function() use ($request, $employee) {

            $employee->position=$request->position;
            $employee->nature_of_work=$request->nature_of_work;
            $employee->payroll_no=$request->payroll_no;
            $employee->employer_id=$request->employer_id;
            $employee->update();


            $customerProfile = CustomerProfile::where('user_id',$employee->user_id)->first();

            if (!is_null($customerProfile)){
                $customerProfile->is_checkoff = true;
                $customerProfile->update();
            }

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Updated employee employer details to ('.Employer::find($request->employer_id)->business_name.') with payroll number ==> '.$request->payroll_no,
            ]);

            request()->session()->flash('success', 'Employee employer/payroll has been updated');

            send_sms(optional($employee->user)->phone_no, "Your employer details have been updated to ".optional($employee->employer)->business_name." with payroll number. ".$request->payroll_no);
        });
        return redirect()->back();
    }

    public function update_customer_overdraft(Request $request) {

        $this->validate($request, [
            'customer_id' => 'required|exists:customer_profiles,id',
            'amount' => 'required|numeric',
        ]);

        $customer = CustomerProfile::find($request->id);

        if (is_null($customer))
            abort(404);

        $customer->max_limit = $request->amount;
        $customer->update();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Updated customer ('.$customer->user->name.') with ID '.$request->customer_id.' overdraft limit '.$request->amount,
        ]);

        Session::flash("success", "Overdraft limit has been updated");

        return redirect()->back();
    }


    public function suspend_customer(Request $request) {

        $this->validate($request, [
            'id' => 'required|exists:customer_profiles,id',
            'reason' => 'required',
        ]);

        $customer = CustomerProfile::find($request->id);

        if (is_null($customer))
            abort(404);

        $customer->status = 'suspended';
        $customer->update();

        $customerSuspension = new CustomerSuspension();
        $customerSuspension->customer_profile_id = $request->id;
        $customerSuspension->created_by = auth()->user()->id;
        $customerSuspension->action_type = "SUSPEND";
        $customerSuspension->reason = $request->reason;
        $customerSuspension->saveOrFail();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Suspended customer ('.$customer->user->name.') with ID '.$request->id,
        ]);

        Session::flash("success", "Customer has been suspended");

        return redirect()->back();
    }

    public function unsuspend_customer(Request $request) {

        $this->validate($request, [
            'id' => 'required|exists:customer_profiles,id',
        ]);

        $customer = CustomerProfile::find($request->id);

        if (is_null($customer))
            abort(404);

        $customer->status = 'active';
        $customer->update();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Unsuspended customer ('.$customer->user->name.') with ID '.$request->id,
        ]);

        Session::flash("success", "Customer has been unsuspended");

        return redirect()->back();
    }

    public function block_customer(Request $request) {

        $this->validate($request, [
            'id' => 'required|exists:customer_profiles,id',
            'reason' => 'required'
        ]);

        $customer = CustomerProfile::find($request->id);

        if (is_null($customer))
            abort(404);

        $customer->status = 'blocked';
        $customer->update();

        $customerSuspension = new CustomerSuspension();
        $customerSuspension->customer_profile_id = $request->id;
        $customerSuspension->created_by = auth()->user()->id;
        $customerSuspension->action_type = "BLOCK";
        $customerSuspension->reason = $request->reason;
        $customerSuspension->saveOrFail();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Blocked customer ('.$customer->user->name.') with ID '.$request->id,
        ]);

        Session::flash("success", "Customer has been blocked");

        return redirect()->back();
    }

    public function unblock_customer(Request $request) {

        $this->validate($request, [
            'id' => 'required|exists:customer_profiles,id',
        ]);

        $customer = CustomerProfile::find($request->id);

        if (is_null($customer))
            abort(404);

        $customer->status = 'active';
        $customer->update();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Unblocked customer ('.$customer->user->name.') with ID '.$request->id,
        ]);

        Session::flash("success", "Customer has been unblocked");

        return redirect()->back();
    }


    public  function search_customers(Request  $request){

        return view('customers.search_customers')
            ->with([
            'id_no'=>is_null($request->id_no) ? ' ' : $request->id_no,
            'phone_no'=>is_null($request->phone_no) ? ' ' : $request->phone_no
        ]);
    }


    public function searchCustomersDT($idNo,  $phoneNo) {

        $users = User::where('id_no', $idNo)->orWhere('phone_no', $phoneNo)->pluck('id');

        $customers = CustomerProfile::whereIn('user_id', $users)->get();


        return DataTables::of($customers)
            ->addColumn('name', function ($customers) {
                return optional($customers->user)->name;
            })

            ->addColumn('surname', function ($customers) {
                return optional($customers->user)->surname;
            })

            ->addColumn('id_no', function ($customers) {
                return optional($customers->user)->id_no;
            })
            ->addColumn('phone_no', function ($customers) {
                return optional($customers->user)->phone_no;
            })
            ->editColumn('status',function ($customers) {
                if ($customers->status == 'active'){
                    return '<span class="badge pill badge-success">'.$customers->status.'</span>';
                }elseif ($customers->status == 'suspended'){
                    return '<span class="badge pill badge-warning">'.$customers->status.'</span>';
                }elseif ($customers->status == 'blocked'){
                    return '<span class="badge pill badge-danger">'.$customers->status.'</span>';
                }else{
                    return '<span class="badge pill badge-info">'.$customers->status.'</span>';
                }
            })
            ->addColumn('loans', function ($customers) {
                return optional($customers->user)->loans->count();
            })
            ->editColumn('is_checkoff',function ($customers) {
                return $customers->is_checkoff ? 'YES' : 'NO';
            })

            ->addColumn('actions', function($customers){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . route('customer-details' ,  $customers->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">perm_identity</i> Profile</a>';

                if ($customers->is_checkoff ){
                    $emp = Employee::where('user_id',$customers->user_id)->first();
                    $actions .= '<a href="' . route('employee-details' , is_null($emp) ? 0 : $emp->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">badge</i> Employee</a>';
                }

                if (auth()->user()->role->has_perm([22]) ){
                    $actions .= '<a href="' . url('wallet/customer/'.optional($customers->user)->wallet_id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">account_balance_wallet</i> Wallet</a>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','status'])
            ->make(true);

    }






    public function generateOtp(){
        return rand(1000, 9999);
    }

}
