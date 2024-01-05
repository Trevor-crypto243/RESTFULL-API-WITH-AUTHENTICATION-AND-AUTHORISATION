<?php

namespace App\Http\Controllers\HR;

use App\AuditTrail;
use App\Employee;
use App\Exports\HrAmendmentAdvance;
use App\Exports\HrApprovedAdvance;
use App\Exports\HrPendingAdvance;
use App\Exports\HrRejectedAdvance;
use App\HrManager;
use App\Http\Controllers\Controller;
use App\InterestRateMatrix;
use App\AdvanceApplication;
use App\AdvanceApplicationComment;
use App\LoanRequest;
use App\LoanRequestFee;
use App\LoanSchedule;
use App\Notifications\AdvanceApplicationNotification;
use App\Notifications\NewIdfApproval;
use App\User;
use App\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class SalaryAdvanceController extends Controller
{
    public function pendingAdvance() {
        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (is_null($hr))
            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");

        return view('hr.advance')->with([
            'title'=>'Pending Salary Advance Applications',
            'ajax'=>'pending-advance-requests-dt',
            'export'=>'hr/advance/pending/export'
        ]);
    }
    public function exportPendingAdvance() {


        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (is_null($hr))
            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");

        return Excel::download(new HrPendingAdvance($hr->employer_id), 'pending.xlsx');

    }
    public function pendingAdvanceDT() {

        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (!is_null($hr))
            $employerId = $hr->employer_id;
        else
            $employerId = 0;


        $advanceApplications = AdvanceApplication::where('employer_id',$employerId)
            ->where('quicksava_status','PROCESSING')
            ->where('hr_status','PENDING')->get();

        return DataTables::of($advanceApplications)
            ->addColumn('selfie', function ($advanceApplications)use ($employerId) {
                return '<a href="' . optional(Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first())->passport_photo_url . '" target="_blank">
                <img src="'.optional(Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first())->passport_photo_url.'" width="75" height="75" />';
            })

            ->addColumn('name', function ($advanceApplications) use ($employerId) {

                $emp = Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first();
                return '<a href="' . route('hr-employee-details' ,  is_null($emp) ? 0 : $emp->id) . '"
                    class="btn btn-primary btn-link btn-sm" target="_blank">'.optional($advanceApplications->user)->name.' </a>';
            })

            ->addColumn('surname', function ($advanceApplications) {
                return optional($advanceApplications->user)->surname;
            })

            ->addColumn('payroll_no', function ($advanceApplications) use ($employerId) {
                return optional(Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first())->payroll_no;
            })

            ->editColumn('amount_requested', function ($advanceApplications) {
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

            ->editColumn('period_in_months', function ($advanceApplications) {
                return $advanceApplications->period_in_months. " Months";
            })

            ->editColumn('hr_status',function ($advanceApplications) {
                if ($advanceApplications->hr_status == 'PENDING'){
                    return '<span class="badge pill badge-info">'.$advanceApplications->hr_status.'</span>';
                }elseif ($advanceApplications->hr_status == 'ACCEPTED'){
                    return '<span class="badge pill badge-success">'.$advanceApplications->hr_status.'</span>';
                }elseif ($advanceApplications->hr_status == 'REJECTED'){
                    return '<span class="badge pill badge-danger">'.$advanceApplications->hr_status.'</span>';
                }elseif ($advanceApplications->hr_status == 'AMENDMENT'){
                    return '<span class="badge pill badge-warning">'.$advanceApplications->hr_status.'</span>';
                }
            })

            ->addColumn('actions', function($advanceApplications){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . route('advance-hr-application-details' ,  $advanceApplications->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View </a>';


//                $actions .= '<form action="'. route('delete-matrix',  $employers->id) .'" style="display: inline;" method="POST" class="del-matrix-form">';
//                $actions .= method_field('DELETE');
//                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','selfie','hr_status','name'])


            ->make(true);
    }


    public function approvedAdvance() {
        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (is_null($hr))
            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");

        return view('hr.advance')->with([
            'title'=>'Approved Salary Advance Applications',
            'ajax'=>'approved-advance-requests-dt',
            'export'=>'hr/advance/approved/export'
        ]);
    }
    public function exportApprovedAdvance() {

        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (is_null($hr))
            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");

        return Excel::download(new HrApprovedAdvance($hr->employer_id), 'approved.xlsx');

    }
    public function approvedAdvanceDT() {

        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (!is_null($hr))
            $employerId = $hr->employer_id;
        else
            $employerId = 0;


        $advanceApplications = AdvanceApplication::where('employer_id',$employerId)->where('hr_status','ACCEPTED')->get();

        return DataTables::of($advanceApplications)
            ->addColumn('selfie', function ($advanceApplications)use ($employerId) {
                return '<a href="' . optional(Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first())->passport_photo_url . '" target="_blank">
                <img src="'.optional(Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first())->passport_photo_url.'" width="75" height="75" />';
            })

            ->addColumn('name', function ($advanceApplications) use ($employerId) {

                $emp = Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first();
                return '<a href="' . route('hr-employee-details' ,  is_null($emp) ? 0 : $emp->id) . '"
                    class="btn btn-primary btn-link btn-sm" target="_blank">'.optional($advanceApplications->user)->name.' </a>';
            })

            ->addColumn('surname', function ($advanceApplications) {
                return optional($advanceApplications->user)->surname;
            })

            ->addColumn('payroll_no', function ($advanceApplications) use ($employerId) {
                return optional(Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first())->payroll_no;
            })

            ->editColumn('amount_requested', function ($advanceApplications) {
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

            ->editColumn('period_in_months', function ($advanceApplications) {
                return $advanceApplications->period_in_months. " Months";
            })

            ->editColumn('hr_status',function ($advanceApplications) {
                if ($advanceApplications->hr_status == 'PENDING'){
                    return '<span class="badge pill badge-info">'.$advanceApplications->hr_status.'</span>';
                }elseif ($advanceApplications->hr_status == 'ACCEPTED'){
                    return '<span class="badge pill badge-success">'.$advanceApplications->hr_status.'</span>';
                }elseif ($advanceApplications->hr_status == 'REJECTED'){
                    return '<span class="badge pill badge-danger">'.$advanceApplications->hr_status.'</span>';
                }elseif ($advanceApplications->hr_status == 'AMENDMENT'){
                    return '<span class="badge pill badge-warning">'.$advanceApplications->hr_status.'</span>';
                }
            })

            ->addColumn('actions', function($advanceApplications){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . route('advance-hr-application-details' ,  $advanceApplications->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View </a>';


//                $actions .= '<form action="'. route('delete-matrix',  $employers->id) .'" style="display: inline;" method="POST" class="del-matrix-form">';
//                $actions .= method_field('DELETE');
//                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','selfie','hr_status','name'])


            ->make(true);
    }


    public function rejectedAdvance() {
        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (is_null($hr))
            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");

        return view('hr.advance')->with([
            'title'=>'Rejected Salary Advance Applications',
            'ajax'=>'rejected-advance-requests-dt',
            'export'=>'hr/advance/rejected/export'
        ]);
    }
    public function exportRejectedAdvance() {

        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (is_null($hr))
            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");

        return Excel::download(new HrRejectedAdvance($hr->employer_id), 'rejected.xlsx');
    }
    public function rejectedAdvanceDT() {

        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (!is_null($hr))
            $employerId = $hr->employer_id;
        else
            $employerId = 0;


        $advanceApplications = AdvanceApplication::where('employer_id',$employerId)->where('hr_status','REJECTED')->get();

        return DataTables::of($advanceApplications)
            ->addColumn('selfie', function ($advanceApplications)use ($employerId) {
                return '<a href="' . optional(Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first())->passport_photo_url . '" target="_blank">
                <img src="'.optional(Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first())->passport_photo_url.'" width="75" height="75" />';
            })

            ->addColumn('name', function ($advanceApplications) use ($employerId) {

                $emp = Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first();
                return '<a href="' . route('hr-employee-details' ,  is_null($emp) ? 0 : $emp->id) . '"
                    class="btn btn-primary btn-link btn-sm" target="_blank">'.optional($advanceApplications->user)->name.' </a>';
            })

            ->addColumn('surname', function ($advanceApplications) {
                return optional($advanceApplications->user)->surname;
            })

            ->addColumn('payroll_no', function ($advanceApplications) use ($employerId) {
                return optional(Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first())->payroll_no;
            })

            ->editColumn('amount_requested', function ($advanceApplications) {
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

            ->editColumn('period_in_months', function ($advanceApplications) {
                return $advanceApplications->period_in_months. " Months";
            })

            ->editColumn('hr_status',function ($advanceApplications) {
                if ($advanceApplications->hr_status == 'PENDING'){
                    return '<span class="badge pill badge-info">'.$advanceApplications->hr_status.'</span>';
                }elseif ($advanceApplications->hr_status == 'ACCEPTED'){
                    return '<span class="badge pill badge-success">'.$advanceApplications->hr_status.'</span>';
                }elseif ($advanceApplications->hr_status == 'REJECTED'){
                    return '<span class="badge pill badge-danger">'.$advanceApplications->hr_status.'</span>';
                }elseif ($advanceApplications->hr_status == 'AMENDMENT'){
                    return '<span class="badge pill badge-warning">'.$advanceApplications->hr_status.'</span>';
                }
            })

            ->addColumn('actions', function($advanceApplications){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . route('advance-hr-application-details' ,  $advanceApplications->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View </a>';


//                $actions .= '<form action="'. route('delete-matrix',  $employers->id) .'" style="display: inline;" method="POST" class="del-matrix-form">';
//                $actions .= method_field('DELETE');
//                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','selfie','hr_status','name'])


            ->make(true);
    }

    public function amendmentAdvance() {
        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (is_null($hr))
            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");

        return view('hr.advance')->with([
            'title'=>'Amendment Salary Advance Applications',
            'ajax'=>'amendment-advance-requests-dt',
            'export'=>'hr/advance/amendment/export'
        ]);
    }
    public function exportAmendmentAdvance() {

        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (is_null($hr))
            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");

        return Excel::download(new HrAmendmentAdvance($hr->employer_id), 'amendment.xlsx');

    }
    public function amendmentAdvanceDT() {

        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (!is_null($hr))
            $employerId = $hr->employer_id;
        else
            $employerId = 0;


        $advanceApplications = AdvanceApplication::where('employer_id',$employerId)->where('hr_status','AMENDMENT')->get();

        return DataTables::of($advanceApplications)
            ->addColumn('selfie', function ($advanceApplications)use ($employerId) {
                return '<a href="' . optional(Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first())->passport_photo_url . '" target="_blank">
                <img src="'.optional(Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first())->passport_photo_url.'" width="75" height="75" />';
            })

            ->addColumn('name', function ($advanceApplications) use ($employerId) {

                $emp = Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first();
                return '<a href="' . route('hr-employee-details' ,  is_null($emp) ? 0 : $emp->id) . '"
                    class="btn btn-primary btn-link btn-sm" target="_blank">'.optional($advanceApplications->user)->name.' </a>';
            })

            ->addColumn('surname', function ($advanceApplications) {
                return optional($advanceApplications->user)->surname;
            })

            ->addColumn('payroll_no', function ($advanceApplications) use ($employerId) {
                return optional(Employee::where('user_id', $advanceApplications->user_id)->where('employer_id', $employerId)->first())->payroll_no;
            })

            ->editColumn('amount_requested', function ($advanceApplications) {
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

            ->editColumn('period_in_months', function ($advanceApplications) {
                return $advanceApplications->period_in_months. " Months";
            })

            ->editColumn('hr_status',function ($advanceApplications) {
                if ($advanceApplications->hr_status == 'PENDING'){
                    return '<span class="badge pill badge-info">'.$advanceApplications->hr_status.'</span>';
                }elseif ($advanceApplications->hr_status == 'ACCEPTED'){
                    return '<span class="badge pill badge-success">'.$advanceApplications->hr_status.'</span>';
                }elseif ($advanceApplications->hr_status == 'REJECTED'){
                    return '<span class="badge pill badge-danger">'.$advanceApplications->hr_status.'</span>';
                }elseif ($advanceApplications->hr_status == 'AMENDMENT'){
                    return '<span class="badge pill badge-warning">'.$advanceApplications->hr_status.'</span>';
                }
            })

            ->addColumn('actions', function($advanceApplications){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . route('advance-hr-application-details' ,  $advanceApplications->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View </a>';


//                $actions .= '<form action="'. route('delete-matrix',  $employers->id) .'" style="display: inline;" method="POST" class="del-matrix-form">';
//                $actions .= method_field('DELETE');
//                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','selfie','hr_status','name'])


            ->make(true);
    }


    public function advance_application_details($id) {

        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (is_null($hr))
            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");


        $advanceApplication = AdvanceApplication::find($id);

        if (is_null($advanceApplication))
            abort(404,"Application not found");

        if ($advanceApplication->employer_id != $hr->employer_id)
            abort(403,"You are not authorised to view this resource. Please contact system admin");


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

        return view('hr.advance_request_details')->with([
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
        $advanceApplication->hr_status = 'AMENDMENT';
        $advanceApplication->hr_comments = $request->amendment_details;
        $advanceApplication->update();

        $comment = new AdvanceApplicationComment();
        $comment->created_by = auth()->user()->id;
        $comment->advance_application_id = $advanceApplication->id;
        $comment->comment_origin = 'EMPLOYER';
        $comment->comment = $request->amendment_details;
        $comment->saveOrFail();


        request()->session()->flash('success', 'Amendment request has been sent successfully');

        return redirect()->back();
    }

    public function reject_request(Request $request) {
        $this->validate($request, [
            'request_id' =>'required|exists:advance_applications,id',
            'reject_reason' =>'required',
        ]);

        $advanceApplication = AdvanceApplication::find($request->request_id);
        $advanceApplication->hr_status = 'REJECTED';
        $advanceApplication->hr_comments = $request->reject_reason;
        $advanceApplication->update();

        $comment = new AdvanceApplicationComment();
        $comment->created_by = auth()->user()->id;
        $comment->advance_application_id = $advanceApplication->id;
        $comment->comment_origin = 'EMPLOYER';
        $comment->comment = $request->reject_reason;
        $comment->saveOrFail();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'HR Rejected salary advance application from '.optional($advanceApplication->user)->name. ' for Ksh. '.number_format($advanceApplication->amount_requested).'. Entry ID #'.$advanceApplication->id,
        ]);

        request()->session()->flash('success', 'Request has been rejected');

        return redirect()->back();
    }

    public function approve_request(Request $request) {
        $this->validate($request, [
            'request_id' =>'required|exists:advance_applications,id',
        ]);

        $advanceApplication = AdvanceApplication::find($request->request_id);
        $advanceApplication->hr_status = 'ACCEPTED';
        $advanceApplication->update();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'HR Accepted salary advance application from '.optional($advanceApplication->user)->name. ' for Ksh. '.number_format($advanceApplication->amount_requested).'. Entry ID #'.$advanceApplication->id,
        ]);

        //send email to admins and super admins
        foreach (User::whereIn('user_group',[1,3])->get() as $notifiable){
            $notifiable->notify(new AdvanceApplicationNotification(optional($advanceApplication->user)->name));
        }

        request()->session()->flash('success', 'Request has been approved');


        return redirect()->back();
    }

}
