<?php

namespace App\Http\Controllers;

use App\AuditTrail;
use App\Bank;
use App\BankAccount;
use App\BankBranch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class BankController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function get_banks_json ($bank_id){
        return json_encode(Bank::where('bank_id',$bank_id)->get());
    }
    public function banks() {
        $banks  = Bank::all();
        return view('bank.banks')->with([
            'banks'=>$banks
        ]);
    }
    public function create_bank(Request $request)
    {
        $this->validate($request, [
            'swift_code' => 'required',
            'bank_name' => 'required',
        ],[
//            'phone_no.exists' => 'The phone number is not registered to any Quicksava account',
        ]);


        $exists = Bank::where('swift_code',$request->swift_code)
            ->orWhere('bank_name', $request->bank_name)
            ->first();

        if (is_null($exists)){
            $bank = new Bank();
            $bank->swift_code = $request->swift_code;
            $bank->bank_name = $request->bank_name;
            $bank->save();


            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Created a new bank:'.$request->bank_name,
            ]);

            Session::flash("success", "Bank has been created successfully");
        }else{
            Session::flash("warning", "A bank with a similar name or swift code already exists");
        }

        return redirect()->back();
    }
    public function banksDT() {
        $banks = Bank::all();
        return DataTables::of($banks)

            ->editColumn('branches', function ($banks) {
                return $banks->branches()->count();
            })
            ->addColumn('actions', function($banks){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<button source="' . route('bank-details' ,  $banks->id) . '"
                    class="btn btn-primary btn-link btn-sm edit-bank-btn" acs-id="'.$banks->id .'">
                    <i class="material-icons">edit</i> Edit</button>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])

            ->make(true);

    }
    public function bank_details($id)
    {
        $rslt = Bank::find($id);
        return $rslt;
    }
    public function update_bank(Request $request)
    {
        $data = request()->validate([
            'id' => 'required|exists:banks,id',
            'swift_code' => 'required',
            'bank_name' => 'required',
        ]);

        $exists = Bank::where('id','!=' ,$request->id)
            ->where('bank_name', $request->bank_name)
            ->first();

        if (is_null($exists)){

            $bank = Bank::find($request->id);
            $bank->swift_code = $request->swift_code;
            $bank->bank_name = $request->bank_name;
            $bank->update();

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Edited bank of id #'.$request->id.' to '.$request->bank_name.' ('.$request->swift_code.')',
            ]);

            request()->session()->flash('success', 'Bank has been updated.');

        }else{
            request()->session()->flash('warning', 'A bank with a similar name already exists.');
        }
        return redirect()->back();
    }



    public function create_branch(Request $request)
    {
        $this->validate($request, [
            'bank_id' => 'required|exists:banks,id',
            'sort_code' => 'required',
            'branch_name' => 'required',
        ],[
//            'phone_no.exists' => 'The phone number is not registered to any Quicksava account',
        ]);


        $exists = BankBranch::where('bank_id',$request->bank_id)
            ->where('sort_code',$request->sort_code)
            ->first();

        if (is_null($exists)){
            $bank = new BankBranch();
            $bank->bank_id = $request->bank_id;
            $bank->sort_code = $request->sort_code;
            $bank->branch_name = $request->branch_name;
            $bank->save();


            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Created a new bank branch:'.$request->branch_name,
            ]);

            Session::flash("success", "Bank Branch has been created successfully");
        }else{
            Session::flash("warning", "A branch with a similar bank and sort code already exists");
        }

        return redirect()->back();
    }
    public function bankBranchesDT() {
        $bankBranches = BankBranch::all();
        return DataTables::of($bankBranches)

            ->editColumn('bank', function ($bankBranches) {
                return optional($bankBranches->bank)->bank_name;
            })
            ->addColumn('actions', function($bankBranches){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<button source="' . route('branch-details' ,  $bankBranches->id) . '"
                    class="btn btn-primary btn-link btn-sm edit-branch-btn" acs-id="'.$bankBranches->id .'">
                    <i class="material-icons">edit</i> Edit</button>';

                $actions .= '<form action="'. route('delete-branch',  $bankBranches->id) .'" style="display: inline;" method="POST" class="delete-branch-form">';
                $actions .= method_field('DELETE');
                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])

            ->make(true);

    }
    public function branch_details($id)
    {
        $rslt = BankBranch::find($id);
        return $rslt;
    }
    public function update_branch(Request $request)
    {
        $data = request()->validate([
            'bank_id' => 'required|exists:banks,id',
            'branch_id' => 'required|exists:bank_branches,id',
            'sort_code' => 'required',
            'branch_name' => 'required',
        ]);

        $exists = BankBranch::where('id','!=' ,$request->branch_id)
            ->where('sort_code', $request->sort_code)
            ->first();

        if (is_null($exists)){

            $bankBranch = BankBranch::find($request->branch_id);
            $bankBranch->bank_id = $request->bank_id;
            $bankBranch->sort_code = $request->sort_code;
            $bankBranch->branch_name = $request->branch_name;
            $bankBranch->update();

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Edited bank branch of id #'.$request->branch_id.' to '.$request->branch_name.' ('.$request->sort_code.')',
            ]);

            request()->session()->flash('success', 'Bank Branch has been updated.');

        }else{
            request()->session()->flash('warning', 'A branch with a similar sort code already exists.');
        }
        return redirect()->back();
    }
    public function delete_branch($id)
    {

        $branch = BankBranch::find($id);
        $bankName =optional($branch->bank)->bank_name;
        $branchName = $branch->branch_name;

        try {
            if ($branch->delete()){
                AuditTrail::create([
                    'created_by' => auth()->user()->id,
                    'action' => 'Deleted bank branch: '.$branchName.' ('.$bankName.')'
                ]);
                Session::flash("success", "bank branch has been deleted");
            }
        }catch (\Exception $ex){
            Session::flash("warning", "Unable to delete branch because it's being used in the system");
        }

        return redirect()->back();
    }

    public function bank_accounts() {
        return view('bank.bank_accounts');
    }

    public function bank_accountsDT() {
        $bankAccounts = BankAccount::all();
        return DataTables::of($bankAccounts)

            ->editColumn('owner', function ($bankAccounts) {
                if ($bankAccounts->type == "INDIVIDUAL")
                    return optional($bankAccounts->user)->surname.' '.optional($bankAccounts->user)->name;
                else
                    return optional($bankAccounts->company)->business_name;
            })

            ->editColumn('bank', function ($bankAccounts) {
                return optional($bankAccounts->bank)->bank_name.' ('.optional($bankAccounts->branch)->branch_name.')';
            })

            ->editColumn('atm_url', function ($bankAccounts) {
                return '<a href="' . $bankAccounts->atm_url . '"
                    class="btn btn-primary btn-link btn-sm" target="_blank">View ATM </a>';
            })

            ->editColumn('approved', function ($bankAccounts) {
                return $bankAccounts->approved ? 'YES' : 'NO';
            })

            ->addColumn('actions', function($bankAccounts){ // add custom column
                $actions = '<div class="align-content-center">';

                if ($bankAccounts->approved){
                    $actions .= '<form action="'. url('bank/accounts/disapprove') .'" method="post" style="display: inline;" class="disapprove-account-form">
                              '. csrf_field() .'<input type="hidden" name="id" value="'.$bankAccounts->id.'">
                              <button class="btn btn-warning btn-sm">Disapprove</button>
                             </form>';
                }else{
                    $actions .= '<form action="'. url('bank/accounts/approve') .'" method="post" style="display: inline;" class="approve-account-form">
                              '. csrf_field() .'<input type="hidden" name="id" value="'.$bankAccounts->id.'">
                              <button class="btn btn-success btn-sm">Approve</button>
                             </form>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions','atm_url'])

            ->make(true);

    }
    public function approve_account(Request  $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:bank_accounts,id',
        ]);

        $acc = BankAccount::find($request->id);
        $acc->approved = true;
        $acc->update();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Approved bank account: '.$acc->account_name.' ('.$acc->account_number.')'
        ]);
        Session::flash("success", "Bank account has been approved");

        return redirect()->back();
    }

    public function disapprove_account(Request  $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:bank_accounts,id',
        ]);

        $acc = BankAccount::find($request->id);
        $acc->approved = false;
        $acc->update();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Disapproved bank account: '.$acc->account_name.' ('.$acc->account_number.')'
        ]);
        Session::flash("success", "Bank account has been disapproved");

        return redirect()->back();
    }


}
