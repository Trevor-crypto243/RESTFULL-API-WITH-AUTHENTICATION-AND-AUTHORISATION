<?php

namespace App\Http\Controllers\API;

use App\Employee;
use App\Employer;
use App\Http\Controllers\Controller;
use App\Http\Resources\GenericCollection;
use App\InterestRateMatrix;
use App\LoanProduct;
use App\LoanRepayment;
use App\LoanRequest;
use App\LoanRequestFee;
use App\LoanSchedule;
use App\Leads;
use App\User;
use App\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\LogbookLoan;



class LoansController extends Controller
{
    public function create_loan(Request $request){

        $this->validate($request, [
            'loan_product_id' =>'required|exists:loan_products,id',
            'amount_requested' => 'required|numeric',
            'period_in_months' => 'required|numeric',
        ]);

        $user = auth()->user();
        $loanProduct = LoanProduct::find($request->loan_product_id);

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


        DB::transaction(function() use($request, $loanProduct, $user) {

            $todaysDate = Carbon::now()->isoFormat('D');
            Log::info("todays date....".$todaysDate);


            if ($todaysDate >= 15){
                //first installment is end of this month
                $firstInstallmentdate = Carbon::now()->subDays(5)->addMonth()->endOfMonth()->toDateString();
            }else{
                //first installment is end of next month
                $firstInstallmentdate = Carbon::now()->endOfMonth()->toDateString();
            }
            Log::info("firstInstallmentdate....".$firstInstallmentdate);

            if($request->period_in_months == 1)
                $period = '1_MONTH';
            elseif ($request->period_in_months == 2)
                $period = '2_MONTHS';
            elseif ($request->period_in_months > 2 && $request->period_in_months <= 5)
                $period = '3_5_MONTHS';
            elseif ($request->period_in_months > 5 && $request->period_in_months <= 12)
                $period = '6_12_MONTHS';
            else
                $period = '12_PLUS_MONTHS';

            $isNew = !(LoanRequest::where('user_id', $user->id)
                    ->whereIn('repayment_status', ['PARTIALLY_PAID', 'PAID'])
                    ->count() > 0);

            if ($isNew)
                $interestRate = optional(InterestRateMatrix::where('loan_period',$period)
                    ->where('loan_product_id',$request->loan_product_id)
                    ->first())->new_client_interest;
            else
                $interestRate = optional(InterestRateMatrix::where('loan_period',$period)
                    ->where('loan_product_id',$request->loan_product_id)
                    ->first())->existing_client_interest;

            $loan = new LoanRequest();
            $loan->user_id = $user->id;
            $loan->loan_product_id = $request->loan_product_id;
            $loan->amount_requested = $request->amount_requested;
            $loan->amount_disbursable = $request->amount_requested;
            $loan->interest_rate = $interestRate;
            $loan->fees = 0;
            $loan->period_in_months = $request->period_in_months;
            $loan->saveOrFail();

            $totalFees = 0;

            foreach ($loanProduct->fees as $fee) {

                $amt = $fee->amount_type == 'PERCENTAGE' ? $fee->amount/100 * $loan->amount_requested : $fee->amount;
                $type = $fee->amount_type == 'PERCENTAGE' ? $fee->name. " (".$fee->amount."%)" :  $fee->name;

                if ($fee->amount_type == 'PERCENTAGE' && $fee->frequency == 'MONTHLY'){
                    $amt = $amt*$request->period_in_months;
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
                $loan->amount_disbursable = $request->amount_requested - $totalFees;
                $loan->fees = $totalFees;
                $loan->update();
            }


            //interest accruing monthly
            $interestAccrued = (($request->amount_requested * $interestRate)/100) * $request->period_in_months;
            Log::info("interest....". $interestAccrued);

            //extra monthly fees
            $extraMonthlyFees = 0;
            foreach (LoanRequestFee::where('loan_request_id', $loan->id)->where('frequency','MONTHLY')->get() as $requestFee){
                $extraMonthlyFees += $requestFee->amount;
            }
            $totalMonthlyFees = $extraMonthlyFees * $request->period_in_months;


            $month = 1;
            $monthlyTotalAmount = ( $request->amount_requested+$interestAccrued+$totalMonthlyFees)/$request->period_in_months;
            $monthlyInterest = $interestAccrued/$request->period_in_months;

            $installmentDate = Carbon::parse($firstInstallmentdate);
            $beginningBalance = $request->amount_requested+$interestAccrued+$totalMonthlyFees;

            Log::info("entering while....");

            while($month <= $request->period_in_months) {

                Log::info("while month....".$month);


                $loanSchedule = new LoanSchedule();
                $loanSchedule->loan_request_id = $loan->id;
                $loanSchedule->payment_date = $installmentDate;
                $loanSchedule->beginning_balance = $beginningBalance;
                $loanSchedule->scheduled_payment = $monthlyTotalAmount;
                $loanSchedule->interest_paid = $monthlyInterest;
                $loanSchedule->principal_paid = $monthlyTotalAmount - $monthlyInterest - $extraMonthlyFees;
                $loanSchedule->ending_balance = $beginningBalance - $monthlyTotalAmount;
                $loanSchedule->saveOrFail();

                $installmentDate->subDays(5)->addMonth()->endOfMonth();
                $month++;
                $beginningBalance -= $monthlyTotalAmount;
            }
        });


        send_sms($user->phone_no, "Your loan request of Ksh. ".number_format($request->amount_requested)." has been received successfully. You will be notified once the status of the application changes");

        return response()->json([
            'success' => true,
            'message' => 'Loan application has been received successfully',
        ], 200);
    }

    public function calculate_loan(Request $request){

        $this->validate($request, [
            'loan_product_id' =>'required|exists:loan_products,id',
//            'employer_id' =>'required|exists:employers,id',
//            'employee_id' =>'required|exists:employees,id',
            'amount_requested' => 'required|numeric',
            'period_in_months' => 'required|numeric',
        ]);

//        $user = User::find($request->user_id);
        $user = auth()->user();
        $loanProduct = LoanProduct::find($request->loan_product_id);

        $employee = Employee::where('user_id', $user->id)->first();


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


        if (auth()->user()->email == "inuatest@gmail.com" && $request->period_in_months <= 2){
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
                $apr = 71.525;
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

        $responseArray['interest_rate'] = number_format($interestRate,3)."%";
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

//        if ($totalFees > 0){
//
//            if ($loanProduct->fee_application == 'BEFORE DISBURSEMENT'){
//                $amount_disbursable = $request->amount_requested - $totalFees;
//            }else{
//                $amount_disbursable = $request->amount_requested;
//            }
//        }else{
//            $amount_disbursable = $request->amount_requested;
//        }

        $amount_disbursable = $request->amount_requested;


        $monthlyPayment = ($request->amount_requested * $interestRate)/100
            + ($request->amount_requested/$request->period_in_months);


        $totalDue = $monthlyPayment*$request->period_in_months;
//        $totalDue = ($monthlyPayment*$request->period_in_months) + $totalFees + $extraMonthlyFees;



        $responseArray['total_fees'] = number_format($totalFees);
        $responseArray['amount_disbursable'] = number_format($amount_disbursable);
        $responseArray['amount_payable'] = number_format($totalDue);
        $responseArray['fees'] = $feesArray;

        return response()->json([
            'success' => true,
            'data' => $responseArray,
        ], 200);
    }

//    public function calculate_loan(Request $request){
//
//        $this->validate($request, [
//            'user_id' =>'required|numeric',
//            'loan_product_id' =>'required|exists:loan_products,id',
//            'amount_requested' => 'required|numeric',
//            'period_in_months' => 'required|numeric',
//        ]);
//
//        $user = User::find($request->user_id);
//        $loanProduct = LoanProduct::find($request->loan_product_id);
//
//        if ($request->period_in_months > $loanProduct->max_period_months){
//            return response()->json([
//                'success' => false,
//                'message' => 'Loan period in months can not be greater than '.$loanProduct->max_period_months.' months',
//            ], 200);
//        }
//
//        if ($request->amount_requested > $loanProduct->max_amount || $request->amount_requested < $loanProduct->min_amount){
//            return response()->json([
//                'success' => false,
//                'message' => 'Amount requested can not be less than KES '.number_format($loanProduct->min_amount).' or greater than KES '.number_format($loanProduct->max_amount),
//            ], 200);
//        }
//
//        $responseArray = array();
//        $feesArray = array();
//
////        DB::transaction(function() use($request, $loanProduct, $user) {
//
//            $todaysDate = Carbon::now()->isoFormat('D');
//            Log::info("todays date....".$todaysDate);
//
//
//            if ($todaysDate >= 15){
//                //first installment is end of this month
//                $firstInstallmentdate = Carbon::now()->subDays(5)->addMonth()->endOfMonth()->toDateString();
//            }else{
//                //first installment is end of next month
//                $firstInstallmentdate = Carbon::now()->endOfMonth()->toDateString();
//            }
//            Log::info("firstInstallmentdate....".$firstInstallmentdate);
//
//            if($request->period_in_months == 1)
//                $period = '1_MONTH';
//            elseif ($request->period_in_months == 2)
//                $period = '2_MONTHS';
//            elseif ($request->period_in_months > 2 && $request->period_in_months <= 5)
//                $period = '3_5_MONTHS';
//            elseif ($request->period_in_months > 5 && $request->period_in_months <= 12)
//                $period = '6_12_MONTHS';
//            else
//                $period = '12_PLUS_MONTHS';
//
//            $isNew = !(LoanRequest::where('user_id', $user->id)
//                    ->whereIn('repayment_status', ['PARTIALLY_PAID', 'PAID'])
//                    ->count() > 0);
//
//            if ($isNew)
//                $interestRate = optional(InterestRateMatrix::where('loan_period',$period)
//                    ->where('loan_product_id',$request->loan_product_id)
//                    ->first())->new_client_interest;
//            else
//                $interestRate = optional(InterestRateMatrix::where('loan_period',$period)
//                    ->where('loan_product_id',$request->loan_product_id)
//                    ->first())->existing_client_interest;
//
//            $responseArray['interest_rate'] = $interestRate."%";
//
//
//            $totalFees = 0;
//            $extraMonthlyFees = 0;
//
//            foreach ($loanProduct->fees as $fee) {
//
//                $amt = $fee->amount_type == 'PERCENTAGE' ? $fee->amount/100 * $request->amount_requested : $fee->amount;
//                $type = $fee->amount_type == 'PERCENTAGE' ? $fee->name. " (".$fee->amount."% ".$fee->frequency.")" :  $fee->name." (".$fee->frequency.")";
//
//                if ($fee->amount_type == 'PERCENTAGE' && $fee->frequency == 'MONTHLY'){
//                    $amt = $amt*$request->period_in_months;
//                }
//
//                array_push($feesArray,["fee"=>$type,"amount"=>number_format($amt)]);
//
//                if ($fee->frequency == 'MONTHLY'){
//                    $extraMonthlyFees += $amt*$request->period_in_months;
//                }
//
//
//                $totalFees += $fee->frequency == "ONE-OFF" ? $amt : 0;
//            }
//
//            if ($totalFees > 0){
//                $amount_disbursable = $request->amount_requested - $totalFees;
//            }
//
//
//        //interest accruing monthly
//        $interestAccrued = (($request->amount_requested * $interestRate)/100) * $request->period_in_months;
//        $totalAmount = $request->amount_requested+$interestAccrued+$extraMonthlyFees;
//
//
//
//        $responseArray['total_fees'] = number_format($totalFees);
//        $responseArray['amount_disbursable'] = number_format($amount_disbursable);
//        $responseArray['amount_payable'] = number_format($totalAmount);
//        $responseArray['fees'] = $feesArray;
//
//        return response()->json([
//            'success' => true,
//            'data' => $responseArray,
//        ], 200);
//    }

    public function get_loan_count()
    {

        $responseArray = array();
        $responseArray['pending'] = LoanRequest::where('user_id', auth()->user()->id)->where('approval_status','PENDING')->count();
        $responseArray['approved'] = LoanRequest::where('user_id', auth()->user()->id)->where('approval_status','APPROVED')->count();
        $responseArray['rejected'] = LoanRequest::where('user_id', auth()->user()->id)->where('approval_status','REJECTED')->count();

        return response()->json([
            'success' => true,
            'data' => $responseArray
        ], 200);

    }

    public function get_my_loans()
    {

        $loans = LoanRequest::where('user_id', auth()->user()->id)->orderBy('id','desc')->paginate(10);

        return new GenericCollection($loans);


    }

    public function get_loan_details($loanId)
    {

        $loan = LoanRequest::find($loanId);

        if (is_null($loan))
            return response()->json([
                'success' => false,
                'message' => 'Invalid Loan',
                'errors' => 'The requested loan could not be found. Please try again',
            ], 200);

        if ($loan->user_id != auth()->user()->id)
            return response()->json([
                'success' => false,
                'message' => 'Not allowed',
                'errors' => 'You are not allowed to access this resource. Please try again',
            ], 200);

        $responseArray = array();

        $paymentSchedules = LoanSchedule::where('loan_request_id',$loanId)->get();


        $due = 0;
        $paid = 0;

        foreach ($paymentSchedules as $paymentSchedule){
            $paid += $paymentSchedule->actual_payment_done;
            $due += $paymentSchedule->scheduled_payment;
        }

        $loanBalance = $due-$paid;




        $responseArray['loan_product_name'] = optional($loan->product)->name;
        $responseArray['created_at'] = $loan->created_at;
        $responseArray['interest_rate'] = optional($loan->product)->interest_rate;
        $responseArray['loan_amount'] = number_format($loan->amount_requested,2);
        $responseArray['approval_status'] = $loan->approval_status;
        $responseArray['repayment_status'] = $loan->repayment_status;
        $responseArray['period_in_months'] = $loan->period_in_months;
        $responseArray['loan_balance'] = number_format($loanBalance,2);




        return response()->json([
            'success' => true,
            'data' => $responseArray,
            'repaymentSchedule' => $paymentSchedules,
        ], 200);

    }


    public function pay_loan(Request  $request)
    {

        $this->validate($request, [
            'amount' =>'required|numeric',
            'loan_id' =>'required|exists:loan_requests,id',
        ]);


        if ($request->amount <= 0)
            return response()->json([
                'success' => false,
                'message' => 'Invalid Amount',
                'errors' => 'Please enter amounts greater than zero',
            ], 200);


        $loan = LoanRequest::find($request->loan_id);

        if (is_null($loan))
            return response()->json([
                'success' => false,
                'message' => 'Invalid Loan',
                'errors' => 'The requested loan could not be found. Please try again',
            ], 200);

        if ($loan->user_id != auth()->user()->id)
            return response()->json([
                'success' => false,
                'message' => 'Not allowed',
                'errors' => 'You are not allowed to access this resource. Please try again',
            ], 200);


        //check loan approval status
        if ($loan->approval_status == "PENDING" || $loan->approval_status == "REJECTED")
            return response()->json([
                'success' => false,
                'message' => 'Loan approval is '.$loan->approval_status,
                'errors' => 'Your loan approval status is '.$loan->approval_status.'. Payment not allowed',
            ], 200);

        //check loan repayment status
        if ($loan->repayment_status == "PAID" || $loan->repayment_status == "CANCELLED")
            return response()->json([
                'success' => false,
                'message' => 'Loan repayment is '.$loan->repayment_status,
                'errors' => 'Your loan repayment status is '.$loan->repayment_status.'. Payment not allowed',
            ], 200);


        //check wallet balance
        $wallet = auth()->user()->wallet;

        if ($wallet->current_balance < $request->amount)
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance',
                'errors' => 'You do not have enough balance in your wallet to make this payment. Please top up your balance to continue.',
            ], 200);


        /*
         * do payment
         *
         */

        //get first payment schedule
        $paymentSchedule = LoanSchedule::where('loan_request_id',$loan->id)
            ->whereIn('status',['UNPAID','PARTIALLY_PAID'])
            ->orderBy('id', 'asc')
            ->first();

        $amountInstructed = $request->amount;
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


            $paymentSchedule = LoanSchedule::where('loan_request_id',$loan->id)
                ->whereIn('status',['UNPAID','PARTIALLY_PAID'])
                ->orderBy('id', 'asc')
                ->first();
        }


