<?php

namespace App\Http\Controllers;

use App\AuditTrail;
use App\BulkSms;
use App\CustomerProfile;
use App\EmployerLoanProduct;
use App\Exports\AllLoans;
use App\Exports\LoanRequests;
use App\Exports\LoansApproved;
use App\Exports\LoansApproveToday;
use App\Exports\LoansDueToday;
use App\Exports\LoansOverdue;
use App\Exports\LoansRejected;
use App\Exports\RepaidLoans;
use App\Exports\RepaidLoansToday;
use App\InterestRateMatrix;
use App\Jobs\SendSms;
use App\LoanFee;
use App\LoanProduct;
use App\LoanRepayment;
use App\LoanRequest;
use App\LoanSchedule;
use App\Notifications\LoanApproved;
use App\Notifications\LoanRejectd;
use App\Notifications\UserCreated;
use App\User;
use App\UserGroup;
use App\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class LoansController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function products() {
        return view('loans.products');
    }

    public function loanProductsDT() {
        $loanProducts = LoanProduct::all();
        return DataTables::of($loanProducts)

            ->editColumn('created_at', function ($loanProducts) {
                return Carbon::parse($loanProducts->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('interest_rate', function ($loanProducts) {
                return $loanProducts->interest_rate.' %';
            })

            ->editColumn('max_period_months', function ($loanProducts) {
                return $loanProducts->max_period_months.' MONTHS';
            })
            ->addColumn('actions', function($loanProducts){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . route('loan-product-details' ,  $loanProducts->id) . '"
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
            ->rawColumns(['actions'])

            ->make(true);

    }

    public  function create_loan_product(Request  $request){
        $data = request()->validate([
            'name'  => 'required',
            'interest_rate'  => 'required',
            'min_amount'  => 'required',
            'max_amount'  => 'required',
            'max_period_months'  => 'required',
            'fee_application'  => 'required',
            'description'  => 'required',
        ]);

        $loanProduct = new LoanProduct();
        DB::transaction(function() use ($request,$loanProduct) {

            $loanProduct->name = $request->name;
            $loanProduct->interest_rate = $request->interest_rate;
            $loanProduct->max_period_months = $request->max_period_months;
            $loanProduct->fee_application = $request->fee_application;
            $loanProduct->description = $request->description;
            $loanProduct->min_amount = $request->min_amount;
            $loanProduct->max_amount = $request->max_amount;
            $loanProduct->saveOrFail();

            $periods = ['1_MONTH','2_MONTHS','3_5_MONTHS','6_12_MONTHS','12_PLUS_MONTHS'];

            foreach ($periods as $period){
                $interestMatrix = new InterestRateMatrix();
                $interestMatrix->loan_product_id = $loanProduct->id;
                $interestMatrix->loan_period = $period;
                $interestMatrix->new_client_interest = $request->interest_rate;
                $interestMatrix->existing_client_interest = $request->interest_rate;
                $interestMatrix->saveOrFail();
            }


            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Created loan product ('.$request->name.') with ID '.$loanProduct->id,
            ]);

            request()->session()->flash('success', 'Loan Product has been created successfully');
        });
        return redirect('products/loans/'.$loanProduct->id);
    }

    public function loan_product_details($id) {
        $loanProduct = LoanProduct::find($id);

        if (is_null($loanProduct))
            abort(404,"Loan Product does not exist");

        return view('loans.product_details')->with(['loanProduct'=>$loanProduct]);
    }

    public  function update_product_min_max(Request  $request){
        $data = request()->validate([
            'product_id'  => 'required|exists:loan_products,id',
            'min_amount'  => 'required',
            'max_amount'  => 'required',
        ]);


        DB::transaction(function() use ($request) {

            $product = LoanProduct::find($request->product_id);
            $product->min_amount = $request->min_amount;
            $product->max_amount = $request->max_amount;

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Updated min/max amounts for product: '.$product->name.' Min amount: '
                    .number_format($product->min_amount).', New Min Amount:'.number_format($request->min_amount).'. Max amount: '
                    .number_format($product->max_amount).'. New max amount: '.number_format($request->max_amount).' respectively',
            ]);

            $product->update();

            request()->session()->flash('success', 'Min/max amounts have been updated successfully');
        });
        return redirect()->back();
    }

    public  function update_product_closing_date(Request  $request){
        $data = request()->validate([
            'product_id'  => 'required|exists:loan_products,id',
            'closing_date'  => 'required|integer|min:1|max:31',
        ]);

        $product = LoanProduct::findOrFail($request->product_id);
        $product->closing_date = $request->closing_date;
        $product->update();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Updated closing date for product: '.$product->name.' to: '.$request->closing_date,
        ]);

        request()->session()->flash('success', 'Product closing date has been updated successfully');

        return redirect()->back();
    }

    public  function update_product_period(Request  $request){
        $data = request()->validate([
            'product_id'  => 'required|exists:loan_products,id',
            'period'  => 'required',
        ]);


        DB::transaction(function() use ($request) {

            $product = LoanProduct::find($request->product_id);

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Updated max loan period for product: '.$product->name.' Max Period: '
                    .number_format($product->max_period_months).', New Max period:'.number_format($request->period).' respectively',
            ]);
            $product->max_period_months = $request->period;
            $product->update();

            request()->session()->flash('success', 'Maximum loan period has been updated successfully');
        });
        return redirect()->back();
    }

    public  function create_loan_product_fee(Request  $request){
        $data = request()->validate([
            'loan_product_id'  => 'required',
            'name'  => 'required',
            'amount'  => 'required',
            'amount_type'  => 'required',
            'frequency'  => 'required',
        ]);


        DB::transaction(function() use ($request) {

            $loanFee = new LoanFee();
            $loanFee->loan_product_id = $request->loan_product_id;
            $loanFee->name = $request->name;
            $loanFee->amount = $request->amount;
            $loanFee->amount_type = $request->amount_type;
            $loanFee->frequency = $request->frequency;
            $loanFee->saveOrFail();


            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Created loan product FEE ('.$request->name.') with ID '.$loanFee->id,
            ]);

            request()->session()->flash('success', 'Product fee has been created successfully');
        });
        return redirect()->back();
    }

    public  function update_interest_matrix(Request  $request){
        $data = request()->validate([
            'interest_rate_matrix_id'  => 'required|exists:interest_rate_matrices,id',
            'new_client_interest'  => 'required',
            'existing_client_interest'  => 'required',
        ]);


        DB::transaction(function() use ($request) {

            $matrix = InterestRateMatrix::find($request->interest_rate_matrix_id);
            $matrix->new_client_interest = $request->new_client_interest;
            $matrix->existing_client_interest = $request->existing_client_interest;

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Updated interest matrix for'.$matrix->loan_period.' from New Client:'
                    .$matrix->new_client_interest.'%, Existing client:'.$matrix->existing_client_interest.'% to'
                    .$request->new_client_interest.'% and '.$request->existing_client_interest.'% respectively',
            ]);

            $matrix->update();

            request()->session()->flash('success', 'Interest matrix has been updated successfully');
        });
        return redirect()->back();
    }

    public function edit_matrix($id)
    {
        $matrix = InterestRateMatrix::find($id);
        return $matrix;
    }

    public  function delete_loan_product_fee(Request  $request){
        $this->validate($request, [
            'id' => 'required|exists:loan_fees,id',
        ]);


        $fee = LoanFee::find($request->id);
        $name = $fee->name;

        if ($fee->delete()){
            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Deleted loan product FEE ('.$name.') with ID '.$request->id,
            ]);
        }


        Session::flash("success", "Fee has been deleted");


        return redirect()->back();
    }

    public function all_loans() {
        return view('loans.loans')->with([
            'title'=>'All Loans',
            'ajax'=>'all-loans-dt',
            'export'=>'loans/all/export'
        ]);
    }
    public function exportAllLoans() {
        return Excel::download(new AllLoans(), 'all_loans.xlsx');
    }

