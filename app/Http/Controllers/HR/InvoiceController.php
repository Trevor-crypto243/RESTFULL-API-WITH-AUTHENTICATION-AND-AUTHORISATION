<?php

namespace App\Http\Controllers\HR;

use App\Employee;
use App\HrManager;
use App\Http\Controllers\Controller;
use App\AdvanceApplication;
use App\Invoice;
use App\InvoiceDiscount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    public function invoices() {
        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (is_null($hr))
            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");

        return view('hr.invoices')->with(['title'=>'Invoice Discounts', 'ajax'=>'manager-invoices-dt']);
    }
    public function invoicesDT() {

        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (!is_null($hr))
            $employerId = $hr->employer_id;
        else
            $employerId = 0;


        $invoiceDiscounts = InvoiceDiscount::where('employer_id',$employerId)->get();

        return DataTables::of($invoiceDiscounts)

            ->editColumn('company', function ($invoiceDiscounts) {
                return optional($invoiceDiscounts->company)->business_name;
            })

            ->editColumn('invoices', function ($invoiceDiscounts) {
                return $invoiceDiscounts->invoices->count();
            })


            ->editColumn('total_amount', function ($invoiceDiscounts) {
                return 'KES '. number_format($invoiceDiscounts->invoices->sum('invoice_amount'));
            })

            ->editColumn('approved_amount', function ($invoiceDiscounts) {
                return 'KES '. number_format($invoiceDiscounts->approved_amount);
            })

            ->editColumn('expected_payment_date', function ($invoiceDiscounts) {
                return Carbon::parse($invoiceDiscounts->expected_payment_date)->isoFormat('MMM Do YYYY');
            })

            ->editColumn('approval_status',function ($invoiceDiscounts) {
                if ($invoiceDiscounts->approval_status == 'PENDING'){
                    return '<span class="badge pill badge-info">'.$invoiceDiscounts->approval_status.'</span>';
                }elseif ($invoiceDiscounts->approval_status == 'APPROVED'){
                    return '<span class="badge pill badge-success">'.$invoiceDiscounts->approval_status.'</span>';
                }elseif ($invoiceDiscounts->approval_status == 'REJECTED'){
                    return '<span class="badge pill badge-danger">'.$invoiceDiscounts->approval_status.'</span>';
                }
            })

            ->editColumn('offer_status',function ($invoiceDiscounts) {
                if ($invoiceDiscounts->offer_status == 'PENDING'){
                    return '<span class="badge pill badge-info">'.$invoiceDiscounts->offer_status.'</span>';
                }elseif ($invoiceDiscounts->offer_status == 'ACCEPTED'){
                    return '<span class="badge pill badge-success">'.$invoiceDiscounts->offer_status.'</span>';
                }elseif ($invoiceDiscounts->offer_status == 'REJECTED'){
                    return '<span class="badge pill badge-danger">'.$invoiceDiscounts->offer_status.'</span>';
                }elseif ($invoiceDiscounts->offer_status == 'AVAILABLE'){
                    return '<span class="badge pill badge-warning">'.$invoiceDiscounts->offer_status.'</span>';
                }
            })

            ->editColumn('payment_status',function ($invoiceDiscounts) {
                if ($invoiceDiscounts->payment_status == 'PENDING'){
                    return '<span class="badge pill badge-info">'.$invoiceDiscounts->payment_status.'</span>';
                }elseif ($invoiceDiscounts->payment_status == 'PAID'){
                    return '<span class="badge pill badge-success">'.$invoiceDiscounts->payment_status.'</span>';
                }elseif ($invoiceDiscounts->payment_status == 'CANCELLED'){
                    return '<span class="badge pill badge-warning">'.$invoiceDiscounts->payment_status.'</span>';
                }
            })

            ->addColumn('actions', function($invoiceDiscounts){ // add custom column
                $actions = '<div class="align-content-center">';


//                $actions .= '<a href="' . $invoiceDiscounts->irrevocable_letter_link . '"
//                    class="btn btn-primary btn-link btn-sm" target="_blank">
//                    <i class="material-icons">mark_email_read</i> Letter </a>';
//
//                $actions .= '<a href="' . $invoiceDiscounts->contract_link . '"
//                    class="btn btn-primary btn-link btn-sm" target="_blank">
//                    <i class="material-icons">receipt</i> Contract </a>';

                $actions .= '<a href="' . route('manager-invoice-details',  $invoiceDiscounts->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View </a>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','invoice_number','approval_status','offer_status','payment_status'])


            ->make(true);
    }

    public function invoice_details($id) {

        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (!is_null($hr))
            $employerId = $hr->employer_id;
        else
            $employerId = 0;


        $invoiceDiscount = InvoiceDiscount::find($id);

        if (is_null($invoiceDiscount))
            abort(404,"Invoice discount not found");


        if ($employerId != $invoiceDiscount->employer_id)
            abort(403,"You are not allowed to access this resource. Please contact system admin");


        return view('hr.invoice_details')->with(['invoiceDiscount'=>$invoiceDiscount]);
    }

    public function invoice_discount_invoices($id) {


        $invoices = Invoice::where('invoice_discount_id',$id)->orderBy('id','desc')->get();

        return DataTables::of($invoices)


            ->editColumn('invoice_amount', function ($invoices) {
                return 'KES '. number_format($invoices->invoice_amount);
            })

            ->editColumn('invoice_date', function ($invoices) {
                return Carbon::parse($invoices->invoice_date)->isoFormat('MMM Do YYYY');
            })

            ->editColumn('expected_payment_date', function ($invoices) {
                return $invoices->expected_payment_date == null ? 'NOT SET' : Carbon::parse($invoices->expected_payment_date)->isoFormat('MMM Do YYYY');
            })

            ->editColumn('invoice_number', function ($invoices) {
                return '<a href="'. $invoices->invoice_link .'" class="btn btn-primary btn-link btn-sm" target="_blank"> '.$invoices->invoice_number.' </a>';
            })

            ->editColumn('approval_status',function ($invoices) {
                if ($invoices->approval_status == 'PENDING'){
                    return '<span class="badge pill badge-info">'.$invoices->approval_status.'</span>';
                }elseif ($invoices->approval_status == 'APPROVED'){
                    return '<span class="badge pill badge-success">'.$invoices->approval_status.'</span>';
                }elseif ($invoices->approval_status == 'REJECTED'){
                    return '<span class="badge pill badge-danger">'.$invoices->approval_status.'</span>';
                }
            })

            ->rawColumns(['approval_status','invoice_number'])


            ->make(true);
    }

}