        //get loan balance after payment
        $paymentSchedules = LoanSchedule::where('loan_request_id',$loan->id)->get();
        $due = 0;
        $paid = 0;
        foreach ($paymentSchedules as $paymentSchedule){
            $paid += $paymentSchedule->actual_payment_done;
            $due += $paymentSchedule->scheduled_payment;
        }
        $loanBalance = $due-$paid;

        $receipt = $this->randomID();


        if ($amountPaid > 0){

            //create loan repayment
            $loanRepayment = new LoanRepayment();
            $loanRepayment->loan_request_id = $loan->id;
            $loanRepayment->amount_repaid = $amountPaid;
            $loanRepayment->outstanding_balance = $loanBalance;
            $loanRepayment->transaction_receipt_number = $receipt;
            $loanRepayment->payment_channel = 'Quicksava Wallet';
            $loanRepayment->description = 'User initiated payment from wallet';
            $loanRepayment->saveOrFail();



            //update loan if completely paid
            if ($loanBalance == 0){
                $loan->repayment_status = 'PAID';
            }else{
                $loan->repayment_status = 'PARTIALLY_PAID';
            }
            $loan->update();



            //update wallet
            $prevBal = $wallet->current_balance;

            //save to wallet transactions
            $walletTransaction = new WalletTransaction();
            $walletTransaction->wallet_id = $wallet->id;
            $walletTransaction->amount = $amountPaid;
            $walletTransaction->previous_balance = $prevBal;
            $walletTransaction->transaction_type = 'DR';
            $walletTransaction->source = 'Quicksava Wallet';
            $walletTransaction->trx_id = $receipt;
            $walletTransaction->narration = "Loan repayment for loan ID #".$loan->id;
            $walletTransaction->saveOrFail();


            $wallet->current_balance = $wallet->current_balance - $amountPaid;
            $wallet->previous_balance = $prevBal;
            $wallet->update();
        }





