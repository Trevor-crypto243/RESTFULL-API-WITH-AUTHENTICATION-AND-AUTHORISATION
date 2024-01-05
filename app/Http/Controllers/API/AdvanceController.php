<?php

namespace App\Http\Controllers\API;

use App\CustomerProfile;
use App\Employee;
use App\Employer;
use App\Http\Controllers\Controller;
use App\Http\Resources\GenericCollection;
use App\InterestRateMatrix;
use App\AdvanceApplication;
use App\AdvanceLoanPeriodMatrix;
use App\LoanProduct;
use App\LoanRequest;
use App\LoanRequestFee;
use App\LoanSchedule;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdvanceController extends Controller
{

    public function calculate_advance_loan(Request $request){

        $this->validate($request, [
            'loan_product_id' =>'required|exists:loan_products,id',
            'employer_id' =>'required|exists:employers,id',
            'employee_id' =>'required',
//            'employee_id' =>'required|exists:employees,id',
            'amount_requested' => 'required|numeric',
            'period_in_months' => 'required|numeric',
        ]);


        //check if has existing loan of same loan product id. this is only for advance
        $user = auth()->user();

        $notPaidApprovedLoan = LoanRequest::where('user_id',$user->id)
            ->where('loan_product_id',$request->loan_product_id)
            ->where('approval_status','APPROVED')
            ->whereIn('repayment_status',['PENDING','PARTIALLY_PAID'])
            ->first();

        if (!is_null($notPaidApprovedLoan))
            return response()->json([
                'success' => false,
                'message' => 'You have an ACTIVE salary advance loan which has not been fully paid. Please pay it from the loans section or request your HR to remit the payment before applying again',
            ], 200);


        $notPaidPendingLoan = LoanRequest::where('user_id',$user->id)
            ->where('loan_product_id',$request->loan_product_id)
            ->where('approval_status','PENDING')
            ->first();

        if (!is_null($notPaidPendingLoan))
            return response()->json([
                'success' => false,
                'message' => 'You have a salary advance loan which is PENDING approval. Please wait until the request is approved or rejected before applying again',
            ], 200);




        //check if has pending/processing application for salary advance
        $ongoingAdvanceApplication = AdvanceApplication::whereIn('quicksava_status',['PENDING','PROCESSING'])
            ->where('user_id',$user->id)
            ->first();

        if (!is_null($ongoingAdvanceApplication))
            return response()->json([
                'success' => false,
                'message' => 'You have an ongoing salary advance application. Please wait until this application is processed to end. Current status: '.$ongoingAdvanceApplication->quicksava_status,
            ], 200);




        $loanProduct = LoanProduct::find($request->loan_product_id);
        $employee = Employee::find($request->employee_id);

        if (is_null($employee)){
            $employee = Employee::where('employer_id',$request->employer_id)
                ->where('user_id',$user->id)
                ->first();
        }

        if ($request->period_in_months > $loanProduct->max_period_months){
            return response()->json([
                'success' => false,
                'message' => 'Loan period in months can not be greater than '.$loanProduct->max_period_months.' months',
            ], 200);
        }

        if ($request->amount_requested > $loanProduct->max_amount || $request->amount_requested < $loanProduct->min_amount){
            return response()->json([
                'success' => false,
                'message' => 'Amount requested can not be less than KES '.number_format($loanProduct->min_amount).' or greater than KES '.number_format($loanProduct->max_amount),
            ], 200);
        }

        $limit = ($employee->max_limit*100)/113.33;

        $allowedLimit = $limit*$request->period_in_months;
        if ($request->amount_requested > $allowedLimit){

            if ($request->period_in_months == 1)
                $resp = 'You can only borrow upto a limit of Ksh. '.number_format($limit).' per month';
            else
                $resp = 'You can only borrow upto a limit of Ksh. '.number_format($allowedLimit).' for '.$request->period_in_months.' months';

            return response()->json([
                'success' => false,
                'message' => $resp,
            ], 200);
        }


        if (auth()->user()->email == "test@quicksava.com" && $request->period_in_months <= 2){
            return response()->json([
                'success' => false,
                'message' => "We do not offer loans for a period of less than 60 days",
            ], 200);
        }



        switch ($request->period_in_months) {
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

        if ($apr == 0.0){
            return response()->json([
                'success' => false,
                'message' => 'Loan period in months can not be greater than 12 months or less than 1 month',
            ], 200);
        }


        //check age in employment
        $this_month = Carbon::now()->floorMonth(); // returns todays month
        $start_month = Carbon::parse($employee->employment_date)->floorMonth(); // returns first day of month of employment
        $diff = $start_month->diffInMonths($this_month);  // returns diff in months

        //if employed < 6 months, only give 1 month advance
        if ($diff < 6 && $request->period_in_months > 1)
            return response()->json([
                'success' => false,
                'message' => "Only one month advance allowed",
                'errors' => 'We can only advance you a loan for 1 month only at this time',
            ], 200);

        //check other employer specific time bounds
        //$employer = Employer::find($request->employer_id);

        $matrix = AdvanceLoanPeriodMatrix::where('employer_id',$request->employer_id)
            ->where('employment_period_from','<=',$diff)
            ->where('employment_period_to','>=',$diff)
            ->first();

        if (!is_null($matrix)){
            if ($request->period_in_months > $matrix->max_loan_period)
                return response()->json([
                    'success' => false,
                    'message' => "Only ".$matrix->max_loan_period." month advance allowed",
                    'errors' => 'We can only advance you a loan for a maximum of '.$matrix->max_loan_period.' months only at this time',
                ], 200);
        }



        $responseArray = array();
        $feesArray = array();

//        DB::transaction(function() use($request, $loanProduct, $user) {

        $todaysDate = Carbon::now()->isoFormat('D');
        Log::info("todays date....".$todaysDate);


        if ($todaysDate >= 24){
            //first installment is end of next month
            $firstInstallmentdate = Carbon::now()->subDays(5)->addMonth()->endOfMonth()->toDateString();
        }else{
            //first installment is end of this month
            $firstInstallmentdate = Carbon::now()->endOfMonth()->toDateString();
        }
        Log::info("firstInstallmentdate....".$firstInstallmentdate);


        $interestRate = $apr/12;

        $responseArray['interest_rate'] = number_format($loanProduct->interest_rate,3)."%";
        $responseArray['apr'] = $apr."%";

        $totalFees = 0;
        $extraMonthlyFees = 0;

        foreach ($loanProduct->fees as $fee) {

            $amt = $fee->amount_type == 'PERCENTAGE' ? $fee->amount/100 * $request->amount_requested : $fee->amount;
            $type = $fee->amount_type == 'PERCENTAGE' ? $fee->name. " (".$fee->amount."% ".$fee->frequency.")" :  $fee->name." (".$fee->frequency.")";

            if ($fee->amount_type == 'PERCENTAGE' && $fee->frequency == 'MONTHLY'){
                $amt = $amt*$request->period_in_months;
            }

            array_push($feesArray,["fee"=>$type,"amount"=>number_format($amt)]);

            if ($fee->frequency == 'MONTHLY'){

                if ($type == 'PERCENTAGE'){
                    $extraMonthlyFees += $amt;
                }else{
                    $extraMonthlyFees += $amt*$request->period_in_months;
                }
            }


            $totalFees += $fee->frequency == "ONE-OFF" ? $amt : 0;
        }


        $amount_disbursable = $request->amount_requested;

        //(loan*rate)/(1-((1+rate)^(-nper)))

        $interestRatePercentage = $interestRate/100;
        Log::info("InterestRatePercentage==>".$interestRatePercentage);
        $a = 1+$interestRatePercentage;
        Log::info("a==>".$a);
        $exponent = -1*$request->period_in_months;
        Log::info("exponent==>".$exponent);
        $raised = pow($a,$exponent);
        Log::info("Raised==>".$raised);
        $raisedFormatted = sprintf("%f",$raised);
        Log::info("raisedFormatted==>".$raisedFormatted);
        Log::info("InterestRate==>".$interestRate);
        $numerator = $request->amount_requested * $interestRatePercentage;
        Log::info("Numerator(amount*monthlyInterestRate)==>".$numerator);
        $denominator = 1-$raisedFormatted;
        Log::info("Denominator(1-raised)==>".$denominator);
        $monthlyPayment = $numerator/$denominator;
        Log::info("Monthly Payment::".$monthlyPayment);
        $totalDue = $monthlyPayment*$request->period_in_months;
        Log::info("Total Due::".$totalDue);


        $interestRatePercentage = $interestRate/100;
        $a = 1+$interestRatePercentage;
        $exponent = -1*$request->period_in_months;
        $raised = pow($a,$exponent);
        $raisedFormatted = sprintf("%f",$raised);
        $numerator = $request->amount_requested * $interestRatePercentage;
        $denominator = 1-$raisedFormatted;
        $monthlyTotalAmount = $numerator/$denominator;


        $responseArray['total_fees'] = number_format($totalFees);
        $responseArray['amount_disbursable'] = number_format($amount_disbursable);
        $responseArray['amount_payable'] = number_format($totalDue);
        $responseArray['monthly_amount'] = number_format(ceil($monthlyTotalAmount));
        $responseArray['fees'] = $feesArray;

        return response()->json([
            'success' => true,
            'data' => $responseArray,
        ], 200);
    }

    public function create_request(Request $request){

        $this->validate($request, [
            'employer_id' =>'required|exists:employers,id',
            'loan_product_id' =>'required|exists:loan_products,id',
            'amount_requested' => 'required|numeric',
            'period_in_months' => 'required|numeric',
        ]);

        $user = auth()->user();

        $customerProfile = CustomerProfile::where('user_id',$user->id)->first();

        if (is_null($customerProfile))
            return response()->json([
                'success' => false,
                'message' => 'Profile not found. Please contact customer service for assistance.',
            ], 200);


        if ($customerProfile->status != 'active')
            return response()->json([
                'success' => false,
                'message' => 'Unable to apply for a salary advance loan. Your profile is '.$customerProfile->status,
            ], 200);


        $notPaidApprovedLoan = LoanRequest::where('user_id',$user->id)
            ->where('loan_product_id',$request->loan_product_id)
            ->where('approval_status','APPROVED')
            ->whereIn('repayment_status',['PENDING','PARTIALLY_PAID'])
            ->first();

        if (!is_null($notPaidApprovedLoan))
            return response()->json([
                'success' => false,
                'message' => 'You have an ACTIVE salary advance loan which has not been fully paid. Please pay it from the loans section or request your HR to remit the payment before applying again',
            ], 200);


        $notPaidPendingLoan = LoanRequest::where('user_id',$user->id)
            ->where('loan_product_id',$request->loan_product_id)
            ->where('approval_status','PENDING')
            ->first();

        if (!is_null($notPaidPendingLoan))
            return response()->json([
                'success' => false,
                'message' => 'You have a salary advance loan which is PENDING approval. Please wait until the request is approved or rejected before applying again',
            ], 200);


        //check if has pending/processing application for salaryadvance
        $ongoingAdvanceApplication = AdvanceApplication::whereIn('quicksava_status',['PENDING','PROCESSING','AMENDMENT'])
            ->where('user_id',$user->id)
            ->first();

        if (!is_null($ongoingAdvanceApplication))
            return response()->json([
                'success' => false,
                'message' => 'You have an ongoing salary advance application. Please wait until this application is processed to end. Current status: '.$ongoingAdvanceApplication->quicksava_status,
            ], 200);


        $loanProduct = LoanProduct::find($request->loan_product_id);

        //check loan period as per loan product max period
        if ($request->period_in_months > $loanProduct->max_period_months){
            return response()->json([
                'success' => false,
                'message' => "Invalid loan period",
                'errors' => 'Loan period in months can not be greater than '.$loanProduct->max_period_months.' months',
            ], 200);
        }

        //check amount request as per loan product
        if ($request->amount_requested > $loanProduct->max_amount || $request->amount_requested < $loanProduct->min_amount){
            return response()->json([
                'success' => false,
                'message' => "Invalid loan amount",
                'errors' => 'Amount requested can not be less than KES '.number_format($loanProduct->min_amount).' or more than KES '.number_format($loanProduct->max_amount),
            ], 200);
        }


        //check if valid employee
        $employee = Employee::where('user_id', $user->id)->where('employer_id', $request->employer_id)->first();
        if (is_null($employee)){
            return response()->json([
                'success' => false,
                'message' => "Invalid employee profile",
                'errors' => 'Employee profile not found. Please contact your HR manager.',
            ], 200);
        }

        //check age in employment
        $this_month = Carbon::now()->floorMonth(); // returns todays month
        $start_month = Carbon::parse($employee->employment_date)->floorMonth(); // returns first day of month of employment
        $diff = $start_month->diffInMonths($this_month);  // returns diff in months

        //if employed < 6 months, only give 1 month advance
        if ($diff < 6 && $request->period_in_months > 1)
            return response()->json([
                'success' => false,
                'message' => "Only one month advance allowed",
                'errors' => 'We can only advance you a loan for 1 month only at this time',
            ], 200);


        //check other employer specific time bounds
        //$employer = Employer::find($request->employer_id);

        $matrix = AdvanceLoanPeriodMatrix::where('employer_id',$request->employer_id)
            ->where('employment_period_from','<=',$diff)
            ->where('employment_period_to','>=',$diff)
            ->first();

        if (!is_null($matrix)){
            if ($request->period_in_months > $matrix->max_loan_period)
                return response()->json([
                    'success' => false,
                    'message' => "Only ".$matrix->max_loan_period." month advance allowed",
                    'errors' => 'We can only advance you a loan for a maximum of '.$matrix->max_loan_period.' months only at this time',
                ], 200);
        }


        //check limit

        $requested = $request->amount_requested;

        $limit = ($employee->max_limit*100)/113.33;
        $allowed = $limit*$request->period_in_months;

        if ($requested > $allowed)
            return response()->json([
                'success' => false,
                'message' => "Requested amount not allowed",
                'errors' => 'For '.$request->period_in_months.' month(s), we can only give you a maximum of '.optional($user->wallet)->currency.' '.number_format($allowed),
            ], 200);



       // DB::transaction(function() use($request, $loanProduct, $user, $employee) {

            $AdvanceApplication = new AdvanceApplication();
            $AdvanceApplication->user_id = $user->id;
            $AdvanceApplication->employer_id = $request->employer_id;
            $AdvanceApplication->loan_product_id = $request->loan_product_id;
            $AdvanceApplication->amount_requested = $request->amount_requested;
            $AdvanceApplication->period_in_months = $request->period_in_months;
            $AdvanceApplication->purpose = $request->purpose;
            $AdvanceApplication->saveOrFail();


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

                $AdvanceApplication->payslip_url = $url;
                $AdvanceApplication->update();
            }

            send_sms($user->phone_no, "Your loan request of Ksh. ".number_format($request->amount_requested)." has been received successfully. You will be notified once the status of the application changes");

        //});



        return response()->json([
            'success' => true,
            'message' => 'Loan application has been received successfully',
        ], 200);
    }

    public function update_request(Request $request){

        $this->validate($request, [
            'advance_application_id' =>'required|exists:advance_applications,id',
            'amount_requested' => 'required|numeric',
            'period_in_months' => 'required|numeric',
        ]);

        $user = auth()->user();
        $AdvanceApplication = AdvanceApplication::find($request->advance_application_id);

        $loanProduct = $AdvanceApplication->product;

        //check loan period as per loan product max period
        if ($request->period_in_months > $loanProduct->max_period_months){
            return response()->json([
                'success' => false,
                'message' => "Invalid loan period",
                'errors' => 'Loan period in months can not be greater than '.$loanProduct->max_period_months.' months',
            ], 200);
        }

        //check amount request as per loan product
        if ($request->amount_requested > $loanProduct->max_amount || $request->amount_requested < $loanProduct->min_amount){
            return response()->json([
                'success' => false,
                'message' => "Invalid loan amount",
                'errors' => 'Amount requested can not be less than KES '.number_format($loanProduct->min_amount).' or more than KES '.number_format($loanProduct->max_amount),
            ], 200);
        }


        //check if valid employee
        $employee = Employee::where('user_id', $user->id)->where('employer_id', $AdvanceApplication->employer_id)->first();
        if (is_null($employee)){
            return response()->json([
                'success' => false,
                'message' => "Invalid employee profile",
                'errors' => 'Employee profile not found. Please contact your HR manager.',
            ], 200);
        }

        //check age in employment
        $this_month = Carbon::now()->floorMonth(); // returns todays month
        $start_month = Carbon::parse($employee->employment_date)->floorMonth(); // returns first day of month of employment
        $diff = $start_month->diffInMonths($this_month);  // returns diff in months

        //if employed < 6 months, only give 1 month advance
        if ($diff < 6 && $request->period_in_months > 1)
            return response()->json([
                'success' => false,
                'message' => "Only one month advance allowed",
                'errors' => 'We can only advance you a loan for 1 month only at this time',
            ], 200);


        //check other employer specific time bounds
        //$employer = Employer::find($request->employer_id);

        $matrix = AdvanceLoanPeriodMatrix::where('employer_id',$request->employer_id)
            ->where('employment_period_from','<=',$diff)
            ->where('employment_period_to','>=',$diff)
            ->first();

        if (!is_null($matrix)){
            if ($request->period_in_months > $matrix->max_loan_period)
                return response()->json([
                    'success' => false,
                    'message' => "Only ".$matrix->max_loan_period." month advance allowed",
                    'errors' => 'We can only advance you a loan for a maximum of '.$matrix->max_loan_period.' months only at this time',
                ], 200);
        }


        //check limit

        $requested = $request->amount_requested;

        $limit = ($employee->max_limit*100)/113.33;
        $allowed = $limit*$request->period_in_months;

        if ($requested > $allowed)
            return response()->json([
                'success' => false,
                'message' => "Requested amount not allowed",
                'errors' => 'For '.$request->period_in_months.' month(s), we can only give you a maximum of '.optional($user->wallet)->currency.' '.number_format($allowed),
            ], 200);


        if ($request->hasFile('payslip_file')){
            //update payslip on employee
            $payslipFilePath = $request->file('payslip_file')->storePublicly('payslips', 's3');

            $url = Storage::disk('s3')->url($payslipFilePath);

            $employee->latest_payslip_url = $url;
            $employee->latest_payslip_filename = basename($payslipFilePath);
            $employee->update();

            $AdvanceApplication->payslip_url = $url;
            $AdvanceApplication->update();
        }


        $AdvanceApplication->amount_requested = $request->amount_requested;
        $AdvanceApplication->period_in_months = $request->period_in_months;
        $AdvanceApplication->quicksava_status = 'PROCESSING';
        $AdvanceApplication->update();

        send_sms($user->phone_no, "Your update for your loan request has been received successfully. You will be notified once the status of the application changes");



        return response()->json([
            'success' => true,
            'message' => 'Loan application has been updated successfully',
        ], 200);
    }

    public function get_my_requests()
    {
        $applications = AdvanceApplication::where('user_id', auth()->user()->id)->orderBy('id','desc')->paginate(10);
        return new GenericCollection($applications);
    }


}
