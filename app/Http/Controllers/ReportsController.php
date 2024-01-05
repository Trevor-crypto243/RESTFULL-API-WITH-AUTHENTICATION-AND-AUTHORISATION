<?php

namespace App\Http\Controllers;

use App\BulkDisbursement;
use App\CustomerProfile;
use App\Employee;
use App\Employer;
use App\EmployerLoanProduct;
use App\InvoiceDiscount;
use App\Loan;
use App\LoanRepayment;
use App\LoanRequest;
use App\LoanSchedule;
use App\MtdTarget;
use App\Repositories\Data;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\In;
use Yajra\DataTables\Facades\DataTables;

class ReportsController extends Controller
{

    public function advance_repayments(){
        return view ('reports.advance_repayments');
    }

    public function advance_repaymentsDT() {

        if (Data::isValid($_GET, 'date_range') && Data::isValid($_GET, 'employer_id')) {
            $date_range = $_GET['date_range'];
            $employer_id = $_GET['employer_id'];
//            Log::info($date_range);
            $dates = explode(' - ', $date_range);
            $from_date = date('Y/m/d 00:00:00', strtotime($dates[0]));
            $to_date = date('Y/m/d 23:59:59', strtotime($dates[1]));

            $employer = Employer::find($employer_id);

            $loanProducts = EmployerLoanProduct::select('loan_product_id')
                ->where('employer_id',$employer_id)
                ->pluck('loan_product_id');

            $userIds = Employee::select('user_id')
                ->where('employer_id',$employer_id)
                ->pluck('user_id');

            $loanRequests=LoanRequest::select('id')
                ->whereIn('loan_product_id',$loanProducts)
                ->whereIn('user_id',$userIds)
                ->pluck('id');

//            Log::info("loan requests::".$loanRequests);

            $loanSchedules = LoanSchedule::whereDate('payment_date','>=',$from_date)
                ->whereDate('payment_date','<=',$to_date)
                ->whereIn('loan_request_id',$loanRequests)
                ->get();
        }else{
            $employer = Employer::first();

            $loanSchedules = LoanSchedule::whereIn('loan_request_id',[0])->get();
        }




        return DataTables::of($loanSchedules)


            ->editColumn('name', function ($loanSchedules) {
                return optional(optional($loanSchedules->loan)->user)->name.' '.optional(optional($loanSchedules->loan)->user)->surname;
            })

            ->editColumn('loan_amount', function ($loanSchedules) {
                return number_format(optional($loanSchedules->loan)->amount_requested);
            })

            ->editColumn('branch', function ($loanSchedules) use ($employer) {
                $employee = Employee::where('employer_id', $employer->id)
                    ->where('user_id',optional($loanSchedules->loan)->user_id)
                    ->first();

                return is_null($employee) ? "" : $employee->location;
            })

            ->editColumn('approved_date', function ($loanSchedules) {
                return Carbon::parse(optional($loanSchedules->loan)->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('payroll_no', function ($loanSchedules) use ($employer) {

                $employee = Employee::where('employer_id', $employer->id)
                    ->where('user_id',optional($loanSchedules->loan)->user_id)
                    ->first();

                return is_null($employee) ? "" : $employee->payroll_no;
            })

            ->editColumn('id_no', function ($loanSchedules) {
                return optional(optional($loanSchedules->loan)->user)->id_no;
            })

            ->editColumn('phone_no', function ($loanSchedules) {
                return optional(optional($loanSchedules->loan)->user)->phone_no;
            })

            ->editColumn('loan_term', function ($loanSchedules) {
                return optional($loanSchedules->loan)->period_in_months.' Months';
            })

            ->editColumn('previous_loans', function ($loanSchedules) {

                $loans = LoanRequest::where('approval_status', 'APPROVED')
                    ->where('user_id',optional($loanSchedules->loan)->user_id)
                    ->count();
                return $loans-1;
            })

            ->editColumn('opening_balance', function ($loanSchedules) {
                return number_format($loanSchedules->beginning_balance,2);
            })

            ->editColumn('installment', function ($loanSchedules) {
                return number_format($loanSchedules->scheduled_payment,2);
            })

            ->editColumn('date_paid', function ($loanSchedules) {
                return Carbon::parse($loanSchedules->payment_date)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('status', function ($loanSchedules) {
                return  $loanSchedules->status;
            })

            ->editColumn('arrears', function ($loanSchedules) {
                return  number_format($loanSchedules->scheduled_payment - $loanSchedules->actual_payment_done ,2);
            })

            ->editColumn('closing_loan_balance', function ($loanSchedules) {
                return number_format($loanSchedules->ending_balance);
            })

            ->make(true);

    }

    public function repayments(){
        return view ('reports.repayments');
    }

    public function repaymentsDT() {

        if (Data::isValid($_GET, 'date_range') && Data::isValid($_GET, 'employer_id')) {
            $date_range = $_GET['date_range'];
            $employer_id = $_GET['employer_id'];
//            Log::info($date_range);
            $dates = explode(' - ', $date_range);
            $from_date = date('Y/m/d 00:00:00', strtotime($dates[0]));
            $to_date = date('Y/m/d 23:59:59', strtotime($dates[1]));

            $employer = Employer::find($employer_id);

            $loanProducts = EmployerLoanProduct::select('loan_product_id')
                ->where('employer_id',$employer_id)
                ->pluck('loan_product_id');

            $userIds = Employee::select('user_id')
                ->where('employer_id',$employer_id)
                ->pluck('user_id');

            $loanRequests=LoanRequest::select('id')
                ->whereIn('loan_product_id',$loanProducts)
                ->whereIn('user_id',$userIds)
                ->pluck('id');

//            Log::info("loan requests::".$loanRequests);

            $loanRepayments = LoanRepayment::whereDate('created_at','>=',$from_date)
                ->whereDate('created_at','<=',$to_date)
                ->whereIn('loan_request_id',$loanRequests)
                ->get();
        }else{
            $employer = Employer::first();

            $loanRepayments = LoanRepayment::whereIn('loan_request_id',[0])->get();
        }




        return DataTables::of($loanRepayments)


            ->editColumn('name', function ($loanRepayments) {
                return optional(optional($loanRepayments->loan_request)->user)->name.' '. optional(optional($loanRepayments->loan_request)->user)->surname;
            })

            ->editColumn('id_no', function ($loanRepayments) {
                return optional(optional($loanRepayments->loan_request)->user)->id_no;
            })

            ->editColumn('phone_no', function ($loanRepayments) {
                return optional(optional($loanRepayments->loan_request)->user)->phone_no;
            })

            ->editColumn('payroll_no', function ($loanRepayments) use ($employer) {

                $employee = Employee::where('employer_id', $employer->id)
                    ->where('user_id',optional($loanRepayments->loan_request)->user_id)
                    ->first();

                return is_null($employee) ? "" : $employee->payroll_no;
            })

            ->editColumn('loan_amount', function ($loanRepayments) {
                return number_format(optional($loanRepayments->loan_request)->amount_requested);
            })

            ->editColumn('approved_date', function ($loanRepayments) {
                return Carbon::parse(optional($loanRepayments->loan_request)->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('outstanding_balance', function ($loanRepayments) {
                return number_format($loanRepayments->outstanding_balance,2);
            })

            ->editColumn('amount_repaid', function ($loanRepayments) {
                return number_format($loanRepayments->amount_repaid,2);
            })

            ->editColumn('date_paid', function ($loanRepayments) {
                return Carbon::parse($loanRepayments->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->make(true);

    }

    public function insurance_data(){
        return view ('reports.insurance_data');
    }

    public function insurance_dataDT() {

        if (Data::isValid($_GET, 'employer_id') && Data::isValid($_GET, 'date_approved')) {
            $employer_id = $_GET['employer_id'];
            $date_approved = $_GET['date_approved'];

            $employer = Employer::find($employer_id);

            $loanProducts = EmployerLoanProduct::select('loan_product_id')
                ->where('employer_id',$employer_id)
                ->pluck('loan_product_id');

            $userIds = Employee::select('user_id')
                ->where('employer_id',$employer_id)
                ->pluck('user_id');

            $loanRequests=LoanRequest::whereIn('loan_product_id',$loanProducts)
                ->whereIn('user_id',$userIds)
                ->whereDate('created_at',$date_approved)
                ->get();

//            Log::info("loan requests::".$loanRequests);

        }else{
            $employer = Employer::first();

            $loanRequests=LoanRequest::where('id',0)
                ->get();
        }




        return DataTables::of($loanRequests)


            ->editColumn('name', function ($loanRequests) {
                return optional($loanRequests->user)->name.' '.optional($loanRequests->user)->surname;
            })
            ->editColumn('payroll_no', function ($loanRequests) use ($employer) {

                $employee = Employee::where('employer_id', $employer->id)
                    ->where('user_id',$loanRequests->user_id)
                    ->first();

                return is_null($employee) ? "" : $employee->payroll_no;
            })
            ->editColumn('phone_no', function ($loanRequests) {
                return optional($loanRequests->user)->phone_no;
            })
            ->editColumn('dob', function ($loanRequests) {
                $profile =  CustomerProfile::where('user_id',$loanRequests->user_id)->first();

                if (is_null($profile)){
                    return 'N/A';
                }else{
                    return Carbon::parse($profile->dob)->isoFormat('MMM Do YYYY');
                }
            })
            ->editColumn('id_no', function ($loanRequests) {
                return optional($loanRequests->user)->id_no;
            })

            ->editColumn('loan_amount', function ($loanRequests) {
                return number_format($loanRequests->amount_requested);
            })

            ->editColumn('opening_balance', function ($loanRequests) {
                $schedule = LoanSchedule::where('loan_request_id',$loanRequests->id)->orderBy('id','asc')->first();
                return number_format($schedule->beginning_balance,2);
            })

            ->editColumn('loan_period', function ($loanRequests) {
                return number_format($loanRequests->period_in_months).' Months';
            })

            ->editColumn('gender', function ($loanRequests) {
                $profile =  CustomerProfile::where('user_id',$loanRequests->user_id)->first();

                if (is_null($profile)){
                    return 'N/A';
                }else{
                    return $profile->gender;
                }
            })

            ->editColumn('employment_date', function ($loanRequests) use ($employer) {
                $employee = Employee::where('employer_id', $employer->id)
                    ->where('user_id',$loanRequests->user_id)
                    ->first();

                return is_null($employee) ? "N/A" : Carbon::parse($employee->employment_date)->isoFormat('MMM Do YYYY');

            })

            ->editColumn('created_at', function ($loanRequests) {
                return Carbon::parse($loanRequests->created_at)->isoFormat('MMM Do YYYY');
            })

            ->make(true);

    }

    public function running_lb(){
        return view ('reports.running_lb');
    }

    public function running_lbDT() {

        if (Data::isValid($_GET, 'loan_product_id')) {
            $loan_product_id = $_GET['loan_product_id'];

            $loanRequests = LoanRequest::where('loan_product_id',$loan_product_id)
                ->whereIn('repayment_status',['PENDING','PARTIALLY_PAID'])
                ->get();

        }else{
            $loanRequests=LoanRequest::where('id',0)->get();
        }




        return DataTables::of($loanRequests)


            ->editColumn('name', function ($loanRequests) {
                return optional($loanRequests->user)->name.' '.optional($loanRequests->user)->surname;
            })
            ->editColumn('payroll_no', function ($loanRequests) {

                $employee = Employee::where('user_id',$loanRequests->user_id)
                    ->first();

                return is_null($employee) ? 'N/A' : $employee->payroll_no;
            })

            ->editColumn('id_no', function ($loanRequests) {
                return optional($loanRequests->user)->id_no;
            })

            ->editColumn('loan_amount', function ($loanRequests) {
                return number_format($loanRequests->amount_requested);
            })

            ->editColumn('running_balance', function ($loanRequests) {
                $total = LoanSchedule::where('loan_request_id',$loanRequests->id)->sum('scheduled_payment');
                $paid = LoanSchedule::where('loan_request_id',$loanRequests->id)->sum('actual_payment_done');
                return number_format($total-$paid,2);
            })

            ->editColumn('outstanding_due', function ($loanRequests) {
                $total = LoanSchedule::where('loan_request_id',$loanRequests->id)
                    ->whereDate('payment_date','<=', Carbon::today())
                    ->sum('scheduled_payment');
                $paid = LoanSchedule::where('loan_request_id',$loanRequests->id)
                    ->whereDate('payment_date','<=', Carbon::today())
                    ->sum('actual_payment_done');

                return number_format($total-$paid,2);
            })

            ->editColumn('loan_period', function ($loanRequests) {
                return number_format($loanRequests->period_in_months).' Months';
            })

            ->editColumn('installment', function ($loanRequests) {
                $schedule = LoanSchedule::where('loan_request_id',$loanRequests->id)->first();

                if (is_null($schedule))
                    return 0;
                else
                    return number_format($schedule->scheduled_payment,2);
            })

            ->editColumn('created_at', function ($loanRequests) {
                return Carbon::parse($loanRequests->created_at)->isoFormat('MMM Do YYYY');
            })

            ->make(true);

    }

    public function mtd(){
        $employer = Employer::orderBy('id','asc')->first();

        return view ('reports.mtd')->with(['employer'=>$employer]);
    }

    public function mtd_filter(Request $request){
        $data = request()->validate([
            'employer_id' => 'required|exists:employers,id',
        ]);

        $employer = Employer::find($request->employer_id);
        return view ('reports.mtd')->with(['employer'=>$employer]);
    }

    public function mtdDT($employer_id) {

        $employer =  Employer::find($employer_id);

        if ($employer->salary_advance == true && $employer->invoice_discounting == false){
            $lpIds = EmployerLoanProduct::where('employer_id',$employer_id)->pluck('loan_product_id');

            $loanRequests = Loan::select(
                    DB::raw('COUNT(id) as loans_requested'),
                    DB::raw('SUM(amount_requested) as sum_requested'),
                    DB::raw("EXTRACT(YEAR FROM `created_at`) as year"),
                    DB::raw("EXTRACT(MONTH FROM `created_at`) as month"))
                ->whereIn('loan_product_id',$lpIds)
                ->where('approval_status','APPROVED')
                ->groupBy('month', 'year');
        }elseif ($employer->salary_advance == false && $employer->invoice_discounting == true){

            $loanRequests = InvoiceDiscount::select(
                    DB::raw('COUNT(id) as loans_requested'),
                    DB::raw('SUM(approved_amount) as sum_requested'),
                    DB::raw("EXTRACT(YEAR FROM `created_at`) as year"),
                    DB::raw("EXTRACT(MONTH FROM `created_at`) as month"))
                ->where('employer_id',$employer_id)
                ->where('offer_status','ACCEPTED')
                ->groupBy('month', 'year');
        }elseif ($employer->salary_advance == true && $employer->invoice_discounting == true){
            $lpIds = EmployerLoanProduct::where('employer_id',$employer_id)->pluck('loan_product_id');

            $idfLoanIds = InvoiceDiscount::where('employer_id', $employer_id)->pluck('loan_request_id');

            $loanRequests = Loan::select(
                    DB::raw('COUNT(id) as loans_requested'),
                    DB::raw('SUM(amount_requested) as sum_requested'),
                    DB::raw("EXTRACT(YEAR FROM `created_at`) as year"),
                    DB::raw("EXTRACT(MONTH FROM `created_at`) as month"))
                ->whereIn('loan_product_id',$lpIds)
                ->orWhereIn('id',$idfLoanIds)
                ->where('approval_status','APPROVED')
                ->groupBy('month', 'year')
                ->orderBy('month', 'desc')
                ->orderBy('year', 'desc');
        }





        return DataTables::of($loanRequests)

            ->editColumn('loans_requested', function ($loanRequests) {
                return number_format($loanRequests->loans_requested);
            })

            ->editColumn('loans_targeted', function ($loanRequests) use ($employer_id) {
                $mtdTarget = MtdTarget::where('employer_id',$employer_id)
                    ->where('year',$loanRequests->year)
                    ->where('month',$loanRequests->month)
                    ->first();

                return is_null($mtdTarget) ? 0 : number_format($mtdTarget->target_loans);
            })


            ->editColumn('sum_requested', function ($loanRequests) {
                return number_format($loanRequests->sum_requested);
            })

            ->editColumn('sum_targeted', function ($loanRequests) use ($employer_id) {
                $mtdTarget = MtdTarget::where('employer_id',$employer_id)
                    ->where('year',$loanRequests->year)
                    ->where('month',$loanRequests->month)
                    ->first();

                return is_null($mtdTarget) ? 0 : number_format($mtdTarget->target_loans_value);
            })

            ->editColumn('target_achieved', function ($loanRequests) use ($employer_id) {
                $mtdTarget = MtdTarget::where('employer_id',$employer_id)
                    ->where('year',$loanRequests->year)
                    ->where('month',$loanRequests->month)
                    ->first();

                return is_null($mtdTarget) ? '100%' :
                    number_format(($loanRequests->sum_requested/$mtdTarget->target_loans_value)*100,2).'%';
            })


            ->make(true);

    }

    public function mtd_range(){
        return view ('reports.mtd_range');
    }

    public function mtd_rangeDT() {

        if (Data::isValid($_GET, 'date_range') && Data::isValid($_GET, 'employer_id')) {
            $date_range = $_GET['date_range'];
            $employer_id = $_GET['employer_id'];
//            Log::info($date_range);
            $dates = explode(' - ', $date_range);
            $from_date = date('Y/m/d 00:00:00', strtotime($dates[0]));
            $to_date = date('Y/m/d 23:59:59', strtotime($dates[1]));

            $employer = Employer::find($employer_id);

            $loanProducts = EmployerLoanProduct::select('loan_product_id')
                ->where('employer_id',$employer_id)
                ->pluck('loan_product_id');


            if ($employer->salary_advance == true && $employer->invoice_discounting == false){
                $loanRequests = Loan::select(
                    DB::raw('COUNT(id) as loans_requested'),
                    DB::raw('SUM(amount_requested) as sum_requested'),
                    DB::raw("DATE(created_at) as day"))
                    ->whereIn('loan_product_id',$loanProducts)
                    ->where('approval_status','APPROVED')
                    ->whereDate('created_at','>=',$from_date)
                    ->whereDate('created_at','<=',$to_date)
                    ->groupBy('day')
                    ->orderBy('day', 'desc');

            }elseif ($employer->salary_advance == false && $employer->invoice_discounting == true){

                $loanRequests = InvoiceDiscount::select(
                        DB::raw('COUNT(id) as loans_requested'),
                        DB::raw('SUM(approved_amount) as sum_requested'),
                        DB::raw("DATE(created_at) as day"))
                    ->where('employer_id',$employer_id)
                    ->where('offer_status','ACCEPTED')
                    ->whereDate('created_at','>=',$from_date)
                    ->whereDate('created_at','<=',$to_date)
                    ->groupBy('day')
                    ->orderBy('day', 'desc');

            }else{

                $idfLoanIds = InvoiceDiscount::where('employer_id', $employer_id)->pluck('loan_request_id');

                $loanRequests = Loan::select(
                        DB::raw('COUNT(id) as loans_requested'),
                        DB::raw('SUM(amount_requested) as sum_requested'),
                        DB::raw("DATE(created_at) as day"))
                    ->whereIn('loan_product_id',$loanProducts)
                    ->whereIn('id',$idfLoanIds)
                    ->where('approval_status','APPROVED')
                    ->whereDate('created_at','>=',$from_date)
                    ->whereDate('created_at','<=',$to_date)
                    ->groupBy('day')
                    ->orderBy('day', 'desc');
            }

            Log::info("loan requests::".json_encode($loanRequests));

        }else{
            $employer = Employer::first();

            $loanProducts = EmployerLoanProduct::select('loan_product_id')
                ->where('employer_id',$employer->id)
                ->pluck('loan_product_id');

            if ($employer->salary_advance == true && $employer->invoice_discounting == false){
                $loanRequests = Loan::select(
                    DB::raw('COUNT(id) as loans_requested'),
                    DB::raw('SUM(amount_requested) as sum_requested'),
                    DB::raw("DATE(created_at) as day"))
                    ->whereIn('loan_product_id',$loanProducts)
                    ->where('approval_status','APPROVED')
                    ->whereDate('created_at',Carbon::now())
                    ->groupBy('day')
                    ->orderBy('day', 'desc');

            }elseif ($employer->salary_advance == false && $employer->invoice_discounting == true){

                $loanRequests = InvoiceDiscount::select(
                    DB::raw('COUNT(id) as loans_requested'),
                    DB::raw('SUM(approved_amount) as sum_requested'),
                    DB::raw("DATE(created_at) as day"))
                    ->where('employer_id',$employer->id)
                    ->where('offer_status','ACCEPTED')
                    ->whereDate('created_at',Carbon::now())
                    ->groupBy('day')
                    ->orderBy('day', 'desc');

            }else{

                $idfLoanIds = InvoiceDiscount::where('employer_id', $employer->id)->pluck('loan_request_id');

                $loanRequests = Loan::select(
                        DB::raw('COUNT(id) as loans_requested'),
                        DB::raw('SUM(amount_requested) as sum_requested'),
                        DB::raw("DATE(created_at) as day"))
                    ->whereIn('loan_product_id',$loanProducts)
                    ->where('approval_status','APPROVED')
                    ->whereDate('created_at',Carbon::now())
                    ->whereIn('id',$idfLoanIds)
                    ->groupBy('day')
                    ->orderBy('day', 'desc');

            }

//            $loanRequests = LoanRequest::select(
//                    DB::raw('COUNT(id) as loans_requested'),
//                    DB::raw('SUM(amount_requested) as sum_requested'),
//                    DB::raw("DATE(created_at) as day"))
//                ->whereIn('loan_product_id',$loanProducts)
//                ->where('approval_status','APPROVED')
//                ->whereDate('created_at',Carbon::today())
//                ->groupBy('day')
//                ->orderBy('day', 'desc');

            Log::info("loan requests::".json_encode($loanRequests));

        }




        return DataTables::of($loanRequests)


            ->editColumn('loans_requested', function ($loanRequests) {
                return number_format($loanRequests->loans_requested);
            })

            ->editColumn('sum_requested', function ($loanRequests) {
                return number_format($loanRequests->sum_requested);
            })

            ->editColumn('day', function ($loanRequests) {
                return Carbon::parse($loanRequests->day)->isoFormat('MMM Do YYYY');
            })

            ->make(true);

    }

    public function ageing(){
        return view ('reports.ageing');
    }

    public function ageingDT() {

        if (Data::isValid($_GET, 'age')) {
            $age = $_GET['age'];
            $lpId = $_GET['loan_product_id'];

//            $employer = Employer::find($employer_id);

//            $loanProducts = EmployerLoanProduct::select('loan_product_id')
//                ->where('employer_id',$employer_id)
//                ->pluck('loan_product_id');
//
//            $userIds = Employee::select('user_id')
//                ->where('employer_id',$employer_id)
//                ->pluck('user_id');
//
            $loanRequests=LoanRequest::select('id')
                ->where('loan_product_id',$lpId)
                ->pluck('id');

            switch ($age){
                case 1: //0-15 days
                    $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
                        ->whereDate('payment_date', '>=', Carbon::now()->subDays(15))
                        ->whereDate('payment_date', '<=', Carbon::today())
                        ->whereIn('loan_request_id',$loanRequests)
                        ->get();
                    break;
                case 2: //15-30 days
                    $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
                        ->whereDate('payment_date', '>=', Carbon::now()->subDays(30))
                        ->whereDate('payment_date', '<', Carbon::now()->subDays(15))
                        ->whereIn('loan_request_id',$loanRequests)
                        ->get();
                    break;
                case 3: //30-60 days
                    $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
                        ->whereDate('payment_date', '>=', Carbon::now()->subDays(60))
                        ->whereDate('payment_date', '<', Carbon::now()->subDays(30))
                        ->whereIn('loan_request_id',$loanRequests)
                        ->get();
                    break;
                case 4: //60-90 days
                    $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
                        ->whereDate('payment_date', '>=', Carbon::now()->subDays(90))
                        ->whereDate('payment_date', '<', Carbon::now()->subDays(60))
                        ->whereIn('loan_request_id',$loanRequests)
                        ->get();
                    break;
                case 5: //over 90 days
                    $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
                        ->whereDate('payment_date', '<', Carbon::now()->subDays(90))
                        ->whereIn('loan_request_id',$loanRequests)
                        ->get();
                    break;

            }


//            if ($employer->salary_advance == true && $employer->invoice_discounting == false){
//
//                $loanRequests=LoanRequest::select('id')
//                    ->whereIn('loan_product_id',$loanProducts)
//                    ->whereIn('user_id',$userIds)
//                    ->pluck('id');
//
//                switch ($age){
//                    case 1: //0-15 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '>=', Carbon::now()->subDays(15))
//                            ->whereDate('payment_date', '<=', Carbon::today())
//                            ->whereIn('loan_request_id',$loanRequests)
//                            ->get();
//                        break;
//                    case 2: //15-30 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '>=', Carbon::now()->subDays(30))
//                            ->whereDate('payment_date', '<', Carbon::now()->subDays(15))
//                            ->whereIn('loan_request_id',$loanRequests)
//                            ->get();
//                        break;
//                    case 3: //30-60 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '>=', Carbon::now()->subDays(60))
//                            ->whereDate('payment_date', '<', Carbon::now()->subDays(30))
//                            ->whereIn('loan_request_id',$loanRequests)
//                            ->get();
//                        break;
//                    case 4: //60-90 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '>=', Carbon::now()->subDays(90))
//                            ->whereDate('payment_date', '<', Carbon::now()->subDays(60))
//                            ->whereIn('loan_request_id',$loanRequests)
//                            ->get();
//                        break;
//                    case 5: //over 90 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '<', Carbon::now()->subDays(90))
//                            ->whereIn('loan_request_id',$loanRequests)
//                            ->get();
//                        break;
//
//                }
//
//
//            }
//            elseif ($employer->salary_advance == false && $employer->invoice_discounting == true){
//
//                $userIds = InvoiceDiscount::where('employer_id', $employer_id)
//                    ->where('offer_status','ACCEPTED')
//                    ->pluck('created_by');
//
//                $lpIds = InvoiceDiscount::where('employer_id', $employer_id)
//                    ->where('offer_status','ACCEPTED')
//                    ->pluck('loan_product_id');
//
//                $loanRequests = LoanRequest::select('id')
//                    ->whereIn('loan_product_id',$lpIds)
//                    ->whereIn('user_id',$userIds)
//                    ->pluck('id');
//
//                switch ($age){
//                    case 1: //0-15 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '>=', Carbon::now()->subDays(15))
//                            ->whereDate('payment_date', '<=', Carbon::today())
//                            ->whereIn('loan_request_id',$loanRequests)
//                            ->get();
//                        break;
//                    case 2: //15-30 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '>=', Carbon::now()->subDays(30))
//                            ->whereDate('payment_date', '<', Carbon::now()->subDays(15))
//                            ->whereIn('loan_request_id',$loanRequests)
//                            ->get();
//                        break;
//                    case 3: //30-60 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '>=', Carbon::now()->subDays(60))
//                            ->whereDate('payment_date', '<', Carbon::now()->subDays(30))
//                            ->whereIn('loan_request_id',$loanRequests)
//                            ->get();
//                        break;
//                    case 4: //60-90 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '>=', Carbon::now()->subDays(90))
//                            ->whereDate('payment_date', '<', Carbon::now()->subDays(60))
//                            ->whereIn('loan_request_id',$loanRequests)
//                            ->get();
//                        break;
//                    case 5: //over 90 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '<', Carbon::now()->subDays(90))
//                            ->whereIn('loan_request_id',$loanRequests)
//                            ->get();
//                        break;
//                }
//
//            }
//            elseif ($employer->salary_advance == true && $employer->invoice_discounting == true){
//
//
//
//
//                $loanRequests1=LoanRequest::select('id')
//                    ->whereIn('loan_product_id',$loanProducts)
//                    ->whereIn('user_id',$userIds)
//                    ->pluck('id');
//
//
//                $userIds2 = InvoiceDiscount::where('employer_id', $employer_id)
//                    ->where('offer_status','ACCEPTED')
//                    ->pluck('created_by');
//
//                $lpIds = InvoiceDiscount::where('employer_id', $employer_id)
//                    ->where('offer_status','ACCEPTED')
//                    ->pluck('loan_product_id');
//
//                $loanRequests2 = LoanRequest::select('id')
//                    ->whereIn('loan_product_id',$lpIds)
//                    ->whereIn('user_id',$userIds2)
//                    ->pluck('id');
//
//                switch ($age){
//                    case 1: //0-15 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '>=', Carbon::now()->subDays(15))
//                            ->whereDate('payment_date', '<=', Carbon::today())
//                            ->whereIn('loan_request_id',$loanRequests1)
//                            ->whereIn('loan_request_id',$loanRequests2)
//                            ->get();
//                        break;
//                    case 2: //15-30 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '>=', Carbon::now()->subDays(30))
//                            ->whereDate('payment_date', '<', Carbon::now()->subDays(15))
//                            ->whereIn('loan_request_id',$loanRequests1)
//                            ->whereIn('loan_request_id',$loanRequests2)
//                            ->get();
//                        break;
//                    case 3: //30-60 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '>=', Carbon::now()->subDays(60))
//                            ->whereDate('payment_date', '<', Carbon::now()->subDays(30))
//                            ->whereIn('loan_request_id',$loanRequests1)
//                            ->whereIn('loan_request_id',$loanRequests2)
//                            ->get();
//                        break;
//                    case 4: //60-90 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '>=', Carbon::now()->subDays(90))
//                            ->whereDate('payment_date', '<', Carbon::now()->subDays(60))
//                            ->whereIn('loan_request_id',$loanRequests1)
//                            ->whereIn('loan_request_id',$loanRequests2)
//                            ->get();
//                        break;
//                    case 5: //over 90 days
//                        $loanSchedules = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
//                            ->whereDate('payment_date', '<', Carbon::now()->subDays(90))
//                            ->whereIn('loan_request_id',$loanRequests1)
//                            ->whereIn('loan_request_id',$loanRequests2)
//                            ->get();
//                        break;
//                }
//
//
//            }




        }else{
            $employer = Employer::first();

            $loanSchedules = LoanSchedule::whereIn('loan_request_id',[0])->get();
        }




        return DataTables::of($loanSchedules)


            ->editColumn('name', function ($loanSchedules) {
                return optional(optional($loanSchedules->loan)->user)->name. ' '. optional(optional($loanSchedules->loan)->user)->surname ;
            })

            ->editColumn('loan_amount', function ($loanSchedules) {
                return number_format(optional($loanSchedules->loan)->amount_requested);
            })

            ->editColumn('approved_date', function ($loanSchedules) {
                return Carbon::parse(optional($loanSchedules->loan)->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('payroll_no', function ($loanSchedules) {

                $employee = Employee::where('user_id',optional($loanSchedules->loan)->user_id)
                    ->first();

                return is_null($employee) ? "" : $employee->payroll_no;
            })

            ->editColumn('id_no', function ($loanSchedules) {
                return optional(optional($loanSchedules->loan)->user)->id_no;
            })

            ->editColumn('phone_no', function ($loanSchedules) {
                return optional(optional($loanSchedules->loan)->user)->phone_no;
            })

            ->editColumn('opening_balance', function ($loanSchedules) {
                return number_format($loanSchedules->beginning_balance,2);
            })

            ->editColumn('installment', function ($loanSchedules) {
                return number_format($loanSchedules->scheduled_payment,2);
            })

            ->editColumn('date_paid', function ($loanSchedules) {
                return Carbon::parse($loanSchedules->payment_date)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('status', function ($loanSchedules) {
                return  $loanSchedules->status;
            })

            ->editColumn('arrears', function ($loanSchedules) {
                return  number_format($loanSchedules->scheduled_payment - $loanSchedules->actual_payment_done ,2);
            })

            ->editColumn('closing_loan_balance', function ($loanSchedules) {
                return number_format($loanSchedules->ending_balance);
            })

            ->make(true);

    }



}