        return response()->json([
            'success' => true,
            'message' => "Your payment of Ksh. ".number_format($amountPaid,2)." has been received successfully.",
        ], 200);

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


    public function leads(Request $request){
        Log::info("The leads api called");
        $request->validate([
            'name'=>'required|string',
            'email'=>'required|string',
            'msisdn'=>'required|string',
            'id_no'=>'required',
            'loan_product_id'=>'required'
        ]);

    
        try{
            DB::transaction(function() use($request){
                $lead = new Leads([
                    'name'=> $request->name,
                    'email'=> $request->email,
                    'msisdn'=> $request->msisdn,
                    'id_no'=>$request->id_no,
                    'loan_product_id'=>$request->loan_product_id
                ]);
    
                $lead->save();   
            });
            send_sms($request->msisdn,"Thanks for applying for a loan at quicksava! Someone will get in touch with you shortly. Meanwhile you signup using this link"
            ."https://api.quicksava.com/api/register");

            //send an email to customer service
            // $lead->sendEmailVerificationNotification();
            Mail::to("trevorogina@gmail.com")->send(new WelcomeEmail($request->name, $request->email, $request->msisdn));

            //response object
            return response()->json([
                'status'=> 200,
                'success'=>true,
                'message'=>'Loan request made successfuly, sign up to continue'
            ],200);

        }catch(Exception $e){
            return response()->json([
                'success'=>false,
                'message'=>$e,
                'errors'=>$e
            ],421);
        }        
    }