//    public function new_loans() {
//        return view('loans.loans')->with([
//            'title'=>'New Loans',
//            'ajax'=>'new-loans-dt',
//            'export'=>'loans/requests/export'
//        ]);
//    }
//    public function exportNewLoans() {
//        return Excel::download(new LoanRequests(), 'new_loans.xlsx');
//    }

//    public function approved_loans() {
//        return view('loans.loans')->with([
//            'title'=>'Approved Loans',
//            'ajax'=>'approved-loans-dt',
//            'export'=>'loans/approved/export'
//        ]);
//    }
//    public function exportApprovedLoans() {
//        return Excel::download(new LoansApproved(), 'approved_loans.xlsx');
//    }

//    public function rejected_loans() {
//        return view('loans.loans')->with([
//            'title'=>'Rejected Loans',
//            'ajax'=>'rejected-loans-dt',
//            'export'=>'loans/rejected/export'
//        ]);
//    }
//    public function exportRejectedLoans() {
//        return Excel::download(new LoansRejected(), 'rejected_loans.xlsx');
//    }

    public function due_today_loans() {
        return view('loans.due_loans')->with([
            'title'=>'Loans Due Today',
            'ajax'=>'due-today-loans-dt',
            'export'=>'loans/due_today/export'
        ]);
    }
    public function exportDueTodayLoans() {
        return Excel::download(new LoansDueToday(), 'due_today_loans.xlsx');
    }


    public function repaid_loans() {
        return view('loans.repaid_loans')->with([
            'title'=>'All Repaid Loans',
            'ajax'=>'repaid-loans-dt',
            'export'=>'loans/repaid/export'
        ]);
    }
    public function exportRepaidLoans() {
        return Excel::download(new RepaidLoans(), 'repaid_loans.xlsx');
    }

    public function repaid_loans_today() {
        return view('loans.repaid_loans_today')->with([
            'title'=>'Repaid Loans Today',
            'ajax'=>'repaid-loans-today-dt',
            'export'=>'loans/repaid/today/export'
        ]);
    }
    public function exportTodayRepaidLoans() {
        return Excel::download(new RepaidLoansToday(), 'repaid_loans_today.xlsx');
    }


    public function approved_today_loans() {
        return view('loans.loans')->with([
            'title'=>'Loans Approved Today',
            'ajax'=>'approved-today-loans-dt',
            'export'=>'loans/approved_today/export'
        ]);
    }
    public function exportApprovedToday() {
        return Excel::download(new LoansApproveToday(), 'approved_today.xlsx');
    }


    public function overdue_loans() {
        return view('loans.due_loans')->with([
            'title'=>'Overdue Loans',
            'ajax'=>'overdue-loans-dt',
            'export'=>'loans/overdue/export'
        ]);
    }
    public function exportOverdue() {
        return Excel::download(new LoansOverdue(), 'overdue_loans.xlsx');
    }

    public function allLoansDT() {
        $loanRequests = LoanRequest::orderBy('id','desc')->get();

        return DataTables::of($loanRequests)
            ->addColumn('product', function ($loanRequests) {
                return optional($loanRequests->product)->name;
            })
            ->addColumn('name', function ($loanRequests) {
                return optional($loanRequests->user)->name.' '.optional($loanRequests->user)->surname;
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

//    public function newLoansDT() {
//        $loanRequests = LoanRequest::where('approval_status','PENDING')->orderBy('id','desc')->get();
//
//        return DataTables::of($loanRequests)
//            ->addColumn('product', function ($loanRequests) {
//                return optional($loanRequests->product)->name;
//            })
//
//            ->addColumn('name', function ($loanRequests) {
//                return optional($loanRequests->user)->name.' '.optional($loanRequests->user)->surname;
//            })
//
//            ->editColumn('amount_requested', function ($loanRequests) {
//                return optional(optional($loanRequests->user)->wallet)->currency.' '. number_format($loanRequests->amount_requested);
//            })
//            ->editColumn('period_in_months', function ($loanRequests) {
//                return $loanRequests->period_in_months.' Months';
//            })
//            ->editColumn('approval_status',function ($loanRequests) {
//                if ($loanRequests->approval_status == 'PENDING'){
//                    return '<span class="badge pill badge-info">'.$loanRequests->approval_status.'</span>';
//                }elseif ($loanRequests->approval_status == 'APPROVED'){
//                    return '<span class="badge pill badge-success">'.$loanRequests->approval_status.'</span>';
//                }elseif ($loanRequests->approval_status == 'REJECTED'){
//                    return '<span class="badge pill badge-danger">'.$loanRequests->approval_status.'</span>';
//                }else{
//                    return '<span class="badge pill badge-info">'.$loanRequests->approval_status.'</span>';
//                }
//            })
//            ->editColumn('repayment_status',function ($loanRequests) {
//                if ($loanRequests->repayment_status == 'PENDING'){
//                    return '<span class="badge pill badge-info">'.$loanRequests->repayment_status.'</span>';
//                }elseif ($loanRequests->repayment_status == 'PARTIALLY_PAID'){
//                    return '<span class="badge pill badge-primary">'.$loanRequests->repayment_status.'</span>';
//                }elseif ($loanRequests->repayment_status == 'PAID'){
//                    return '<span class="badge pill badge-success">'.$loanRequests->repayment_status.'</span>';
//                }elseif ($loanRequests->repayment_status == 'CANCELLED'){
//                    return '<span class="badge pill badge-warning">'.$loanRequests->repayment_status.'</span>';
//                }else{
//                    return '<span class="badge pill badge-info">'.$loanRequests->repayment_status.'</span>';
//                }
//
//            })
//            ->addColumn('actions', function($loanRequests){ // add custom column
//                $actions = '<div class="align-content-center">';
//
//                $actions .= '<a href="' . route('loan-details' ,  $loanRequests->id) . '"
//                    class="btn btn-primary btn-link btn-sm">
//                    <i class="material-icons">visibility</i> View</a>';
//
//
////                $actions .= '<a href="' . url('/users/details' ,  $user->id) . '"
////                    class="btn btn-info btn-link btn-sm" >
////                    <i class="material-icons">preview</i> View Merchant</a>';
////
////
////                if (auth()->user()->role->has_perm([8])) {
////                    $actions .= '<form action="'. route('delete-merchant',  $merchants->id) .'" style="display: inline;" method="post" class="del_merchant_form">';
////                    $actions .= method_field('DELETE');
////                    $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';
////                }
//
//                $actions .= '</div>';
//                return $actions;
//            })
//            ->rawColumns(['actions','repayment_status','approval_status'])
//            ->make(true);
//    }

//    public function approvedLoansDT() {
//        $loanRequests = LoanRequest::where('approval_status','APPROVED')->orderBy('id','desc')->get();
//
//        return DataTables::of($loanRequests)
//            ->addColumn('product', function ($loanRequests) {
//                return optional($loanRequests->product)->name;
//            })
//
//            ->addColumn('name', function ($loanRequests) {
//                return optional($loanRequests->user)->name.' '.optional($loanRequests->user)->surname;
//            })
//
//            ->editColumn('amount_requested', function ($loanRequests) {
//                return optional(optional($loanRequests->user)->wallet)->currency.' '. number_format($loanRequests->amount_requested);
//            })
//            ->editColumn('period_in_months', function ($loanRequests) {
//                return $loanRequests->period_in_months.' Months';
//            })
//            ->editColumn('approval_status',function ($loanRequests) {
//                if ($loanRequests->approval_status == 'PENDING'){
//                    return '<span class="badge pill badge-info">'.$loanRequests->approval_status.'</span>';
//                }elseif ($loanRequests->approval_status == 'APPROVED'){
//                    return '<span class="badge pill badge-success">'.$loanRequests->approval_status.'</span>';
//                }elseif ($loanRequests->approval_status == 'REJECTED'){
//                    return '<span class="badge pill badge-danger">'.$loanRequests->approval_status.'</span>';
//                }else{
//                    return '<span class="badge pill badge-info">'.$loanRequests->approval_status.'</span>';
//                }
//            })
//            ->editColumn('repayment_status',function ($loanRequests) {
//                if ($loanRequests->repayment_status == 'PENDING'){
//                    return '<span class="badge pill badge-info">'.$loanRequests->repayment_status.'</span>';
//                }elseif ($loanRequests->repayment_status == 'PARTIALLY_PAID'){
//                    return '<span class="badge pill badge-primary">'.$loanRequests->repayment_status.'</span>';
//                }elseif ($loanRequests->repayment_status == 'PAID'){
//                    return '<span class="badge pill badge-success">'.$loanRequests->repayment_status.'</span>';
//                }elseif ($loanRequests->repayment_status == 'CANCELLED'){
//                    return '<span class="badge pill badge-warning">'.$loanRequests->repayment_status.'</span>';
//                }else{
//                    return '<span class="badge pill badge-info">'.$loanRequests->repayment_status.'</span>';
//                }
//
//            })
//            ->addColumn('actions', function($loanRequests){ // add custom column
//                $actions = '<div class="align-content-center">';
//
//                $actions .= '<a href="' . route('loan-details' ,  $loanRequests->id) . '"
//                    class="btn btn-primary btn-link btn-sm">
//                    <i class="material-icons">visibility</i> View</a>';
//
//
////                $actions .= '<a href="' . url('/users/details' ,  $user->id) . '"
////                    class="btn btn-info btn-link btn-sm" >
////                    <i class="material-icons">preview</i> View Merchant</a>';
////
////
////                if (auth()->user()->role->has_perm([8])) {
////                    $actions .= '<form action="'. route('delete-merchant',  $merchants->id) .'" style="display: inline;" method="post" class="del_merchant_form">';
////                    $actions .= method_field('DELETE');
////                    $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';
////                }
//
//                $actions .= '</div>';
//                return $actions;
//            })
//            ->rawColumns(['actions','repayment_status','approval_status'])
//            ->make(true);
//    }

//    public function rejectedLoansDT() {
//        $loanRequests = LoanRequest::where('approval_status','REJECTED')->orderBy('id','desc')->get();
//
//        return DataTables::of($loanRequests)
//            ->addColumn('product', function ($loanRequests) {
//                return optional($loanRequests->product)->name;
//            })
//
//            ->addColumn('name', function ($loanRequests) {
//                return optional($loanRequests->user)->name.' '.optional($loanRequests->user)->surname;
//            })
//            ->editColumn('amount_requested', function ($loanRequests) {
//                return optional(optional($loanRequests->user)->wallet)->currency.' '. number_format($loanRequests->amount_requested);
//            })
//            ->editColumn('period_in_months', function ($loanRequests) {
//                return $loanRequests->period_in_months.' Months';
//            })
//            ->editColumn('approval_status',function ($loanRequests) {
//                if ($loanRequests->approval_status == 'PENDING'){
//                    return '<span class="badge pill badge-info">'.$loanRequests->approval_status.'</span>';
//                }elseif ($loanRequests->approval_status == 'APPROVED'){
//                    return '<span class="badge pill badge-success">'.$loanRequests->approval_status.'</span>';
//                }elseif ($loanRequests->approval_status == 'REJECTED'){
//                    return '<span class="badge pill badge-danger">'.$loanRequests->approval_status.'</span>';
//                }else{
//                    return '<span class="badge pill badge-info">'.$loanRequests->approval_status.'</span>';
//                }
//            })
//            ->editColumn('repayment_status',function ($loanRequests) {
//                if ($loanRequests->repayment_status == 'PENDING'){
//                    return '<span class="badge pill badge-info">'.$loanRequests->repayment_status.'</span>';
//                }elseif ($loanRequests->repayment_status == 'PARTIALLY_PAID'){
//                    return '<span class="badge pill badge-primary">'.$loanRequests->repayment_status.'</span>';
//                }elseif ($loanRequests->repayment_status == 'PAID'){
//                    return '<span class="badge pill badge-success">'.$loanRequests->repayment_status.'</span>';
//                }elseif ($loanRequests->repayment_status == 'CANCELLED'){
//                    return '<span class="badge pill badge-warning">'.$loanRequests->repayment_status.'</span>';
//                }else{
//                    return '<span class="badge pill badge-info">'.$loanRequests->repayment_status.'</span>';
//                }
//
//            })
//            ->addColumn('actions', function($loanRequests){ // add custom column
//                $actions = '<div class="align-content-center">';
//
//                $actions .= '<a href="' . route('loan-details' ,  $loanRequests->id) . '"
//                    class="btn btn-primary btn-link btn-sm">
//                    <i class="material-icons">visibility</i> View</a>';
//
//
////                $actions .= '<a href="' . url('/users/details' ,  $user->id) . '"
////                    class="btn btn-info btn-link btn-sm" >
////                    <i class="material-icons">preview</i> View Merchant</a>';
////
////
////                if (auth()->user()->role->has_perm([8])) {
////                    $actions .= '<form action="'. route('delete-merchant',  $merchants->id) .'" style="display: inline;" method="post" class="del_merchant_form">';
////                    $actions .= method_field('DELETE');
////                    $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';
////                }
//
//                $actions .= '</div>';
//                return $actions;
//            })
//            ->rawColumns(['actions','repayment_status','approval_status'])
//            ->make(true);
//    }

    public function dueTodayloansDT() {
        $loanSchedules = LoanSchedule::whereIn('status',['UNPAID','PARTIALLY_PAID'])
            ->whereDate('payment_date', Carbon::now())
            ->get();

        return DataTables::of($loanSchedules)
            ->addColumn('name', function ($loanSchedules) {
                return optional(optional($loanSchedules->loan)->user)->surname.' '.optional(optional($loanSchedules->loan)->user)->name;
            })

            ->addColumn('product', function ($loanSchedules) {
                return optional(optional($loanSchedules->loan)->product)->name;
            })
//            ->editColumn('amount_requested', function ($loanSchedules) {
//                return optional(optional(optional($loanSchedules->loan)->user)->wallet)->currency.' '. number_format(optional($loanSchedules->loan)->amount_requested);
//            })
//            ->editColumn('period_in_months', function ($loanSchedules) {
//                return optional($loanSchedules->loan)->period_in_months.' Months';
//            })

            ->editColumn('payment_date', function ($loanSchedules) {
                return $loanSchedules->payment_date;
            })

            ->editColumn('status',function ($loanSchedules) {
                if ($loanSchedules->status == 'UNPAID'){
                    return '<span class="badge pill badge-danger">'.$loanSchedules->status.'</span>';
                }elseif ($loanSchedules->status == 'PARTIALLY_PAID'){
                    return '<span class="badge pill badge-primary">'.$loanSchedules->status.'</span>';
                }else{
                    return '<span class="badge pill badge-info">'.$loanSchedules->status.'</span>';
                }

            })

            ->editColumn('amount_paid', function ($loanSchedules) {
                return number_format($loanSchedules->actual_payment_done);
            })

            ->editColumn('amount_due', function ($loanSchedules) {
                return number_format($loanSchedules->scheduled_payment - $loanSchedules->actual_payment_done,2);
            })

            ->addColumn('actions', function($loanSchedules){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . route('loan-details' ,  $loanSchedules->loan_request_id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View</a>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','status'])
            ->make(true);
    }

    public function repaidLoansDT() {
        $loanRequests = LoanRequest::where('approval_status','APPROVED')
            ->whereIn('repayment_status',['PARTIALLY_PAID','PAID'])
            ->orderBy('id','desc')
            ->get();

        return DataTables::of($loanRequests)
            ->addColumn('product', function ($loanRequests) {
                return optional($loanRequests->product)->name;
            })

            ->addColumn('name', function ($loanRequests) {
                return optional($loanRequests->user)->name.' '.optional($loanRequests->user)->surname;
            })

            ->editColumn('amount_requested', function ($loanRequests) {
                return optional(optional($loanRequests->user)->wallet)->currency.' '. number_format($loanRequests->amount_requested);
            })

            ->editColumn('amount_due', function ($loanRequests) {
                $paymentSchedules = LoanSchedule::where('loan_request_id',$loanRequests->id)->get();

                $due = 0;
                //$paid = 0;

                foreach ($paymentSchedules as $paymentSchedule){
                   // $paid += $paymentSchedule->actual_payment_done;
                    $due += $paymentSchedule->scheduled_payment;
                }

               // $loanBalance = $due-$paid;

                return "KES ".number_format($due);
            })

            ->editColumn('amount_paid', function ($loanRequests) {
                $paymentSchedules = LoanSchedule::where('loan_request_id',$loanRequests->id)->get();

                //$due = 0;
                $paid = 0;

                foreach ($paymentSchedules as $paymentSchedule){
                    $paid += $paymentSchedule->actual_payment_done;
                    //$due += $paymentSchedule->scheduled_payment;
                }

               // $loanBalance = $due-$paid;

                return "KES ".number_format($paid);
            })
            ->editColumn('period_in_months', function ($loanRequests) {
                return $loanRequests->period_in_months.' Months';
            })
            ->editColumn('balance',function ($loanRequests) {

                $paymentSchedules = LoanSchedule::where('loan_request_id',$loanRequests->id)->get();

                $due = 0;
                $paid = 0;

                foreach ($paymentSchedules as $paymentSchedule){
                    $paid += $paymentSchedule->actual_payment_done;
                    $due += $paymentSchedule->scheduled_payment;
                }

                $loanBalance = $due-$paid;

                return "KES ".number_format($loanBalance);
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

    public function repaidLoansTodayDT() {
        $loanRepayments = LoanRepayment::whereDate('created_at',Carbon::today())
            ->orderBy('id','desc')
            ->get();

        return DataTables::of($loanRepayments)
            ->addColumn('product', function ($loanRepayments) {
                return optional(optional($loanRepayments->loan_request)->product)->name;
            })

            ->addColumn('name', function ($loanRepayments) {
                return optional(optional($loanRepayments->loan_request)->user)->name.' '.optional(optional($loanRepayments->loan_request)->user)->surname;
            })

            ->editColumn('amount_repaid', function ($loanRepayments) {
                return optional((optional($loanRepayments->loan_request)->user)->wallet)->currency.' '. number_format($loanRepayments->amount_repaid);
            })

            ->editColumn('outstanding_balance', function ($loanRepayments) {
                return optional((optional($loanRepayments->loan_request)->user)->wallet)->currency.' '. number_format($loanRepayments->outstanding_balance);
            })

            ->addColumn('actions', function($loanRepayments){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . route('loan-details' ,  $loanRepayments->loan_request_id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View Loan</a>';


                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function approvedTodayloansDT() {
        $loanRequests = LoanRequest::whereDate('created_at',Carbon::now())->get();

        return DataTables::of($loanRequests)
            ->addColumn('product', function ($loanRequests) {
                return optional($loanRequests->product)->name;
            })

            ->addColumn('name', function ($loanRequests) {
                return optional($loanRequests->user)->name.' '.optional($loanRequests->user)->surname;
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

    public function overdueLoansDT() {
        $loanSchedules = LoanSchedule::whereIn('status',['UNPAID','PARTIALLY_PAID'])
            ->whereDate('payment_date','<', Carbon::now())
            ->get();

        return DataTables::of($loanSchedules)
            ->addColumn('name', function ($loanSchedules) {
                return optional(optional($loanSchedules->loan)->user)->surname.' '.optional(optional($loanSchedules->loan)->user)->name;
            })

            ->addColumn('product', function ($loanSchedules) {
                return optional(optional($loanSchedules->loan)->product)->name;
            })
//            ->editColumn('amount_requested', function ($loanSchedules) {
//                return optional(optional(optional($loanSchedules->loan)->user)->wallet)->currency.' '. number_format(optional($loanSchedules->loan)->amount_requested);
//            })
//            ->editColumn('period_in_months', function ($loanSchedules) {
//                return optional($loanSchedules->loan)->period_in_months.' Months';
//            })

            ->editColumn('payment_date', function ($loanSchedules) {
                return $loanSchedules->payment_date;
            })

            ->editColumn('status',function ($loanSchedules) {
                if ($loanSchedules->status == 'UNPAID'){
                    return '<span class="badge pill badge-danger">'.$loanSchedules->status.'</span>';
                }elseif ($loanSchedules->status == 'PARTIALLY_PAID'){
                    return '<span class="badge pill badge-primary">'.$loanSchedules->status.'</span>';
                }else{
                    return '<span class="badge pill badge-info">'.$loanSchedules->status.'</span>';
                }

            })

            ->editColumn('amount_paid', function ($loanSchedules) {
                return number_format($loanSchedules->actual_payment_done);
            })

            ->editColumn('amount_due', function ($loanSchedules) {
                return number_format($loanSchedules->scheduled_payment - $loanSchedules->actual_payment_done,2);
            })

            ->addColumn('actions', function($loanSchedules){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . route('loan-details' ,  $loanSchedules->loan_request_id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View</a>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','status'])
            ->make(true);
    }

    public  function loan_details($id)
    {
        $loanRequest = LoanRequest::find($id);

        if (is_null($loanRequest))
            abort(404);


        $paymentSchedules = LoanSchedule::where('loan_request_id',$id)->get();

        $due = 0;
        $paid = 0;

        foreach ($paymentSchedules as $paymentSchedule){
            $paid += $paymentSchedule->actual_payment_done;
            $due += $paymentSchedule->scheduled_payment;
        }

        $loanBalance = $due-$paid;

        return view('loans.loan_details')->with([
            'loan' => $loanRequest,
            'loanBalance' => $loanBalance,
        ]);
    }

    public function wallet_loan_repay(Request $request){
        $data = request()->validate([
            'loan_id'  => 'required',
            'amount'  => 'required|integer',
        ]);


        if ($request->amount <= 0){
            Session::flash("warning", "Please enter amounts greater than zero");
            return redirect()->back();
        }


        $loan = LoanRequest::findOrFail($request->loan_id);

        if (is_null($loan)){
            Session::flash("warning", "The requested loan could not be found. Please try again");
            return redirect()->back();
        }



        //check loan approval status
        if ($loan->approval_status == "PENDING" || $loan->approval_status == "REJECTED"){
            Session::flash("warning", 'The loan approval status is '.$loan->approval_status.'. Payment not allowed');
            return redirect()->back();
        }

        //check loan repayment status
        if ($loan->repayment_status == "PAID" || $loan->repayment_status == "CANCELLED"){
            Session::flash("warning", 'The loan repayment status is '.$loan->repayment_status.'. Payment not allowed');
            return redirect()->back();
        }


        //check wallet balance
        $wallet = $loan->user->wallet;

        if ($wallet->current_balance < $request->amount){
            Session::flash("warning", 'Insufficient balance. There is not enough balance in the user wallet to make this payment.');
            return redirect()->back();
        }

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
            $loanRepayment->description = 'Admin initiated payment from wallet';
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


            //create audit trail
            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Admin loan repayment of Ksh '.$amountPaid.' for loan with ID '.$request->loan_id,
            ]);

        }


        Session::flash("success", "Manual payment of Ksh. ".number_format($amountPaid,2)." has been made successfully.");
        return redirect()->back();

    }

    public function loanRepaymentsDT($id) {
        $repayments = LoanRepayment::where('loan_request_id',$id)->orderBy('id','desc')->get();

        return DataTables::of($repayments)
            ->editColumn('outstanding_balance', function ($repayments) {
                return number_format($repayments->outstanding_balance,2);
            })
            ->editColumn('amount_repaid', function ($repayments) {
                return number_format($repayments->amount_repaid,2);
            })

            ->editColumn('created_at', function ($repayments) {
                return Carbon::parse($repayments->created_at)->isoFormat('MMMM Do YYYY');
            })
            ->make(true);
    }

    public function loanScheduleDT($id) {
        $schedule = LoanSchedule::where('loan_request_id',$id)->orderBy('id','desc')->get();

        return DataTables::of($schedule)
            ->editColumn('payment_date', function ($schedule) {
                return Carbon::parse($schedule->payment_date)->isoFormat('MMM Do YYYY');
            })
            ->editColumn('beginning_balance', function ($schedule) {
                return number_format($schedule->beginning_balance);
            })
            ->editColumn('scheduled_payment', function ($schedule) {
                return number_format($schedule->scheduled_payment);
            })
            ->editColumn('interest_paid', function ($schedule) {
                return number_format($schedule->interest_paid);
            })
            ->editColumn('principal_paid', function ($schedule) {
                return number_format($schedule->principal_paid);
            })
            ->editColumn('ending_balance', function ($schedule) {
                return number_format($schedule->ending_balance);
            })
            ->make(true);
    }

    public  function reject_loan(Request  $request){
        $data = request()->validate([
            'reject_reason'  => 'required',
            'loan_id'  => 'required',
        ]);

        $loanRequest = LoanRequest::find($request->loan_id);

        if (is_null($loanRequest))
            abort(404);


        DB::transaction(function() use ($request, $loanRequest) {

            $loanRequest->reject_reason=$request->reject_reason;
            $loanRequest->approval_status="REJECTED";
            $loanRequest->repayment_status="CANCELLED";
            $loanRequest->update();

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Rejected loan request #'.$request->loan_id.' with reason ==> '.$request->reject_reason,
            ]);

            request()->session()->flash('success', 'Loan request has been rejected');

            send_sms($loanRequest->user->phone_no, "Your loan request was rejected. Check your email for more details");

            $loanRequest->user->notify(new LoanRejectd($request->reject_reason, $loanRequest->amount_requested, optional($loanRequest->product)->name));

        });
        return redirect()->back();
    }

    public  function approve_loan(Request  $request){
        $data = request()->validate([
            'loan_id'  => 'required',
        ]);

        $loanRequest = LoanRequest::find($request->loan_id);

        if (is_null($loanRequest))
            abort(404);


        DB::transaction(function() use ($request, $loanRequest) {

            $loanRequest->approved_date=Carbon::now();
            $loanRequest->approval_status="APPROVED";
            $loanRequest->repayment_status="PENDING";
            $loanRequest->update();

            //move to wallet

            $wallet = $loanRequest->user->wallet;

            $prevBal = $wallet->current_balance;
            $newBal = $prevBal+$loanRequest->amount_disbursable;

            $wallet->current_balance = $newBal;
            $wallet->previous_balance = $prevBal;
            $wallet->update();

            $receipt = $this->randomID();

            //save to wallet transactions
            $walletTransaction = new WalletTransaction();
            $walletTransaction->wallet_id = $wallet->id;
            $walletTransaction->amount = $loanRequest->amount_disbursable;
            $walletTransaction->previous_balance = $prevBal;
            $walletTransaction->transaction_type = 'CR';
            $walletTransaction->source = 'Loan Approval';
            $walletTransaction->trx_id = $receipt;
            $walletTransaction->narration = $loanRequest->product->name ." approved";
            $walletTransaction->saveOrFail();


            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Approved loan request #'.$request->loan_id. ' for customer '.optional($loanRequest->user)->name,
            ]);

            request()->session()->flash('success', 'Loan request has been approved');

            send_sms($loanRequest->user->phone_no, "Your loan request was approved. KES ".number_format($loanRequest->amount_disbursable).' has been deposited to your Quicksava Credit wallet.');

            $outstanding_amount = $wallet->current_balance;
            $loanRequest->user->notify(new LoanApproved($loanRequest->amount_requested,$loanRequest->amount_disbursable, optional($loanRequest->product)->name, $outstanding_amount));

        });
        return redirect()->back();
    }


    public  function add_org_loan_product(Request  $request){
        $data = request()->validate([
            'organisation_id'  => 'required',
            'loan_product_id'  => 'required',
        ]);

        $orgProduct = EmployerLoanProduct::where('employer_id',$request->organisation_id)
            ->where('loan_product_id', $request->loan_product_id)
            ->first();

        if (!is_null($orgProduct)){
            request()->session()->flash('warning', 'This loan product is already assigned to this organisation');
            return redirect()->back();
        }

        $orgProduct = new EmployerLoanProduct();
        $orgProduct->employer_id = $request->organisation_id;
        $orgProduct->loan_product_id = $request->loan_product_id;
        $orgProduct->saveOrFail();

        request()->session()->flash('success', 'Loan product has been assigned to organisation successfully');

        return redirect()->back();
    }

    public  function delete_org_loan_product(Request  $request){
        $this->validate($request, [
            'id' => 'required|exists:employer_loan_products,id',
        ]);


        $ep = EmployerLoanProduct::find($request->id);
        $productName = optional($ep->loan_product)->name;
        $employerName = optional($ep->employer)->business_name;

        if ($ep->delete()){
            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Deleted loan product ('.$productName.') from employer ('.$employerName.')',
            ]);
        }


        Session::flash("success", "Product has been deleted from organisation");


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
