<?php

namespace App\Http\Controllers;

use App\AuditTrail;
use App\DashLogbookLoan;
use App\InterestRateMatrix;
use App\LoanProduct;
use App\LoanRequest;
use App\LoanRequestFee;
use App\LoanSchedule;
use App\LogbookDeduction;
use App\LogbookLoan;
use App\LogbookLoanAdditionalFile;
use App\LogbookLoanComment;
use App\LogbookLoanVehicle;
use App\Notifications\LogbookSubmittedForApproval;
use App\Notifications\LogbookSubmittedForReview;
use App\Repositories\Data;
use App\User;
use App\UserGroup;
use App\UserPermission;
use App\VehicleMake;
use App\VehicleModel;
use App\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Svg\Tag\Group;
use Yajra\DataTables\Facades\DataTables;

class LogbookController extends Controller
{
    private $_passed = false;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function get_models_json ($make_id){
        return json_encode(VehicleModel::where('make_id',$make_id)->get());
    }

    public function makes() {
        $makes  = VehicleMake::all();
        return view('auto.makes')->with([
            'makes'=>$makes
        ]);
    }

    public function create_make(Request $request)
    {
        $this->validate($request, [
            'make' => 'required',
        ],[
//            'phone_no.exists' => 'The phone number is not registered to any Quicksava account',
        ]);


        $exists = VehicleMake::where('make',$request->make)->first();

        if (is_null($exists)){
            $make = new VehicleMake();
            $make->make = $request->make;
            $make->save();


            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Created a new vehicle make:'.$request->make,
            ]);