    public function upload_documents(Request $request){
        Log::info("The upload documents api called");
        
        $request->validate([
            'kra_pin'=>'required|string',
            'requested_amount'=>'required',
            'payment_period'=>'required',
            'id_back' => 'required|file',
            'id_front' => 'required|file',
            'mpesa_statement' => 'required|file',
            'bank_statement' => 'required|file',
            'user_id'=>'required',
            'till_paybill'=>'required|file'
        ]);
        
        try{
        //Saving the Documents
        $logbookLoan = LogbookLoan::find($request->user_id);

        $id_backPath = $request->file('id_back')->storePublicly('logbook_docs', 's3');
        $id_frontPath = $request->file('id_front')->storePublicly('logbook_docs', 's3');
        $mpesaStatementPath = $request->file('mpesa_statement')->storePublicly('logbook_docs', 's3');
        $bankStatementPath = $request->file('bank_statement')->storePublicly('logbook_docs', 's3');
        $bankStatementPath = $request->file('till_paybill')->storePublicly('logbook_docs', 's3');





        $id_backurl = Storage::disk('s3')->url($id_backPath);
        $id_fronturl = Storage::disk('s3')->url($id_frontPath);
        $mpesaStatementurl = Storage::disk('s3')->url($mpesaStatementPath);
        $bankStatementurl = Storage::disk('s3')->url($bankStatementPath);


        $logbookLoan->id_back_url = $id_backurl;
        $logbookLoan->id_back_url = $id_fronturl;
        $logbookLoan->id_back_url = $mpesaStatementurl;
        $logbookLoan->id_back_url = $bankStatementurl;


        // Saving the Loan applicant

        
        $logbookLoan = new LogbookLoan();
        $logbookLoan->user_id = $request->user_id;
        $logbookLoan->applicant_type = "INDIVIDUAL";
        $logbookLoan->status = 'NEW';
        $logbookLoan->requested_amount = $request->requested_amount;
        $logbookLoan->payment_period = $request->payment_period;
        $logbookLoan->personal_kra_pin = $request->personal_kra_pin;
        $logbookLoan->loan_purpose = "N/A";
        $logbookLoan->save();

        return response()->json([
            'success' => true,
            'message' => 'A new logbook loan application has been opened',
            'data' => $logbookLoan
        ], 200);
        
         }catch(Exception $e){
            return response()->json([
                'success'=>false,
                'message'=>$e,
                'errors'=>$e
            ],421);
        }     
    }

}
