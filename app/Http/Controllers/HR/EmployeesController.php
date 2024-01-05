<?php

namespace App\Http\Controllers\HR;

use App\AuditTrail;
use App\CustomerProfile;
use App\Employee;
use App\Employer;
use App\HrManager;
use App\Http\Controllers\Controller;
use App\LoanRequest;
use App\Notifications\EmployeeApproved;
use App\Notifications\EmployeeRejectd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EmployeesController extends Controller
{
    public function all_employees() {
        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (is_null($hr))
            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");

        return view('hr.employees')->with(['title'=>'All Employees', 'ajax'=>'all-employees-dt']);
    }
    public function allEmployeesDT() {

        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (!is_null($hr))
            $employerId = $hr->employer_id;
        else
            $employerId = 0;


        $employees = Employee::where('employer_id',$employerId)->get();

        return DataTables::of($employees)
            ->editColumn('selfie', function ($employees) {
                return '<a href="' . $employees->passport_photo_url . '" target="_blank">
                <img src="'.$employees->passport_photo_url.'" width="75" height="75" />';
            })

            ->editColumn('name', function ($employees) {
                return optional($employees->user)->name;
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

                $actions .= '<a href="' . $employees->latest_payslip_url . '" target="_blank"
                    class="btn btn-primary btn-link btn-sm edit-matrix-btn" >
                   Payslip</button>';

                $actions .= '<a href="' . route('hr-employee-details' ,  $employees->id) . '"
                    class="btn btn-primary btn-link btn-sm">
                    <i class="material-icons">visibility</i> View</a>';


//                $actions .= '<form action="'. route('delete-matrix',  $employers->id) .'" style="display: inline;" method="POST" class="del-matrix-form">';
//                $actions .= method_field('DELETE');
//                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','selfie'])


            ->make(true);
    }

//    public function active_employees() {
//
//        $hr = HrManager::where('user_id', auth()->user()->id)->first();
//        if (is_null($hr))
//            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");
//
//        return view('hr.employees')->with(['title'=>'Active Employees', 'ajax'=>'active-employees-dt']);
//    }
//    public function activeEmployeesDT() {
//
//        $hr = HrManager::where('user_id', auth()->user()->id)->first();
//        if (!is_null($hr))
//            $employerId = $hr->employer_id;
//        else
//            $employerId = 0;
//
//
//        $employees = Employee::where('employer_id',$employerId)->where('status','ACTIVE')->get();
//
//        return DataTables::of($employees)
//            ->editColumn('selfie', function ($employees) {
//                return '<a href="' . $employees->passport_photo_url . '" target="_blank">
//                <img src="'.$employees->passport_photo_url.'" width="75" height="75" />';
//            })
//
//            ->editColumn('status',function ($employees) {
//                if ($employees->status == 'PENDING'){
//                    return '<span class="badge pill badge-info">'.$employees->status.'</span>';
//                }elseif ($employees->status == 'ACTIVE'){
//                    return '<span class="badge pill badge-success">'.$employees->status.'</span>';
//                }elseif ($employees->status == 'REJECTED'){
//                    return '<span class="badge pill badge-danger">'.$employees->status.'</span>';
//                }else{
//                    return '<span class="badge pill badge-warning">'.$employees->status.'</span>';
//                }
//            })
//
//            ->editColumn('name', function ($employees) {
//                return optional($employees->user)->name;
//            })
//
//            ->editColumn('basic_salary', function ($employees) {
//                return number_format($employees->basic_salary);
//            })
//
//            ->editColumn('net_salary', function ($employees) {
//                return number_format($employees->net_salary);
//            })
//
//            ->editColumn('max_limit', function ($employees) {
//                return number_format($employees->max_limit);
//            })
//
//            ->addColumn('actions', function($employees){ // add custom column
//                $actions = '<div class="align-content-center">';
//
//                $actions .= '<a href="' . $employees->latest_payslip_url . '" target="_blank"
//                    class="btn btn-primary btn-link btn-sm edit-matrix-btn" >
//                   Payslip</button>';
//
//                $actions .= '<a href="' . route('hr-employee-details' ,  $employees->id) . '"
//                    class="btn btn-primary btn-link btn-sm">
//                    <i class="material-icons">visibility</i> View</a>';
//
//
////                $actions .= '<form action="'. route('delete-matrix',  $employers->id) .'" style="display: inline;" method="POST" class="del-matrix-form">';
////                $actions .= method_field('DELETE');
////                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';
//
//                $actions .= '</div>';
//                return $actions;
//            })
//            ->rawColumns(['actions','selfie','status'])
//
//
//            ->make(true);
//    }

//    public function inactive_employees() {
//
//        $hr = HrManager::where('user_id', auth()->user()->id)->first();
//        if (is_null($hr))
//            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");
//
//        return view('hr.employees')->with(['title'=>'Inactive Employees', 'ajax'=>'inactive-employees-dt']);
//    }
//    public function inactiveEmployeesDT() {
//
//        $hr = HrManager::where('user_id', auth()->user()->id)->first();
//        if (!is_null($hr))
//            $employerId = $hr->employer_id;
//        else
//            $employerId = 0;
//
//
//        $employees = Employee::where('employer_id',$employerId)->where('status','INACTIVE')->get();
//
//        return DataTables::of($employees)
//            ->editColumn('selfie', function ($employees) {
//                return '<a href="' . $employees->passport_photo_url . '" target="_blank">
//                <img src="'.$employees->passport_photo_url.'" width="75" height="75" />';
//            })
//
//            ->editColumn('status',function ($employees) {
//                if ($employees->status == 'PENDING'){
//                    return '<span class="badge pill badge-info">'.$employees->status.'</span>';
//                }elseif ($employees->status == 'ACTIVE'){
//                    return '<span class="badge pill badge-success">'.$employees->status.'</span>';
//                }elseif ($employees->status == 'REJECTED'){
//                    return '<span class="badge pill badge-danger">'.$employees->status.'</span>';
//                }else{
//                    return '<span class="badge pill badge-warning">'.$employees->status.'</span>';
//                }
//            })
//
//            ->editColumn('name', function ($employees) {
//                return optional($employees->user)->name;
//            })
//
//            ->editColumn('basic_salary', function ($employees) {
//                return number_format($employees->basic_salary);
//            })
//
//            ->editColumn('net_salary', function ($employees) {
//                return number_format($employees->net_salary);
//            })
//
//            ->editColumn('max_limit', function ($employees) {
//                return number_format($employees->max_limit);
//            })
//
//            ->addColumn('actions', function($employees){ // add custom column
//                $actions = '<div class="align-content-center">';
//
//                $actions .= '<a href="' . $employees->latest_payslip_url . '" target="_blank"
//                    class="btn btn-primary btn-link btn-sm edit-matrix-btn" >
//                   Payslip</button>';
//
//                $actions .= '<a href="' . route('hr-employee-details' ,  $employees->id) . '"
//                    class="btn btn-primary btn-link btn-sm">
//                    <i class="material-icons">visibility</i> View</a>';
//
//
////                $actions .= '<form action="'. route('delete-matrix',  $employers->id) .'" style="display: inline;" method="POST" class="del-matrix-form">';
////                $actions .= method_field('DELETE');
////                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';
//
//                $actions .= '</div>';
//                return $actions;
//            })
//            ->rawColumns(['actions','selfie','status'])
//
//
//            ->make(true);
//    }
//
//    public function pending_employees() {
//
//        $hr = HrManager::where('user_id', auth()->user()->id)->first();
//        if (is_null($hr))
//            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");
//
//        return view('hr.employees')->with(['title'=>'Pending Employees', 'ajax'=>'pending-employees-dt']);
//    }
//    public function pendingEmployeesDT() {
//
//        $hr = HrManager::where('user_id', auth()->user()->id)->first();
//        if (!is_null($hr))
//            $employerId = $hr->employer_id;
//        else
//            $employerId = 0;
//
//
//        $employees = Employee::where('employer_id',$employerId)->where('status','PENDING')->get();
//
//        return DataTables::of($employees)
//            ->editColumn('selfie', function ($employees) {
//                return '<a href="' . $employees->passport_photo_url . '" target="_blank">
//                <img src="'.$employees->passport_photo_url.'" width="75" height="75" />';
//            })
//
//            ->editColumn('status',function ($employees) {
//                if ($employees->status == 'PENDING'){
//                    return '<span class="badge pill badge-info">'.$employees->status.'</span>';
//                }elseif ($employees->status == 'ACTIVE'){
//                    return '<span class="badge pill badge-success">'.$employees->status.'</span>';
//                }elseif ($employees->status == 'REJECTED'){
//                    return '<span class="badge pill badge-danger">'.$employees->status.'</span>';
//                }else{
//                    return '<span class="badge pill badge-warning">'.$employees->status.'</span>';
//                }
//            })
//
//            ->editColumn('name', function ($employees) {
//                return optional($employees->user)->name;
//            })
//
//            ->editColumn('basic_salary', function ($employees) {
//                return number_format($employees->basic_salary);
//            })
//
//            ->editColumn('net_salary', function ($employees) {
//                return number_format($employees->net_salary);
//            })
//
//            ->editColumn('max_limit', function ($employees) {
//                return number_format($employees->max_limit);
//            })
//
//            ->addColumn('actions', function($employees){ // add custom column
//                $actions = '<div class="align-content-center">';
//
//                $actions .= '<a href="' . $employees->latest_payslip_url . '" target="_blank"
//                    class="btn btn-primary btn-link btn-sm edit-matrix-btn" >
//                   Payslip</button>';
//
//                $actions .= '<a href="' . route('hr-employee-details' ,  $employees->id) . '"
//                    class="btn btn-primary btn-link btn-sm">
//                    <i class="material-icons">visibility</i> View</a>';
//
//
////                $actions .= '<form action="'. route('delete-matrix',  $employers->id) .'" style="display: inline;" method="POST" class="del-matrix-form">';
////                $actions .= method_field('DELETE');
////                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';
//
//                $actions .= '</div>';
//                return $actions;
//            })
//            ->rawColumns(['actions','selfie','status'])
//
//
//            ->make(true);
//    }
//
//    public function rejected_employees() {
//        $hr = HrManager::where('user_id', auth()->user()->id)->first();
//        if (is_null($hr))
//            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");
//
//        return view('hr.employees')->with(['title'=>'Rejected Employees', 'ajax'=>'rejected-employees-dt']);
//    }
//    public function rejectedEmployeesDT() {
//
//        $hr = HrManager::where('user_id', auth()->user()->id)->first();
//        if (!is_null($hr))
//            $employerId = $hr->employer_id;
//        else
//            $employerId = 0;
//
//
//        $employees = Employee::where('employer_id',$employerId)->where('status','REJECTED')->get();
//
//        return DataTables::of($employees)
//            ->editColumn('selfie', function ($employees) {
//                return '<a href="' . $employees->passport_photo_url . '" target="_blank">
//                <img src="'.$employees->passport_photo_url.'" width="75" height="75" />';
//            })
//
//            ->editColumn('status',function ($employees) {
//                if ($employees->status == 'PENDING'){
//                    return '<span class="badge pill badge-info">'.$employees->status.'</span>';
//                }elseif ($employees->status == 'ACTIVE'){
//                    return '<span class="badge pill badge-success">'.$employees->status.'</span>';
//                }elseif ($employees->status == 'REJECTED'){
//                    return '<span class="badge pill badge-danger">'.$employees->status.'</span>';
//                }else{
//                    return '<span class="badge pill badge-warning">'.$employees->status.'</span>';
//                }
//            })
//
//            ->editColumn('name', function ($employees) {
//                return optional($employees->user)->name;
//            })
//
//            ->editColumn('basic_salary', function ($employees) {
//                return number_format($employees->basic_salary);
//            })
//
//            ->editColumn('net_salary', function ($employees) {
//                return number_format($employees->net_salary);
//            })
//
//            ->editColumn('max_limit', function ($employees) {
//                return number_format($employees->max_limit);
//            })
//
//            ->addColumn('actions', function($employees){ // add custom column
//                $actions = '<div class="align-content-center">';
//
//                $actions .= '<a href="' . $employees->latest_payslip_url . '" target="_blank"
//                    class="btn btn-primary btn-link btn-sm edit-matrix-btn" >
//                   Payslip</button>';
//
//                $actions .= '<a href="' . route('hr-employee-details' ,  $employees->id) . '"
//                    class="btn btn-primary btn-link btn-sm">
//                    <i class="material-icons">visibility</i> View</a>';
//
//
////                $actions .= '<form action="'. route('delete-matrix',  $employers->id) .'" style="display: inline;" method="POST" class="del-matrix-form">';
////                $actions .= method_field('DELETE');
////                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';
//
//                $actions .= '</div>';
//                return $actions;
//            })
//            ->rawColumns(['actions','selfie','status'])
//
//
//            ->make(true);
//    }

    public function employee_details($id) {

        $hr = HrManager::where('user_id', auth()->user()->id)->first();
        if (is_null($hr))
            abort(403,"You have not been assigned to an organisation yet. Please contact system admin");


        $employee = Employee::find($id);

        if (is_null($employee))
            abort(404, "Employee does not exist");

        return view('hr.employee_details')->with(['employee'=>$employee]);
    }

    public function employeeLoansDT($_user_id) {
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

            ->rawColumns(['repayment_status','approval_status'])
            ->make(true);

    }

//    public  function reject_employee(Request  $request){
//        $data = request()->validate([
//            'reject_reason'  => 'required',
//            'employee_id'  => 'required',
//        ]);
//
//        $employee = Employee::find($request->employee_id);
//
//        if (is_null($employee))
//            abort(404);
//
//
//        DB::transaction(function() use ($request, $employee) {
//
//            $employee->comments=$request->reject_reason;
//            $employee->status="REJECTED";
//            $employee->update();
//
//            AuditTrail::create([
//                'created_by' => auth()->user()->id,
//                'action' => 'Rejected employee request ('.optional($employee->user)->name.'> with reason ==> '.$request->reject_reason,
//            ]);
//
//            request()->session()->flash('success', 'Employee request has been rejected');
//
//            $hr = HrManager::where('user_id', auth()->user()->id)->first();
//            send_sms(optional($employee->user)->phone_no, "Your employee request was rejected by ".optional($hr->employer)->business_name.". Check your email for more details");
//
//            $employee->user->notify(new EmployeeRejectd($request->reject_reason, optional($hr->employer)->business_name));
//
//        });
//        return redirect()->back();
//    }
//
//    public  function approve_employee(Request  $request){
//        $data = request()->validate([
//            'basic_salary'  => 'required',
//            'gross_salary'  => 'required',
//            'net_salary'  => 'required',
//            'max_limit'  => 'required',
//            'comments'  => 'required',
//            'employee_id'  => 'required',
//        ]);
//
//        $employee = Employee::find($request->employee_id);
//
//        if (is_null($employee))
//            abort(404);
//
//
//        DB::transaction(function() use ($request, $employee) {
//
//            $employee->basic_salary=$request->basic_salary;
//            $employee->gross_salary=$request->gross_salary;
//            $employee->net_salary=$request->net_salary;
//            $employee->max_limit=$request->max_limit;
//            $employee->comments=$request->comments;
//            $employee->status="ACTIVE";
//            $employee->update();
//
//
//            $customerProfile = CustomerProfile::where('user_id',$employee->user_id)->first();
//
//            if (!is_null($customerProfile)){
//                $customerProfile->is_checkoff = true;
//                $customerProfile->update();
//            }
//
//            AuditTrail::create([
//                'created_by' => auth()->user()->id,
//                'action' => 'Approved employee request ('.optional($employee->user)->name.'> with reason ==> '.$request->comments.'. New limit set at Ksh.'.$request->max_limit,
//            ]);
//
//            request()->session()->flash('success', 'Employee request has been approved');
//
//            $hr = HrManager::where('user_id', auth()->user()->id)->first();
//            send_sms(optional($employee->user)->phone_no, "Your employee request was approved by ".optional($hr->employer)->business_name.". Check your email for more details");
//
//            $employee->user->notify(new EmployeeApproved($request->comments, optional($hr->employer)->business_name, $request->max_limit));
//
//        });
//        return redirect()->back();
//    }

    public  function update_limit(Request  $request){
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

            $hr = HrManager::where('user_id', auth()->user()->id)->first();
            send_sms(optional($employee->user)->phone_no, "Your Inua limit for ".optional($hr->employer)->business_name." has been updated to Ksh. ".$request->max_limit);
        });
        return redirect()->back();
    }



}
