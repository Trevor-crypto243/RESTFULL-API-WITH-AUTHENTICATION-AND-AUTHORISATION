<?php

namespace App\Http\Controllers;

use App\AuditTrail;
use App\Employee;
use App\HrManager;
use App\InterestRateMatrix;
use App\AdvanceApplication;
use App\AdvanceApplicationComment;
use App\LoanProduct;
use App\LoanRequest;
use App\LoanRequestFee;
use App\LoanSchedule;
use App\MpesaCharge;
use App\Notifications\HrAdvanceApplication;
use App\Notifications\ManagedAdvanceAmedment;
use App\Repositories\Data;
use App\User;
use App\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yajra\DataTables\Facades\DataTables;

class SalaryAdvanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function new_requests() {
        return view('advance.requests')->with([
            'request'=>'New advance requests',
            'type'=>'NEW',
            'employer_id' => 0
        ]);
    }
    public function get_new_requests(Request $request) {

        $this->validate($request, [
            'employer_id' =>'required|exists:employers,id',
            'type' =>'required',
        ]);

        return view('advance.requests')->with([
            'request'=>'New advance requests',
            'type'=>$request->type,
            'employer_id'=>$request->employer_id,
        ]);
    }

    public function progress_requests() {
        return view('advance.requests')->with([
            'request'=>'In progress advance requests',
            'type'=>'PROGRESS',
            'employer_id' => 0
        ]);
    }
    public function get_progress_requests(Request $request) {

        $this->validate($request, [
            'employer_id' =>'required|exists:employers,id',
            'type' =>'required',
        ]);

        return view('advance.requests')->with([
            'request'=>'In progress advance requests',
            'type'=>$request->type,
            'employer_id'=>$request->employer_id,
        ]);
    }

    public function amending_requests() {
        return view('advance.requests')->with([
            'request'=>'In amendment advance requests',
            'type'=>'AMENDMENT',
            'employer_id' => 0
        ]);
    }
    public function get_amending_requests(Request $request) {

        $this->validate($request, [
            'employer_id' =>'required|exists:employers,id',
            'type' =>'required',
        ]);

        return view('advance.requests')->with([
            'request'=>'In progress advance requests',
            'type'=>$request->type,
            'employer_id'=>$request->employer_id,
        ]);
    }

    public function accepted_requests() {
        return view('advance.requests')->with([
            'request'=>'Accepted advance requests',
            'type'=>'ACCEPTED',
            'employer_id' => 0
        ]);
    }
    public function get_accepted_requests(Request $request) {

        $this->validate($request, [
            'employer_id' =>'required|exists:employers,id',
            'type' =>'required',
        ]);

        return view('advance.requests')->with([
            'request'=>'Accepted advance requests',
            'type'=>$request->type,
            'employer_id'=>$request->employer_id,
        ]);
    }

    public function rejected_requests() {
        return view('advance.requests')->with([
            'request'=>'Rejected advance requests',
            'type'=>'REJECTED',
            'employer_id' => 0
        ]);
    }
    public function get_rejected_requests(Request $request) {

        $this->validate($request, [
            'employer_id' =>'required|exists:employers,id',
            'type' =>'required',
        ]);

        return view('advance.requests')->with([
            'request'=>'Rejected advance requests',
            'type'=>$request->type,
            'employer_id'=>$request->employer_id,
        ]);
    }

    public function requestsDT(Request $request) {
        $employerID = $request->employee_id;
        $type = $request->type;



        if ($type == 'NEW')
            $advanceApplications = AdvanceApplication::where('employer_id', $employerID)
                ->where('quicksava_status','PENDING');
        elseif ($type == 'ACCEPTED')
            $advanceApplications = AdvanceApplication::where('employer_id', $employerID)
                ->where('quicksava_status','ACCEPTED');
        elseif ($type == 'PROGRESS')
            $advanceApplications = AdvanceApplication::where('employer_id', $employerID)
                ->where('quicksava_status','PROCESSING')
                ->whereIn('hr_status',['PENDING','ACCEPTED']);
         elseif ($type == 'AMENDMENT')
            $advanceApplications = AdvanceApplication::where('employer_id', $employerID)
                ->where('quicksava_status','AMENDMENT')
                ->orWhere('hr_status','AMENDMENT');
        else
            $advanceApplications =AdvanceApplication::where('employer_id', $employerID)
                ->where('quicksava_status','REJECTED')
                ->orWhere('hr_status','REJECTED');


        return DataTables::of($advanceApplications)

            ->addColumn('payroll_no', function ($advanceApplications) {

                $employee = Employee::where('user_id', $advanceApplications->user_id)
                    ->where('employer_id',$advanceApplications->employer_id)
                    ->first();
                if (is_null($employee))
                    return 'N/A';
                else
                    return $employee->payroll_no;
            })

            ->addColumn('name', function ($advanceApplications) {
                return optional($advanceApplications->user)->name;
            })
            ->addColumn('surname', function ($advanceApplications) {
                return optional($advanceApplications->user)->surname;
            })

            ->addColumn('employer', function ($advanceApplications) {
                return optional($advanceApplications->employer)->business_name;
            })

            ->addColumn('loan_product', function ($advanceApplications) {
                return optional($advanceApplications->product)->name;
            })

            ->addColumn('created_at', function ($advanceApplications) {
                return Carbon::parse($advanceApplications->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->addColumn('period_in_months', function ($advanceApplications) {
                return $advanceApplications->period_in_months . ' Months' ;
            })

            ->addColumn('amount_requested', function ($advanceApplications) {
                return 'KES '. number_format($advanceApplications->amount_requested);
            })

            ->addColumn('monthly_installment', function ($advanceApplications) {

                switch ($advanceApplications->period_in_months) {
                    case 1:
                        $apr = 96;
                        break;
                    case 2:
                        $apr = 86.107;
                        break;
                    case 3:
                        $apr = 79.711;
                        break;

                    case 4:
                        $apr = 75.886;
                        break;

                    case 5:
                        $apr = 73.342;
                        break;

                    case 6:
                        $apr = 71.527;
                        break;

                    case 7:
                        $apr = 70.169;
                        break;

                    case 8:
                        $apr = 69.116;
                        break;

                    case 9:
                        $apr = 68.275;
                        break;

                    case 10:
                        $apr = 67.585;
                        break;

                    case 11:
                        $apr = 67.013;
                        break;

                    case 12:
                        $apr = 66.531;
                        break;
                    default:
                        $apr = 0.0;
                }

                $interestRate = $apr/12;

                $interestRatePercentage = $interestRate/100;
                $a = 1+$interestRatePercentage;
                $exponent = -1*$advanceApplications->period_in_months;
                $raised = pow($a,$exponent);
                $raisedFormatted = sprintf("%f",$raised);
                $numerator = $advanceApplications->amount_requested * $interestRatePercentage;
                $denominator = 1-$raisedFormatted;
                $monthlyTotalAmount = ceil($numerator/$denominator);

                return 'KES '. number_format($monthlyTotalAmount);
            })

            ->addColumn('actions', function($advanceApplications){ // add custom column
                $actions = '<div class="align-content-center">';

//                $actions .= '<button source="' . route('valuation-matrix-details' ,  $employers->id) . '"
//                    class="btn btn-primary btn-link btn-sm edit-matrix-btn" acs-id="'.$employers->id .'">
//                    <i class="material-icons">edit</i> Edit</button>';

                $actions .= '<a href="' . route('advance-application-details' ,  $advanceApplications->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View</a>';


//                $actions .= '<form action="'. route('delete-matrix',  $employers->id) .'" style="display: inline;" method="POST" class="del-matrix-form">';
//                $actions .= method_field('DELETE');
//                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])

            ->make(true);

    }


    public function user_requests($userId) {
        $user = User::find($userId);

        if (is_null($user))
            abort(404, "Invalid user, please try again");

        return view('advance.user_requests')->with(['user'=>$user]);
    }

    public function userRequestsDT($userId) {


        $advanceApplications = AdvanceApplication::where('user_id', $userId)->get();


        return DataTables::of($advanceApplications)

            ->addColumn('payroll_no', function ($advanceApplications) {

                $employee = Employee::where('user_id', $advanceApplications->user_id)
                    ->where('employer_id',$advanceApplications->employer_id)
                    ->first();
                if (is_null($employee))
                    return 'N/A';
                else
                    return $employee->payroll_no;
            })

            ->addColumn('name', function ($advanceApplications) {
                return optional($advanceApplications->user)->name;
            })

            ->addColumn('employer', function ($advanceApplications) {
                return optional($advanceApplications->employer)->business_name;
            })

            ->addColumn('loan_product', function ($advanceApplications) {
                return optional($advanceApplications->product)->name;
            })

            ->editColumn('created_at', function ($advanceApplications) {
                return Carbon::parse($advanceApplications->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('period_in_months', function ($advanceApplications) {
                return $advanceApplications->period_in_months . ' Months' ;
            })

            ->editColumn('amount_requested', function ($advanceApplications) {
                return 'KES '. number_format($advanceApplications->amount_requested);
            })

            ->addColumn('actions', function($advanceApplications){ // add custom column
                $actions = '<div class="align-content-center">';

//                $actions .= '<button source="' . route('valuation-matrix-details' ,  $employers->id) . '"
//                    class="btn btn-primary btn-link btn-sm edit-matrix-btn" acs-id="'.$employers->id .'">
//                    <i class="material-icons">edit</i> Edit</button>';

                $actions .= '<a href="' . route('advance-application-details' ,  $advanceApplications->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View</a>';


//                $actions .= '<form action="'. route('delete-matrix',  $employers->id) .'" style="display: inline;" method="POST" class="del-matrix-form">';
//                $actions .= method_field('DELETE');
//                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])

            ->make(true);

    }

    public function request_details($id) {

        $advanceApplication = AdvanceApplication::find($id);

        if (is_null($advanceApplication))
            abort(404,"Application not found");

        $employee = Employee::where('user_id',$advanceApplication->user_id)
            ->where('employer_id',$advanceApplication->employer_id)
            ->first();

        if (is_null($employee))
            abort(404,"Employee profile not found");


        switch ($advanceApplication->period_in_months) {
            case 1:
                $apr = 96;
                break;
            case 2:
                $apr = 86.107;
                break;
            case 3:
                $apr = 79.711;
                break;

            case 4:
                $apr = 75.886;
                break;

            case 5:
                $apr = 73.342;
                break;

            case 6:
                $apr = 71.527;
                break;

            case 7:
                $apr = 70.169;
                break;

            case 8:
                $apr = 69.116;
                break;

            case 9:
                $apr = 68.275;
                break;

            case 10:
                $apr = 67.585;
                break;

            case 11:
                $apr = 67.013;
                break;

            case 12:
                $apr = 66.531;
                break;
            default:
                $apr = 0.0;
        }

        $interestRate = $apr/12;

        $interestRatePercentage = $interestRate/100;
        $a = 1+$interestRatePercentage;
        $exponent = -1*$advanceApplication->period_in_months;
        $raised = pow($a,$exponent);
        $raisedFormatted = sprintf("%f",$raised);
        $numerator = $advanceApplication->amount_requested * $interestRatePercentage;
        $denominator = 1-$raisedFormatted;
        $monthlyTotalAmount = $numerator/$denominator;

        $employerComments = AdvanceApplicationComment::where('advance_application_id',$id)
            ->where('comment_origin','EMPLOYER')
            ->get();

        $systemComments = AdvanceApplicationComment::where('advance_application_id',$id)
            ->where('comment_origin','SYSTEM')
            ->get();

        return view('advance.request_details')->with([
            'advanceApplication'=>$advanceApplication,
            'employee'=>$employee,
            'employerComments'=>$employerComments,
            'systemComments'=>$systemComments,
            'monthlyAmount'=>ceil($monthlyTotalAmount)
        ]);
    }

    public function request_amendment(Request $request) {
        $this->validate($request, [
            'request_id' =>'required|exists:advance_applications,id',
            'amendment_details' => 'required',
        ]);

        $advanceApplication = AdvanceApplication::find($request->request_id);
        $advanceApplication->quicksava_status = 'AMENDMENT';
        $advanceApplication->quicksava_comments = $request->amendment_details;
        $advanceApplication->update();

        $comment = new AdvanceApplicationComment();
        $comment->created_by = auth()->user()->id;
        $comment->advance_application_id = $advanceApplication->id;
        $comment->comment_origin = 'SYSTEM';
        $comment->comment = $request->amendment_details;
        $comment->saveOrFail();

        if ($advanceApplication->created_by != null){
            $applicant_name = $advanceApplication->user->name.' '.$advanceApplication->user->surname;
            optional(User::find($advanceApplication->created_by))->notify(new ManagedadvanceAmedment($request->amendment_details,$advanceApplication->id,$applicant_name));
        }

        send_sms(optional($advanceApplication->user)->phone_no, "Your advance application of Ksh. ".number_format($advanceApplication->amount_requested)." needs some amendment. Please open the app for more details and for your action");

        request()->session()->flash('success', 'Amendment request has been sent successfully');

        return redirect()->back();
    }

    public function send_to_hr(Request $request) {
        $this->validate($request, [
            'request_id' =>'required|exists:advance_applications,id',
        ]);

        $advanceApplication = AdvanceApplication::find($request->request_id);
        $advanceApplication->quicksava_status = 'PROCESSING';
        $advanceApplication->hr_status = 'PENDING';
        $advanceApplication->update();

        $hrs = HrManager::where('employer_id', $advanceApplication->employer_id)->get();
        foreach ($hrs as $hr){
            optional($hr->user)->notify(new HrAdvanceApplication(optional($advanceApplication->user)->name));
        }

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => auth()->user()->name.' - Sent advance loan to HR for approval. Loan amount:Ksh. '.number_format($advanceApplication->amount_requested).'. advance application ID #'.$advanceApplication->id,
        ]);

        request()->session()->flash('success', 'Request has been forwarded to HR for approval');

        return redirect()->back();
    }

    public function reject_request(Request $request) {
        $this->validate($request, [
            'request_id' =>'required|exists:advance_applications,id',
            'reject_reason' =>'required',
        ]);

        $advanceApplication = AdvanceApplication::find($request->request_id);
        $advanceApplication->quicksava_status = 'REJECTED';
        $advanceApplication->hr_status = 'REJECTED';
        $advanceApplication->quicksava_comments = $request->reject_reason;
        $advanceApplication->update();

        $comment = new AdvanceApplicationComment();
        $comment->created_by = auth()->user()->id;
        $comment->advance_application_id = $advanceApplication->id;
        $comment->comment_origin = 'SYSTEM';
        $comment->comment = $request->reject_reason;
        $comment->saveOrFail();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Rejected advance application from '.optional($advanceApplication->user)->name. ' for Ksh. '.number_format($advanceApplication->amount_requested).'. Entry ID #'.$advanceApplication->id,
        ]);


        send_sms(optional($advanceApplication->user)->phone_no, "Your advance application of Ksh. ".number_format($advanceApplication->amount_requested)." was rejected. Log in to the app to see more details");

        request()->session()->flash('success', 'Request has been rejected');

        return redirect()->back();
    }

    public function approve_request(Request $request) {
        $this->validate($request, [
            'request_id' =>'required|exists:advance_applications,id',
        ]);

        $advanceApplication = AdvanceApplication::find($request->request_id);

        if ($advanceApplication->hr_status == "PENDING"){
            request()->session()->flash('warning', 'This request is still PENDING HR approval. Please contact HR to approve it first');
            return redirect()->back();
        }

        if ($advanceApplication->hr_status == "REJECTED"){
            request()->session()->flash('warning', 'This request has been REJECTED by HR. Loan can not be approved');
            return redirect()->back();
        }

        if ($advanceApplication->hr_status == "AMENDMENT"){
            request()->session()->flash('warning', 'This request has an AMENDMENT from HR. Please send an amendment request to the customer');
            return redirect()->back();
        }

        if ($advanceApplication->quicksava_status != "PROCESSING"){
            request()->session()->flash('warning', 'Unable to approve request. Current status is '.$advanceApplication->quicksava_status);
            return redirect()->back();
        }


        $loanProduct = $advanceApplication->product;
        $user = $advanceApplication->user;
        $periodInMonths = $advanceApplication->period_in_months;


        //DB::transaction(function() use($loanProduct,$advanceApplication, $user, $periodInMonths) {


            $advanceApplication->quicksava_status = 'ACCEPTED';
            $advanceApplication->update();

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Approved advance application from '.optional($advanceApplication->user)->name. ' for Ksh. '.number_format($advanceApplication->amount_requested).'. Entry ID #'.$advanceApplication->id,
            ]);



            switch ($advanceApplication->period_in_months) {
                case 1:
                    $apr = 96;
                    break;
                case 2:
                    $apr = 86.107;
                    break;
                case 3:
                    $apr = 79.711;
                    break;

                case 4:
                    $apr = 75.886;
                    break;

                case 5:
                    $apr = 73.342;
                    break;

                case 6:
                    $apr = 71.527;
                    break;

                case 7:
                    $apr = 70.169;
                    break;

                case 8:
                    $apr = 69.116;
                    break;

                case 9:
                    $apr = 68.275;
                    break;

                case 10:
                    $apr = 67.585;
                    break;

                case 11:
                    $apr = 67.013;
                    break;

                case 12:
                    $apr = 66.531;
                    break;
                default:
                    $apr = 0.0;
            }

            $interestRate = $apr/12;


            $totalFees = 0;
            $extraMonthlyFees = 0;

            foreach ($loanProduct->fees as $fee) {

                $amt = $fee->amount_type == 'PERCENTAGE' ? $fee->amount/100 * $advanceApplication->amount_requested : $fee->amount;
                $type = $fee->amount_type == 'PERCENTAGE' ? $fee->name. " (".$fee->amount."% ".$fee->frequency.")" :  $fee->name." (".$fee->frequency.")";

                if ($fee->amount_type == 'PERCENTAGE' && $fee->frequency == 'MONTHLY'){
                    $amt = $amt*$advanceApplication->period_in_months;
                }


                if ($fee->frequency == 'MONTHLY'){

                    if ($type == 'PERCENTAGE'){
                        $extraMonthlyFees += $amt;
                    }else{
                        $extraMonthlyFees += $amt*$advanceApplication->period_in_months;
                    }
                }
                $totalFees += $fee->frequency == "ONE-OFF" ? $amt : 0;
            }


            $amount_disbursable = $advanceApplication->amount_requested;



            $loan = new LoanRequest();
            $loan->user_id = $user->id;
            $loan->loan_product_id = $advanceApplication->loan_product_id;
            $loan->amount_requested = $advanceApplication->amount_requested;
            $loan->amount_disbursable = $amount_disbursable;
            $loan->interest_rate = $interestRate;
            $loan->fees = 0;
            $loan->approval_status="APPROVED";
            $loan->repayment_status="PENDING";
            $loan->period_in_months = $periodInMonths;
            $loan->approved_date = Carbon::now();
            $loan->saveOrFail();

            $totalFees = 0;

            foreach ($loanProduct->fees as $fee) {

                $amt = $fee->amount_type == 'PERCENTAGE' ? $fee->amount/100 * $loan->amount_requested : $fee->amount;
                $type = $fee->amount_type == 'PERCENTAGE' ? $fee->name. " (".$fee->amount."%)" :  $fee->name;

                if ($fee->amount_type == 'PERCENTAGE' && $fee->frequency == 'MONTHLY'){
                    $amt = $amt*$periodInMonths;
                }

                $loanRequestFee = new LoanRequestFee();
                $loanRequestFee->loan_request_id = $loan->id;
                $loanRequestFee->fee = $type;
                $loanRequestFee->amount = $amt;
                $loanRequestFee->frequency = $fee->frequency;
                $loanRequestFee->saveOrFail();

                $totalFees += $fee->frequency == "ONE-OFF" ? $amt : 0;
            }

            if ($totalFees > 0){
                $loan->fees = $totalFees;
                $loan->update();
            }

            //move to wallet

            $wallet = $advanceApplication->user->wallet;

            $prevBal = $wallet->current_balance;
            $newBal = $prevBal+$amount_disbursable;

            $wallet->current_balance = $newBal;
            $wallet->previous_balance = $prevBal;
            $wallet->active = true;
            $wallet->update();

            $receipt = $this->randomID();

            //save to wallet transactions
            $walletTransaction = new WalletTransaction();
            $walletTransaction->wallet_id = $wallet->id;
            $walletTransaction->amount = $amount_disbursable;
            $walletTransaction->previous_balance = $prevBal;
            $walletTransaction->transaction_type = 'CR';
            $walletTransaction->source = 'Loan approval';
            $walletTransaction->trx_id = $receipt;
            $walletTransaction->narration ="salary advance loan offered";
            $walletTransaction->saveOrFail();


            //direct withdrawal
            $charge = MpesaCharge::where('min', '<=',$amount_disbursable)->where('max', '>=',$amount_disbursable)->first();
            if (!is_null($charge)){
                $withdrawalAmount  = $amount_disbursable - $charge->charge;
                $timestamp = Carbon::now()->getTimestamp();

                $payload = array(
                    "wallet_id"=>$wallet->id,
                    "recipient"=>$user->phone_no,
                    "amount"=>floor($withdrawalAmount),
                    "randomID"=>$timestamp."M-PESA withdrawal",
                );

                $connection = new AMQPStreamConnection('localhost', 5672,
                    config('app.AMQP_USER'), config('app.AMQP_PASSWORD'));
                $channel = $connection->channel();
                $channel->queue_declare('QUICKSAVA_B2C_QUEUE', false, true, false, false);
                $msg = new AMQPMessage(json_encode($payload), array('delivery_mode' => 2)
                );
                $channel->basic_publish($msg, '', 'QUICKSAVA_B2C_QUEUE');
                $channel->close();
                $connection->close();
            }

            //interest accruing monthly
            $interestAccrued = (($advanceApplication->amount_requested * $interestRate)/100) * $periodInMonths;
            Log::info("interest....". $interestAccrued);

            //extra monthly fees
            $extraMonthlyFees = 0;
            foreach (LoanRequestFee::where('loan_request_id', $loan->id)->where('frequency','MONTHLY')->get() as $requestFee){
                $extraMonthlyFees += $requestFee->amount;
            }

            $todaysDate = Carbon::now()->isoFormat('D');
            Log::info("todays date....".$todaysDate);

            $closingDate = $loanProduct->closing_date;

            if ($todaysDate >= $closingDate){
                //first installment is end of next month
                $firstInstallmentdate = Carbon::now()->addMonthNoOverflow()->day($closingDate)->toDateString();
            }else{
                //first installment is end of this month
                $firstInstallmentdate = Carbon::now()->day($closingDate)->toDateString();
            }
            Log::info("firstInstallmentdate....".$firstInstallmentdate);


            Log::info("InterestRate==>".$interestRate);
            $interestRatePercentage = $interestRate/100;
            Log::info("InterestRatePercentage==>".$interestRatePercentage);
            $a = 1+$interestRatePercentage;
            Log::info("a==>".$a);
            $exponent = -1*$advanceApplication->period_in_months;
            Log::info("exponent==>".$exponent);
            $raised = pow($a,$exponent);
            Log::info("Raised==>".$raised);
            $raisedFormatted = sprintf("%f",$raised);
            Log::info("raisedFormatted==>".$raisedFormatted);
            $numerator = $advanceApplication->amount_requested * $interestRatePercentage;
            Log::info("Numerator(amount*monthlyInterestRate)==>".$numerator);
            $denominator = 1-$raisedFormatted;
            Log::info("Denominator(1-raised)==>".$denominator);
            $monthlyTotalAmount = ceil($numerator/$denominator);
            Log::info("Monthly Payment::".$monthlyTotalAmount);
            $beginningBalance = $monthlyTotalAmount*$advanceApplication->period_in_months;
            Log::info("Total Due::".$beginningBalance);


            $principalAmount = $advanceApplication->amount_requested;


            $month = 1;
            while($month <= $advanceApplication->period_in_months) {

                Log::info("while month....".$month);

                $interestPaid = ceil($principalAmount * $interestRatePercentage);
                $principalPaid = ceil($monthlyTotalAmount - $interestPaid);

                $loanSchedule = new LoanSchedule();
                $loanSchedule->loan_request_id = $loan->id;
                $loanSchedule->payment_date = $firstInstallmentdate;
                $loanSchedule->beginning_balance = ceil($beginningBalance);
                $loanSchedule->scheduled_payment = ceil($monthlyTotalAmount);
                $loanSchedule->interest_paid = $interestPaid;
                $loanSchedule->principal_paid = $principalPaid;
                $loanSchedule->ending_balance = ceil($beginningBalance - $monthlyTotalAmount);
                $loanSchedule->saveOrFail();

                $firstInstallmentdate = Carbon::parse($firstInstallmentdate)->addMonthNoOverflow();
                $month++;
                $beginningBalance -= $monthlyTotalAmount;
                $principalAmount -= $principalPaid;
            }


            send_sms($user->phone_no, "Your salary advance loan request of Ksh. ".number_format($advanceApplication->amount_requested)." has been approved. KES ".number_format($amount_disbursable)." has been deposited to your wallet. Open the Quicksava app to confirm and withdraw.");

            request()->session()->flash('success', 'Request has been approved');

       // });



        return redirect()->back();
    }

    public function randomID()
    {
        $alphabet = "ABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";


        $pass = array();
        $alphaLength = strlen($alphabet) - 1;

        for ($i = 0; $i < 5; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $id = implode($pass);


        return $id;

    }


}