            Session::flash("success", "Vehicle make has been created successfully");
        }else{
            Session::flash("warning", "A similar vehicle make already exists");
        }

        return redirect()->back();
    }
    public function makesDT() {
        $vehicleMakes = VehicleMake::all();
        return DataTables::of($vehicleMakes)

            ->editColumn('created_at', function ($vehicleMakes) {
                return Carbon::parse($vehicleMakes->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->editColumn('models', function ($vehicleMakes) {
                return $vehicleMakes->models()->count();
            })
            ->addColumn('actions', function($vehicleMakes){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<button source="' . route('vehicle-make-details' ,  $vehicleMakes->id) . '"
                    class="btn btn-primary btn-link btn-sm edit-make-btn" acs-id="'.$vehicleMakes->id .'">
                    <i class="material-icons">edit</i> Edit</button>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])

            ->make(true);

    }
    public function make_details($id)
    {
        $rslt = VehicleMake::find($id);
        return $rslt;
    }
    public function update_make(Request $request)
    {
        $data = request()->validate([
            'id' => 'required|exists:vehicle_makes,id',
            'make'  => 'required',
        ]);

        $exists = VehicleMake::where('make',$request->make)
            ->where('id','!=' ,$request->id)
            ->first();

        if (is_null($exists)){

            $make = VehicleMake::find($request->id);
            $make->make = $request->make;
            $make->update();

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Edited vehicle make of id #'.$request->id.' to '.$request->make,
            ]);

            request()->session()->flash('success', 'Vehicle make has been updated.');

        }else{
            request()->session()->flash('warning', 'A vehicle make of the same name already exists.');
        }
        return redirect()->back();
    }
    public function delete_make($id)
    {

        $vehicleMake = VehicleMake::find($id);
        $make = $vehicleMake->make;

        try {
            if ($vehicleMake->delete()){
                AuditTrail::create([
                    'created_by' => auth()->user()->id,
                    'action' => 'Deleted vehicle make: '.$make
                ]);
                Session::flash("success", "Vehicle make has been deleted");
            }
        }catch (\Exception $ex){
            Session::flash("warning", "Unable to delete vehicle make because it's being used int he system");
        }

        return redirect()->back();
    }


    public function create_model(Request $request)
    {
        $this->validate($request, [
            'make_id' => 'required|exists:vehicle_makes,id',
            'model' => 'required',
        ],[
//            'phone_no.exists' => 'The phone number is not registered to any Quicksava account',
        ]);


        $exists = VehicleModel::where('model',$request->model)
            ->where('make_id' ,$request->make_id)
            ->first();

        if (is_null($exists)){
            $model = new VehicleModel();
            $model->make_id = $request->make_id;
            $model->model = $request->model;
            $model->save();


            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Created a new vehicle model:'.$request->model,
            ]);

            Session::flash("success", "Vehicle model has been created successfully");
        }else{
            Session::flash("warning", "A similar vehicle make and model already exists");
        }

        return redirect()->back();
    }
    public function modelsDT() {
        $vehicleModels = VehicleModel::all();
        return DataTables::of($vehicleModels)

            ->editColumn('make', function ($vehicleModels) {
                return optional($vehicleModels->make)->make;
            })

            ->editColumn('created_at', function ($vehicleModels) {
                return Carbon::parse($vehicleModels->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })

            ->addColumn('actions', function($vehicleModels){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<button source="' . route('vehicle-model-details' ,  $vehicleModels->id) . '"
                    class="btn btn-primary btn-link btn-sm edit-model-btn" acs-id="'.$vehicleModels->id .'">
                    <i class="material-icons">edit</i> Edit</button>';


                $actions .= '<form action="'. route('delete-model',  $vehicleModels->id) .'" style="display: inline;" method="POST" class="delete-model-form">';
                $actions .= method_field('DELETE');
                $actions .= csrf_field() .'<button class="btn btn-danger btn-sm">Delete</button></form>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])
            ->make(true);

    }
    public function model_details($id)
    {
        $rslt = VehicleModel::find($id);
        return $rslt;
    }
    public function update_model(Request $request)
    {
        $data = request()->validate([
            'model_id' => 'required|exists:vehicle_models,id',
            'make_id'  => 'required',
            'model'  => 'required',
        ]);

        $exists = VehicleModel::where('model',$request->model)
            ->where('make_id' ,$request->make_id)
            ->where('id','!=' ,$request->model_id)
            ->first();

        if (is_null($exists)){

            $model = VehicleModel::find($request->model_id);
            $model->make_id = $request->make_id;
            $model->model = $request->model;
            $model->update();

            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Edited vehicle model of id #'.$request->model_id.' to '.$request->model,
            ]);

            request()->session()->flash('success', 'Vehicle model has been updated.');

        }else{
            request()->session()->flash('warning', 'A vehicle model of the same make and model already exists.');
        }
        return redirect()->back();
    }
    public function delete_model($id)
    {

        $vehicleModel = VehicleModel::find($id);
        $model = $vehicleModel->model;

        try {
            if ($vehicleModel->delete()){
                AuditTrail::create([
                    'created_by' => auth()->user()->id,
                    'action' => 'Deleted vehicle model: '.$model
                ]);
                Session::flash("success", "Vehicle model has been deleted");
            }
        }catch (\Exception $ex){
            Session::flash("warning", "Unable to delete vehicle model because it's being used int he system");
        }

        return redirect()->back();
    }


    public function logbook_applications() {
        return view('auto.applications')->with([
            'status'=>'NEW'
        ]);
    }

    public function add_applicant() {    
        $users = User::where('user_group',4)->get();
        return view('auto.add_applicant',['users'=>$users]);
    }

        
    public function add_applicant_details(Request $request) {
        try {
            $this->validate($request, [           
                'personal_kra_pin' => 'required',
                'requested_amount' => 'required',
                'payment_period' => 'required',
                'loan_purpose' => 'required',                 
                'selectedUser'=>'required',      
            ]);
           
            $user = User::where('name', $request->selectedUser)->first();

    
            $logbookLoan = new LogbookLoan();
            $logbookLoan->user_id = $user->id;
            $logbookLoan->applicant_type = "INDIVIDUAL";
            $logbookLoan->status = 'NEW';
            $logbookLoan->requested_amount = $request->requested_amount;
            $logbookLoan->payment_period = $request->payment_period;
            $logbookLoan->personal_kra_pin = $request->personal_kra_pin;
            $logbookLoan->loan_purpose = $request->loan_purpose;
            $logbookLoan->save();
    
            request()->session()->flash('success', 'Application Made Successfully');
    
            return redirect('/auto/applications');
        } catch (\Exception $e) {
            request()->session()->flash('error', 'An error occurred while adding applicant details: ' . $e->getMessage());
            return redirect('/auto/applications');
        }
    }
    


    public function logbook_applicationsDT($status) {

        $applications = DashLogbookLoan::where('status', $status)->get();

        return DataTables::of($applications)

            ->editColumn('client', function ($applications) {
                if ($applications->applicant_type == 'INDIVIDUAL'){
                    return optional($applications->user)->surname.' '.optional($applications->user)->name;
                }else{
                    return $applications->company_name;
                }
            })

            ->editColumn('requested_amount', function ($applications) {
                return number_format($applications->requested_amount,2);
            })

            ->editColumn('approved_amount', function ($applications) {
                return number_format($applications->approved_amount,2);
            })

            ->editColumn('payment_period', function ($applications) {
                return $applications->payment_period.' Months';
            })

            ->editColumn('vehicles', function ($applications) {
                return $applications->vehicles->count();
            })

            ->editColumn('created_at', function ($applications) {
                return Carbon::parse($applications->created_at)->isoFormat('MMM Do YYYY H:m:s');
            })



            ->addColumn('actions', function($applications){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . url('auto/applications' ,  $applications->id) . '"
                    class="btn btn-primary btn-link btn-sm edit-model-btn">
                    <i class="material-icons">visibility</i> View</a>';



                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])
            ->make(true);

    }


    public function logbook_application_details($application_id) {

        $application = DashLogbookLoan::findOrFail($application_id);

        $feesArray = array();
        $loanPrincipal = $application->approved_amount;


        if ($application->loan_product_id !=null){

            $periodInMonths = $application->payment_period;

            if ($periodInMonths == 0)
                $periodInMonths = 1;

            if($periodInMonths == 1)
                $period = '1_MONTH';
            elseif ($periodInMonths == 2)
                $period = '2_MONTHS';
            elseif ($periodInMonths > 2 && $periodInMonths <= 5)
                $period = '3_5_MONTHS';
            elseif ($periodInMonths > 5 && $periodInMonths <= 12)
                $period = '6_12_MONTHS';
            else
                $period = '12_PLUS_MONTHS';

            $isNew = !(LoanRequest::where('user_id', $application->user_id)
                    ->whereIn('repayment_status', ['PARTIALLY_PAID', 'PAID'])
                    ->count() > 0);

            $matrix = InterestRateMatrix::where('loan_period',$period)
                ->where('loan_product_id',$application->loan_product_id)
                ->first();


            if (is_null($matrix)){
                $interestRate = 'N/A';
                $amount_disbursable = 0;
                $upfrontFees = 0;
                $monthly_amount = 0;
                $amount_payable = 0;
            }
            else{
                if ($isNew)
                    $interestRate = $matrix->new_client_interest;
                else
                    $interestRate = $matrix->existing_client_interest;
            }


            $moreUpfront = LogbookDeduction::where('logbook_loan_id',$application->id)->where('type','UPFRONT')->sum('amount');
            $morePrincipal = LogbookDeduction::where('logbook_loan_id',$application->id)->where('type','ADD TO PRINCIPAL')->sum('amount');

            $loanPrincipal = $loanPrincipal+$morePrincipal;



            $loanProduct = LoanProduct::find($application->loan_product_id);

            if ($interestRate != 'N/A'){

                $upfrontFees = $moreUpfront;
                if ($loanProduct->fee_application == 'BEFORE DISBURSEMENT'){

                    //upfront fees
                    foreach ($loanProduct->fees as $fee) {
                        if ($fee->amount_type == 'PERCENTAGE'){
                            $amt = ($fee->amount/100 * $loanPrincipal);
                        }else{
                            $amt = $fee->amount;
                        }
                        $upfrontFees +=  $amt;

                        array_push($feesArray,["name"=>$fee->name,"amount"=>number_format($amt),"type"=>"UPFRONT"]);

                    }

                    //monthly fees
                    $interestAmount = ($interestRate/100) * $loanPrincipal;
                    array_push($feesArray,["name"=>"Interest - ".$interestRate." %","amount"=>number_format($interestAmount),"type"=>"ADD TO PRINCIPAL"]);

                    $carTrack = 2000;
                    array_push($feesArray,["name"=>"Car Track","amount"=>number_format($carTrack*$periodInMonths),"type"=>"ADD TO PRINCIPAL"]);


                    $loanMaintenance = (0.02 * $loanPrincipal) / $application->payment_period;
                    array_push($feesArray,["name"=>"Loan Maintenance (2%)","amount"=>number_format($loanMaintenance*$periodInMonths),"type"=>"ADD TO PRINCIPAL"]);

                    $principalAmount = $loanPrincipal / $application->payment_period;

                    $monthly_amount = $interestAmount+$carTrack+$loanMaintenance+$principalAmount;

                    $amount_disbursable = $loanPrincipal - $upfrontFees;

                    $amount_payable = $monthly_amount * $application->payment_period;

                }
                else{
                    //upfront fees
                    foreach ($loanProduct->fees as $fee) {
                        if ($fee->amount_type == 'PERCENTAGE'){
                            $amt = ($fee->amount/100 * $loanPrincipal);
                        }else{
                            $amt = $fee->amount;
                        }
                        $upfrontFees +=  $amt;

                        array_push($feesArray,["name"=>$fee->name,"amount"=>number_format($amt),"type"=>"UPFRONT"]);
                    }

                    $newPrincipal = $loanPrincipal + $upfrontFees;
                    //monthly fees
                    $interestAmount = ($interestRate/100) * $newPrincipal;
                    array_push($feesArray,["name"=>"Interest - ".$interestRate." %","amount"=>number_format($interestAmount),"type"=>"ADD TO PRINCIPAL"]);


                    $carTrack = 2000;
                    array_push($feesArray,["name"=>"Car Track","amount"=>number_format($carTrack*$periodInMonths),"type"=>"ADD TO PRINCIPAL"]);


                    $loanMaintenance = (0.02 * $newPrincipal) / $application->payment_period;
                    array_push($feesArray,["name"=>"Loan Maintenance (2%)","amount"=>number_format($loanMaintenance*$periodInMonths),"type"=>"ADD TO PRINCIPAL"]);


                    $principalAmount = $newPrincipal / $application->payment_period;

                    $monthly_amount = $interestAmount+$carTrack+$loanMaintenance+$principalAmount;

                    $amount_disbursable =  $loanPrincipal;

                    $amount_payable = $monthly_amount * $application->payment_period;

                }
            }else{
                $interestRate = 'N/A';
                $amount_disbursable = 0;
                $upfrontFees = $moreUpfront;
                $monthly_amount = 0;
                $amount_payable = 0;

                array_push($feesArray,["name"=>"Interest Rate","amount"=>$interestRate,"type"=>"UPFRONT"]);

            }

        }
        else{
            $interestRate = 'N/A';
            $amount_disbursable = 0;
            $upfrontFees = 0;
            $monthly_amount = 0;
            $amount_payable = 0;

            array_push($feesArray,["name"=>"Interest Rate","amount"=>$interestRate,"type"=>"UPFRONT"]);

        }


        $adminComments = LogbookLoanComment::where('logbook_loan_id', $application_id)->orderBy('id','desc')->get();
        return view('auto.logbook_application_details')->with([
            'application'=>$application,
            'interestRate'=>$interestRate,
            'adminComments'=>$adminComments,
            'upfront_fees'=>$upfrontFees,
            'monthly_amount'=>$monthly_amount,
            'amount_payable'=>$amount_payable,
            'amount_disbursable'=>$amount_disbursable,
            'feesArray'=>$feesArray,
            'loanPrincipal'=>$loanPrincipal
        ]);

    }
    public function logbook_application_vehiclesDT($application_id) {

        $vehicles = LogbookLoanVehicle::where('logbook_loan_id', $application_id)->get();
        return DataTables::of($vehicles)

            ->editColumn('make', function ($vehicles) {
                return optional($vehicles->make)->make;
            })

            ->editColumn('model', function ($vehicles) {
                return optional($vehicles->model)->model;
            })

            ->addColumn('actions', function($vehicles){ // add custom column
                $actions = '<div class="align-content-center">';

                $actions .= '<a href="' . $vehicles->logbook_url . '" target="_blank"
                    class="btn btn-info btn-link btn-sm">
                    <i class="material-icons">visibility</i> Logbook</a>';

                if (auth()->user()->role->has_perm([29])) {
                    $actions .= '<button source="' . route('edit-vehicle-details', $vehicles->id) . '"
                    class="btn btn-primary btn-link btn-sm edit-vehicle-btn" acs-id="' . $vehicles->id . '">
                    <i class="material-icons">edit</i> View/Edit</button>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])
            ->make(true);

    }

    public function add_deduction(Request $request)
    {
        $data = request()->validate([
            'id' => 'required',
            'deduction_name' => 'required',
            'amount' => 'required',
            'type' => 'required',
        ]);

        $logbookLoan = DashLogbookLoan::find($request->id);

        if (is_null($logbookLoan)){
            request()->session()->flash('warning', 'Invalid loan. Please try again');
            return redirect()->back();
        }

        $logbookLoanDeduction = new LogbookDeduction();
        $logbookLoanDeduction->logbook_loan_id = $request->id;
        $logbookLoanDeduction->deduction_name = $request->deduction_name;
        $logbookLoanDeduction->amount = $request->amount;
        $logbookLoanDeduction->type = $request->type;
        $logbookLoanDeduction->save();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Created a new deduction on logbook application with ID  ('.$request->id.'. Deduction: '.$request->deduction_name.'. Amount: '.number_format($request->amount),
        ]);

        request()->session()->flash('success', 'Deduction has been added.');
        return redirect()->back();
    }

    public function delete_deduction(Request $request)
    {
        $data = request()->validate([
            'id' => 'required|exists:logbook_deductions,id',
        ]);

        $logbookDedution = LogbookDeduction::find($request->id);

        if (is_null($logbookDedution)){
            request()->session()->flash('warning', 'Invalid deduction. Please try again');
            return redirect()->back();
        }

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Deleted a deduction on logbook application with ID  ('.$logbookDedution->logbook_loan_id.'. Deduction: '.$logbookDedution->deduction_name.'. Amount: '.number_format($logbookDedution->amount),
        ]);

        $logbookDedution->delete();

        request()->session()->flash('success', 'Deduction has been deleted.');
        return redirect()->back();
    }


    public function vehicle_details($id)
    {
        $vehicle = LogbookLoanVehicle::find($id);
        return $vehicle;
    }
    public function update_vehicle(Request $request)
    {
        $data = request()->validate([
            'id' => 'required',
            'reg_no' => 'required',
            'make_id' =>'required|exists:vehicle_makes,id',
            'model_id' =>'required|exists:vehicle_models,id',
            'yom' => 'required|numeric',
            'chassis_no' => 'required',
        ]);

        $logbookLoanVehicle = LogbookLoanVehicle::find($request->id);

        if (is_null($logbookLoanVehicle)){
            request()->session()->flash('warning', 'Invalid loan vehicle. Please try again');
            return redirect()->back();
        }

        $regNo = strtoupper(preg_replace('/[^A-Za-z0-9]/', "", $request->reg_no));

        $logbookLoanVehicle->reg_no = $regNo;
        $logbookLoanVehicle->vehicle_make_id = $request->make_id;
        $logbookLoanVehicle->vehicle_model_id = $request->model_id;
        $logbookLoanVehicle->yom = $request->yom;
        $logbookLoanVehicle->chassis_no = $request->chassis_no;
        $logbookLoanVehicle->insurance_company = $request->insurance_company;
        $logbookLoanVehicle->insurance_expiry_date = $request->insurance_expiry_date;
        $logbookLoanVehicle->premium_paid_by = $request->premium_paid_by;
        $logbookLoanVehicle->premium_amount_paid = $request->premium_amount_paid;
        $logbookLoanVehicle->forced_sale_value = $request->forced_sale_value;
        $logbookLoanVehicle->market_value = $request->market_value;
        $logbookLoanVehicle->valuation_date = $request->valuation_date;

        if ($request->hasFile('logbook_file')){
            //upload image to s3 here
            $path = $request->file('logbook_file')->storePublicly('logbooks', 's3');
            $logbookLoanVehicle->logbook_url = Storage::disk('s3')->url($path);
        }

        if ($request->hasFile('icf_file')){
            //upload image to s3 here
            $path = $request->file('icf_file')->storePublicly('logbook_docs', 's3');
            $logbookLoanVehicle->icf_confirmation_form_url = Storage::disk('s3')->url($path);
        }

        if ($request->hasFile('valuation_file')){
            //upload image to s3 here
            $path = $request->file('valuation_file')->storePublicly('logbook_docs', 's3');
            $logbookLoanVehicle->valuation_report_url = Storage::disk('s3')->url($path);
        }

        $logbookLoanVehicle->update();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Edited logbook vehicle ('.$regNo.') with ID '.$request->id,
        ]);

        request()->session()->flash('success', 'Vehicle has been updated.');

        return redirect()->back();
    }

    public function update_logbook_application(Request $request)
    {
        $data = request()->validate([
            'id' => 'required',
            'source_of_business' => 'required',
            'payment_mode' => 'required',
            'requested_amount' => 'required|numeric',
            'payment_period' => 'required|numeric',
            'loan_purpose' => 'required',
        ]);

        $logbookLoan = DashLogbookLoan::find($request->id);

        if (is_null($logbookLoan)){
            request()->session()->flash('warning', 'Invalid loan. Please try again');
            return redirect()->back();
        }

        if ($logbookLoan->applicant_type == 'INDIVIDUAL'){

            $logbookLoan->payment_mode = $request->payment_mode;
            $logbookLoan->source_of_business = $request->source_of_business;
            $logbookLoan->lead_originator = $request->lead_originator;
            $logbookLoan->loan_purpose = $request->loan_purpose;
            $logbookLoan->requested_amount = $request->requested_amount;
            $logbookLoan->payment_period = $request->payment_period;
            $logbookLoan->personal_kra_pin = $request->personal_kra_pin;
            $logbookLoan->loan_product_id = $request->loan_product_id;
            $logbookLoan->approved_amount = $request->approved_amount;


            if ($request->hasFile('id_back_file')){
                //upload image to s3 here
                $path = $request->file('id_back_file')->storePublicly('logbook_docs', 's3');
                $logbookLoan->id_back_url = Storage::disk('s3')->url($path);
            }

            if ($request->hasFile('id_front_file')){
                //upload image to s3 here
                $path = $request->file('id_front_file')->storePublicly('logbook_docs', 's3');
                $logbookLoan->id_front_url = Storage::disk('s3')->url($path);
            }

            if ($request->hasFile('kra_pin_file')){
                //upload image to s3 here
                $path = $request->file('kra_pin_file')->storePublicly('logbook_docs', 's3');
                $logbookLoan->personal_kra_pin_url = Storage::disk('s3')->url($path);
            }

            if ($request->hasFile('loan_form_file')){
                //upload image to s3 here
                $path = $request->file('loan_form_file')->storePublicly('logbook_docs', 's3');
                $logbookLoan->loan_form_url = Storage::disk('s3')->url($path);
            }


            if ($request->hasFile('offer_letter_file')){
                //upload image to s3 here
                $path = $request->file('offer_letter_file')->storePublicly('logbook_docs', 's3');
                $logbookLoan->offer_letter_url = Storage::disk('s3')->url($path);
            }

            $logbookLoan->update();


            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Edited logbook application from ('.optional($logbookLoan->user)->name.' '.optional($logbookLoan->user)->surname.') with ID '.$request->id,
            ]);

            request()->session()->flash('success', 'Loan application has been updated.');
            return redirect()->back();

        }
        else {

            $logbookLoan->company_name = $request->company_name;
            $logbookLoan->directors = $request->directors;
            $logbookLoan->company_kra_pin = $request->company_kra_pin;
            $logbookLoan->company_reg_no = $request->company_reg_no;
            $logbookLoan->payment_mode = $request->payment_mode;
            $logbookLoan->source_of_business = $request->source_of_business;
            $logbookLoan->lead_originator = $request->lead_originator;
            $logbookLoan->requested_amount = $request->requested_amount;
            $logbookLoan->payment_period = $request->payment_period;
            $logbookLoan->loan_purpose = $request->loan_purpose;
            $logbookLoan->loan_product_id = $request->loan_product_id;
            $logbookLoan->approved_amount = $request->approved_amount;


            if ($request->hasFile('company_kra_pin_file')){
                //upload image to s3 here
                $path = $request->file('company_kra_pin_file')->storePublicly('logbook_docs', 's3');
                $logbookLoan->company_kra_pin_url = Storage::disk('s3')->url($path);
            }

            if ($request->hasFile('company_reg_no_file')){
                //upload image to s3 here
                $path = $request->file('company_reg_no_file')->storePublicly('logbook_docs', 's3');
                $logbookLoan->company_reg_no_url = Storage::disk('s3')->url($path);
            }

            if ($request->hasFile('loan_form_file')){
                //upload image to s3 here
                $path = $request->file('loan_form_file')->storePublicly('logbook_docs', 's3');
                $logbookLoan->loan_form_url = Storage::disk('s3')->url($path);
            }


            if ($request->hasFile('offer_letter_file')){
                //upload image to s3 here
                $path = $request->file('offer_letter_file')->storePublicly('logbook_docs', 's3');
                $logbookLoan->offer_letter_url = Storage::disk('s3')->url($path);
            }

            $logbookLoan->update();


            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Edited logbook application from ('.$logbookLoan->company_name.') with ID '.$request->id,
            ]);

            request()->session()->flash('success', 'Loan application has been updated.');
            return redirect()->back();
        }
    }

    public function comment_on_logbook_application(Request $request)
    {
        $data = request()->validate([
            'id' => 'required',
            'comment' => 'required',
        ]);

        $logbookLoan = DashLogbookLoan::find($request->id);

        if (is_null($logbookLoan)){
            request()->session()->flash('warning', 'Invalid loan. Please try again');
            return redirect()->back();
        }

        $logbookLoanComment = new LogbookLoanComment();
        $logbookLoanComment->logbook_loan_id = $request->id;
        $logbookLoanComment->created_by = auth()->user()->id;
        $logbookLoanComment->comment = $request->comment;
        $logbookLoanComment->save();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Commented on logbook application with ID  ('.$request->id.'. Comment: '.$request->comment,
        ]);

        request()->session()->flash('success', 'Comment has been posted.');
        return redirect()->back();
    }

    public function upload_additional_file(Request $request)
    {
        $data = request()->validate([
            'id' => 'required',
            'additional_file' => 'required|file',
            'file_name' => 'required',
        ]);

        $logbookLoan = DashLogbookLoan::find($request->id);

        if (is_null($logbookLoan)){
            request()->session()->flash('warning', 'Invalid loan. Please try again');
            return redirect()->back();
        }


        $path = $request->file('additional_file')->storePublicly('logbook_docs', 's3');

        $addFile = new LogbookLoanAdditionalFile();

        $addFile->logbook_loan_id = $request->id;
        $addFile->file_name = $request->file_name;
        $addFile->created_by = auth()->user()->id;
        $addFile->file_url = Storage::disk('s3')->url($path);
        $addFile->save();

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Uploaded additional logbook loan file for application ID: '.$request->id,
        ]);

        request()->session()->flash('success', 'File has been uploaded successfully.');
        return redirect()->back();


    }

    public function submit_for_review(Request $request)
    {
        $data = request()->validate([
            'id' => 'required',
        ]);

        $logbookLoan = DashLogbookLoan::find($request->id);

        if (is_null($logbookLoan)){
            request()->session()->flash('warning', 'Invalid loan. Please try again');
            return redirect()->back();
        }


        //validate all required fields have been entered

        if ($logbookLoan->loan_product_id == null){
            request()->session()->flash('warning', 'Please select a LOAN PRODUCT before submitting for review.');
            return redirect()->back();
        }

        if ($logbookLoan->approved_amount == null){
            request()->session()->flash('warning', 'Please enter the APPROVED AMOUNT before submitting for review.');
            return redirect()->back();
        }

        if ($logbookLoan->loan_form_url == null){
            request()->session()->flash('warning', 'Please upload a signed LOAN FORM before submitting for review.');
            return redirect()->back();
        }

        if ($logbookLoan->offer_letter_url == null){
            request()->session()->flash('warning', 'Please upload a signed OFFER LETTER before submitting for review.');
            return redirect()->back();
        }

        $reject = false;
        $rejectMessage = "";

        foreach ($logbookLoan->vehicles as $vehicle){

            if ($vehicle->insurance_company == null){
                $reject = true;
                $rejectMessage =  'Please enter the INSURANCE COMPANY for '.$vehicle->reg_no.' before submitting for review';
                break;
            }

            if ($vehicle->insurance_expiry_date == null){
                $reject = true;
                $rejectMessage =  'Please enter the INSURANCE EXPIRY DATE for '.$vehicle->reg_no.' before submitting for review';
                break;
            }

            if ($vehicle->forced_sale_value == null){
                $reject = true;
                $rejectMessage =  'Please enter the FORCED SALE VALUE for '.$vehicle->reg_no.' before submitting for review';
                break;
            }

            if ($vehicle->market_value == null){
                $reject = true;
                $rejectMessage =  'Please enter the MARKET VALUE for '.$vehicle->reg_no.' before submitting for review';
                break;
            }

            if ($vehicle->valuation_report_url == null){
                $reject = true;
                $rejectMessage =  'Please upload the VALUATION REPORT for '.$vehicle->reg_no.' before submitting for review';
                break;
            }

            if ($vehicle->valuation_date == null){
                $reject = true;
                $rejectMessage =  'Please enter the VALUATION DATE for '.$vehicle->reg_no.' before submitting for review';
                break;
            }
        }


        if ($reject == true){
            request()->session()->flash('warning', $rejectMessage);
            return redirect()->back();
        }


        //update the application to submitted for review
        $logbookLoan->submitted_for_review_by = auth()->user()->id;
        $logbookLoan->update();


        //send email notifications to everyone with "submit for approval" permission
        $applicantName = $logbookLoan->applicant_type == 'INDIVIDUAL' ? $logbookLoan->user->surname.' '.$logbookLoan->user->name : $logbookLoan->company_name;
        $userPermissions = UserPermission::where('permission_id',34)->get();

        foreach ($userPermissions as $userPermission){
            //send to all members of group
            $groupMembers = User::where('user_group',$userPermission->group_id)->get();
            foreach ($groupMembers as $groupMember){
                //send the notification
                $groupMember->notify(new LogbookSubmittedForReview($applicantName,$logbookLoan->id));
            }
        }

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Submitted logbook loan for review. Application ID: '.$request->id,
        ]);

        request()->session()->flash('success', 'Application has been submitted for review successfully.');
        return redirect()->back();


    }

    public function submit_for_approval(Request $request)
    {
        $data = request()->validate([
            'id' => 'required',
        ]);

        $logbookLoan = DashLogbookLoan::find($request->id);

        if (is_null($logbookLoan)){
            request()->session()->flash('warning', 'Invalid loan. Please try again');
            return redirect()->back();
        }

        //validate all required fields have been entered

        if ($logbookLoan->loan_product_id == null){
            request()->session()->flash('warning', 'Please select a LOAN PRODUCT before submitting for review.');
            return redirect()->back();
        }

        if ($logbookLoan->approved_amount == null){
            request()->session()->flash('warning', 'Please enter the APPROVED AMOUNT before submitting for review.');
            return redirect()->back();
        }

        if ($logbookLoan->loan_form_url == null){
            request()->session()->flash('warning', 'Please upload a signed LOAN FORM before submitting for review.');
            return redirect()->back();
        }

        if ($logbookLoan->offer_letter_url == null){
            request()->session()->flash('warning', 'Please upload a signed OFFER LETTER before submitting for review.');
            return redirect()->back();
        }

        $reject = false;
        $rejectMessage = "";

        foreach ($logbookLoan->vehicles as $vehicle){

            if ($vehicle->insurance_company == null){
                $reject = true;
                $rejectMessage =  'Please enter the INSURANCE COMPANY for '.$vehicle->reg_no.' before submitting for review';
                break;
            }

            if ($vehicle->insurance_expiry_date == null){
                $reject = true;
                $rejectMessage =  'Please enter the INSURANCE EXPIRY DATE for '.$vehicle->reg_no.' before submitting for review';
                break;
            }

            if ($vehicle->forced_sale_value == null){
                $reject = true;
                $rejectMessage =  'Please enter the FORCED SALE VALUE for '.$vehicle->reg_no.' before submitting for review';
                break;
            }

            if ($vehicle->market_value == null){
                $reject = true;
                $rejectMessage =  'Please enter the MARKET VALUE for '.$vehicle->reg_no.' before submitting for review';
                break;
            }

            if ($vehicle->valuation_report_url == null){
                $reject = true;
                $rejectMessage =  'Please upload the VALUATION REPORT for '.$vehicle->reg_no.' before submitting for review';
                break;
            }

            if ($vehicle->valuation_date == null){
                $reject = true;
                $rejectMessage =  'Please enter the VALUATION DATE for '.$vehicle->reg_no.' before submitting for review';
                break;
            }
        }


        if ($reject == true){
            request()->session()->flash('warning', $rejectMessage);
            return redirect()->back();
        }


        $logbookLoan->submitted_for_approval_by = auth()->user()->id;
        $logbookLoan->update();

        //send email notifications to everyone with "approve" permission
        $applicantName = $logbookLoan->applicant_type == 'INDIVIDUAL' ? $logbookLoan->user->surname.' '.$logbookLoan->user->name : $logbookLoan->company_name;
        $userPermissions = UserPermission::where('permission_id',35)->get();

        foreach ($userPermissions as $userPermission){
            //send to all members of group
            $groupMembers = User::where('user_group',$userPermission->group_id)->get();
            foreach ($groupMembers as $groupMember){
                //send the notification
                $groupMember->notify(new LogbookSubmittedForApproval($applicantName,$logbookLoan->id));
            }
        }

        $superAdmins = User::where('user_group',1)->get();
        foreach ($superAdmins as $superAdmin){
            //send the notification
            $superAdmin->notify(new LogbookSubmittedForApproval($applicantName,$logbookLoan->id));
        }

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Submitted logbook loan for approval. Application ID: '.$request->id,
        ]);

        request()->session()->flash('success', 'Application has been submitted for approval successfully.');
        return redirect()->back();


    }

    public function reject_application(Request $request)
    {
        $data = request()->validate([
            'id' => 'required',
            'reject_reason' => 'required',
        ]);

        $logbookLoan = DashLogbookLoan::find($request->id);

        if (is_null($logbookLoan)){
            request()->session()->flash('warning', 'Invalid loan. Please try again');
            return redirect()->back();
        }

        $logbookLoan->status = 'REJECTED';
        $logbookLoan->reject_reason = $request->reject_reason;
        $logbookLoan->update();


        send_sms($logbookLoan->user->phone_no, "Your logbook loan request of Ksh. ".number_format($logbookLoan->requested_amount)." has been rejected. Open the Quicksava app to see the rejection reason.");


        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => 'Rejected logbook loan. Application ID: '.$request->id,
        ]);

        request()->session()->flash('success', 'Application has been rejected.');
        return redirect()->back();


    }

    public function approve_application(Request $request)
    {
        $data = request()->validate([
            'id' => 'required',
            'bank_id' => 'nullable|integer',
        ]);

        $logbookLoan = DashLogbookLoan::find($request->id);

        if (is_null($logbookLoan)){
            request()->session()->flash('warning', 'Invalid loan. Please try again');
            return redirect()->back();
        }

        if ($logbookLoan->loan_product_id == null){
            request()->session()->flash('warning', 'Please select a LOAN PRODUCT before submitting for review.');
            return redirect()->back();
        }

        if ($logbookLoan->approved_amount == null){
            request()->session()->flash('warning', 'Please enter the APPROVED AMOUNT before submitting for review.');
            return redirect()->back();
        }

        if ($logbookLoan->loan_form_url == null){
            request()->session()->flash('warning', 'Please upload a signed LOAN FORM before submitting for review.');
            return redirect()->back();
        }

        if ($logbookLoan->offer_letter_url == null){
            request()->session()->flash('warning', 'Please upload a signed OFFER LETTER before submitting for review.');
            return redirect()->back();
        }

        if (($request->payment_mode == 'PESALINK' | $request->payment_mode == 'EFT' | $request->payment_mode == 'RTGS') && is_null($request->bank_id)){
            request()->session()->flash('warning', 'Please select a bank account if payment mode is '.strtoupper($request->payment_mode));
            return redirect()->back();
        }

        $reject = false;
        $rejectMessage = "";

        foreach ($logbookLoan->vehicles as $vehicle){

            if ($vehicle->insurance_company == null){
                $reject = true;
                $rejectMessage =  'Please enter the INSURANCE COMPANY for '.$vehicle->reg_no.' before approving';
                break;
            }

            if ($vehicle->insurance_expiry_date == null){
                $reject = true;
                $rejectMessage =  'Please enter the INSURANCE EXPIRY DATE for '.$vehicle->reg_no.' before approving';
                break;
            }

            if ($vehicle->forced_sale_value == null){
                $reject = true;
                $rejectMessage =  'Please enter the FORCED SALE VALUE for '.$vehicle->reg_no.' before approving';
                break;
            }

            if ($vehicle->market_value == null){
                $reject = true;
                $rejectMessage =  'Please enter the MARKET VALUE for '.$vehicle->reg_no.' before approving';
                break;
            }

            if ($vehicle->valuation_report_url == null){
                $reject = true;
                $rejectMessage =  'Please upload the VALUATION REPORT for '.$vehicle->reg_no.' before approving';
                break;
            }

            if ($vehicle->icf_confirmation_form_url == null){
                $reject = true;
                $rejectMessage =  'Please upload the ICF CONFIRMATION FORM for '.$vehicle->reg_no.' before approving';
                break;
            }

            if ($vehicle->valuation_date == null){
                $reject = true;
                $rejectMessage =  'Please enter the VALUATION DATE for '.$vehicle->reg_no.' before approving';
                break;
            }
        }


        if ($reject == true){
            request()->session()->flash('warning', $rejectMessage);
            return redirect()->back();
        }


        //do approve

        $user = $logbookLoan->user;
        $periodInMonths = $logbookLoan->payment_period;


        if ($periodInMonths == 0)
            $periodInMonths = 1;

        if($periodInMonths == 1)
            $period = '1_MONTH';
        elseif ($periodInMonths == 2)
            $period = '2_MONTHS';
        elseif ($periodInMonths > 2 && $periodInMonths <= 5)
            $period = '3_5_MONTHS';
        elseif ($periodInMonths > 5 && $periodInMonths <= 12)
            $period = '6_12_MONTHS';
        else
            $period = '12_PLUS_MONTHS';

        $isNew = !(LoanRequest::where('user_id', $logbookLoan->user_id)
                ->whereIn('repayment_status', ['PARTIALLY_PAID', 'PAID'])
                ->count() > 0);

        $matrix = InterestRateMatrix::where('loan_period',$period)
            ->where('loan_product_id',$logbookLoan->loan_product_id)
            ->first();


        if (is_null($matrix)){
            $interestRate = 'N/A';
            $amount_disbursable = 0;
            $upfrontFees = 0;
            $monthly_amount = 0;
            $amount_payable = 0;
        }
        else{
            if ($isNew)
                $interestRate = $matrix->new_client_interest;
            else
                $interestRate = $matrix->existing_client_interest;
        }



        $loanProduct = LoanProduct::find($logbookLoan->loan_product_id);

        $moreUpfront = LogbookDeduction::where('logbook_loan_id',$logbookLoan->id)->where('type','UPFRONT')->sum('amount');
        $morePrincipal = LogbookDeduction::where('logbook_loan_id',$logbookLoan->id)->where('type','ADD TO PRINCIPAL')->sum('amount');


        $loanPrincipal = $logbookLoan->approved_amount +$morePrincipal;


        if ($interestRate != 'N/A'){

            $upfrontFees = $moreUpfront;

            $amount_disbursable = 0;


            $loan = new LoanRequest();
            $loan->user_id = $user->id;
            $loan->loan_product_id = $logbookLoan->loan_product_id;
            $loan->amount_requested = $loanPrincipal;
            $loan->amount_disbursable = $amount_disbursable;
            $loan->interest_rate = $interestRate;
            $loan->fees = $upfrontFees;
            $loan->approval_status="APPROVED";
            $loan->repayment_status="PENDING";
            $loan->period_in_months = $periodInMonths;
            $loan->approved_date = Carbon::now();
            $loan->saveOrFail();


            if ($loanProduct->fee_application == 'BEFORE DISBURSEMENT'){

                //upfront fees
                foreach ($loanProduct->fees as $fee) {
                    if ($fee->amount_type == 'PERCENTAGE'){
                        $amt = ($fee->amount/100 * $logbookLoan->approved_amount);
                    }else{
                        $amt = $fee->amount;
                    }

                    $type = $fee->amount_type == 'PERCENTAGE' ? $fee->name. " (".$fee->amount."%)" :  $fee->name;


                    $loanRequestFee = new LoanRequestFee();
                    $loanRequestFee->loan_request_id = $loan->id;
                    $loanRequestFee->fee = $type;
                    $loanRequestFee->amount = $amt;
                    $loanRequestFee->frequency = $fee->frequency;
                    $loanRequestFee->saveOrFail();

                    $upfrontFees +=  $amt;
                }

                //monthly fees
                $interestAmount = ($interestRate/100) * $loanPrincipal;
                $carTrack = 2000;
                $loanMaintenance = (0.02 * $loanPrincipal) / $logbookLoan->payment_period;
                $principalAmount = $loanPrincipal / $logbookLoan->payment_period;

                $monthly_amount = $interestAmount+$carTrack+$loanMaintenance+$principalAmount;

                $amount_disbursable =  $loanPrincipal - $upfrontFees;

                $amount_payable = $monthly_amount * $logbookLoan->payment_period;

                $loanRequestFee = new LoanRequestFee();
                $loanRequestFee->loan_request_id = $loan->id;
                $loanRequestFee->fee = "Car Tracking";
                $loanRequestFee->amount = 2000;
                $loanRequestFee->frequency = "MONTHLY";
                $loanRequestFee->saveOrFail();

                $loanRequestFee2 = new LoanRequestFee();
                $loanRequestFee2->loan_request_id = $loan->id;
                $loanRequestFee2->fee = "Loan Maintenance (2%)";
                $loanRequestFee2->amount = $loanMaintenance;
                $loanRequestFee2->frequency = "MONTHLY";
                $loanRequestFee2->saveOrFail();

            }
            else{
                //upfront fees
                foreach ($loanProduct->fees as $fee) {
                    if ($fee->amount_type == 'PERCENTAGE'){
                        $amt = ($fee->amount/100 * $loanPrincipal);
                    }else{
                        $amt = $fee->amount;
                    }

                    $type = $fee->amount_type == 'PERCENTAGE' ? $fee->name. " (".$fee->amount."%)" :  $fee->name;


                    $loanRequestFee = new LoanRequestFee();
                    $loanRequestFee->loan_request_id = $loan->id;
                    $loanRequestFee->fee = $type;
                    $loanRequestFee->amount = $amt;
                    $loanRequestFee->frequency = $fee->frequency;
                    $loanRequestFee->saveOrFail();

                    $upfrontFees +=  $amt;


                    $upfrontFees +=  $amt;
                }

                $newPrincipal = $loanPrincipal+ $upfrontFees;


                //monthly fees
                $interestAmount = ($interestRate/100) * $newPrincipal;
                $carTrack = 2000;
                $loanMaintenance = (0.02 * $newPrincipal) / $logbookLoan->payment_period;
                $principalAmount = $newPrincipal / $logbookLoan->payment_period;

                $monthly_amount = $interestAmount+$carTrack+$loanMaintenance+$principalAmount;

                $amount_disbursable =  $logbookLoan->approved_amount;

                $amount_payable = $monthly_amount * $logbookLoan->payment_period;

                $loanRequestFee = new LoanRequestFee();
                $loanRequestFee->loan_request_id = $loan->id;
                $loanRequestFee->fee = "Car Tracking";
                $loanRequestFee->amount = 2000;
                $loanRequestFee->frequency = "MONTHLY";
                $loanRequestFee->saveOrFail();

                $loanRequestFee2 = new LoanRequestFee();
                $loanRequestFee2->loan_request_id = $loan->id;
                $loanRequestFee2->fee = "Loan Maintenance (2%)";
                $loanRequestFee2->amount = $loanMaintenance;
                $loanRequestFee2->frequency = "MONTHLY";
                $loanRequestFee2->saveOrFail();

            }


            $loan->amount_disbursable = $amount_disbursable;
            $loan->update();

            $logbookLoan->loan_request_id = $loan->id;
            $logbookLoan->status = 'ACTIVE';
            $logbookLoan->payment_mode = $request->payment_mode;
            $logbookLoan->update();




            //create loan schedule

            $todaysDate = Carbon::now()->isoFormat('D');
            Log::info("todays date....".$todaysDate);

            $closingDate = 15;

            if ($todaysDate >= $closingDate){
                //first installment is end of next month
                $firstInstallmentdate = Carbon::now()->addMonthNoOverflow()->day($closingDate)->toDateString();
            }else{
                //first installment is end of this month
                $firstInstallmentdate = Carbon::now()->day($closingDate)->toDateString();
            }
            Log::info("firstInstallmentdate....".$firstInstallmentdate);



            $month = 1;
            $beginningBalance = $amount_payable;
            while($month <= $logbookLoan->payment_period) {

                Log::info("while month....".$month);

                $interestPaid = ceil($monthly_amount - $principalAmount);
                $principalPaid = ceil($principalAmount);

                $loanSchedule = new LoanSchedule();
                $loanSchedule->loan_request_id = $loan->id;
                $loanSchedule->payment_date = $firstInstallmentdate;
                $loanSchedule->beginning_balance = ceil($beginningBalance);
                $loanSchedule->scheduled_payment = ceil($monthly_amount);
                $loanSchedule->interest_paid = $interestPaid;
                $loanSchedule->principal_paid = $principalPaid;
                $loanSchedule->ending_balance = ceil($beginningBalance - $monthly_amount);
                $loanSchedule->saveOrFail();

                $firstInstallmentdate = Carbon::parse($firstInstallmentdate)->addMonthNoOverflow();
                $month++;
                $beginningBalance -= $monthly_amount;
            }

            //move to wallet

            $wallet = $logbookLoan->user->wallet;

            $prevBal = $wallet->current_balance;
            $newBal = $prevBal+$amount_disbursable;

            $wallet->current_balance = $newBal;
            $wallet->previous_balance = $prevBal;
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
            $walletTransaction->narration ="Logbook loan approved";
            $walletTransaction->saveOrFail();


            //TODO withdrawal














            send_sms($user->phone_no, "Your logbook loan request of Ksh. ".number_format($logbookLoan->requested_amount)." has been approved. KES ".number_format($amount_disbursable)." has been deposited to your wallet. Open the Quicksava app to confirm and withdraw.");

            request()->session()->flash('success', 'Request has been approved');


            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Approved logbook loan. Application ID: '.$request->id,
            ]);

            return redirect()->back();

        }else{
            $interestRate = 'N/A';
            $amount_disbursable = 0;
            $upfrontFees = 0;
            $monthly_amount = 0;
            $amount_payable = 0;

            request()->session()->flash('warning', 'Loan interest could not be found. Request has NOT been approved');
            return redirect()->back();

        }


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
