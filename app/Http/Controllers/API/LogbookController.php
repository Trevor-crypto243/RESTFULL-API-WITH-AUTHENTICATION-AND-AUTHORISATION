<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\GenericCollection;
use App\LogbookCompanyDirector;
use App\LogbookLoan;
use App\LogbookLoanVehicle;
use App\VehicleMake;
use App\VehicleModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Storage;

class LogbookController extends Controller
{
    public function makes()
    {
        return new GenericCollection(VehicleMake::orderBy('id', 'desc')->get());
    }

    public function models($make_id)
    {
        return new GenericCollection(VehicleModel::where('make_id',$make_id)->orderBy('id', 'desc')->get());
    }

    public function new_logbook_request(Request  $request)
    {
        $this->validate($request, [
            'applicant_type' =>'required',
            'requested_amount' =>'required|numeric',
            'payment_period' => 'required|numeric',
            'loan_purpose' => 'required',
        ]);

        $user = auth()->user();

        if ($request->applicant_type == "INDIVIDUAL"){
            $logbookLoan = new LogbookLoan();
            $logbookLoan->user_id = $user->id;
            $logbookLoan->applicant_type = $request->applicant_type;
            $logbookLoan->status = 'NEW';
            $logbookLoan->requested_amount = $request->requested_amount;
            $logbookLoan->payment_period = $request->payment_period;
            $logbookLoan->personal_kra_pin = $request->personal_kra_pin;
            $logbookLoan->loan_purpose = $request->loan_purpose;
            $logbookLoan->save();

            return response()->json([
                'success' => true,
                'message' => 'A new logbook loan application has been opened',
                'data' => $logbookLoan
            ], 200);

        }
        else{
            $logbookLoan = new LogbookLoan();
            $logbookLoan->user_id = $user->id;
            $logbookLoan->applicant_type = $request->applicant_type;
            $logbookLoan->status = 'NEW';
            $logbookLoan->requested_amount = $request->requested_amount;
            $logbookLoan->payment_period = $request->payment_period;
            $logbookLoan->loan_purpose = $request->loan_purpose;
            $logbookLoan->company_name = $request->company_name;
            $logbookLoan->directors = $request->directors;
            $logbookLoan->company_kra_pin = $request->company_kra_pin;
            $logbookLoan->company_reg_no = $request->company_reg_no;

            if ($request->has('company_kra_pin_file')){
                $kraPinFilePath = $request->file('company_kra_pin_file')->storePublicly('logbook_docs', 's3');
                $kraPinUrl = Storage::disk('s3')->url($kraPinFilePath);

                $logbookLoan->company_kra_pin_url = $kraPinUrl;
            }

            if ($request->has('company_reg_file')){
                $regFilePath = $request->file('company_reg_file')->storePublicly('logbook_docs', 's3');
                $regUrl = Storage::disk('s3')->url($regFilePath);

                $logbookLoan->company_reg_no_url = $regUrl;
            }



            $logbookLoan->save();

            return response()->json([
                'success' => true,
                'message' => 'A new logbook loan application has been opened. Please complete the application and submit for review.',
                'data' => $logbookLoan
            ], 200);
        }
    }

    public function logbook_applications()
    {
        return new GenericCollection(LogbookLoan::where('user_id',auth()->user()->id)->orderBy('id', 'desc')->get());
    }

    public function get_logbook_application_vehicles($application_id)
    {
        $logbookLoan = LogbookLoan::find($application_id);

        if ($logbookLoan->user_id != auth()->user()->id)
            return response()->json([
                'success' => false,
                'message' => 'You are not authorised to access this resource',
            ], 200);


        return new GenericCollection(LogbookLoanVehicle::where('logbook_loan_id',$application_id)->orderBy('id', 'desc')->get());
    }

    public function add_logbook_application_vehicle(Request  $request)
    {
        $this->validate($request, [
            'logbook_loan_id' =>'required|exists:logbook_loans,id',
            'vehicle_make_id' =>'required|exists:vehicle_makes,id',
            'vehicle_model_id' =>'required|exists:vehicle_models,id',
            'yom' => 'required|numeric',
            'reg_no' => 'required',
            'chassis_no' => 'required',
            'logbook_file' => 'required|file',
        ]);

        $user = auth()->user();


        $logbookLoan = LogbookLoan::find($request->logbook_loan_id);

        if ($logbookLoan->user_id != $user->id)
            return response()->json([
                'success' => false,
                'message' => 'You are not authorised to access this resource',
            ], 200);


        $logbookFilePath = $request->file('logbook_file')->storePublicly('logbooks', 's3');
        $url = Storage::disk('s3')->url($logbookFilePath);


        $regNo = strtoupper(preg_replace('/[^A-Za-z0-9]/', "", $request->reg_no));

        $applicationsIds = LogbookLoan::whereIn('status',['NEW','IN REVIEW','AMENDMENT','OFFER','ACTIVE'])->pluck('id');

        $vehicleExists = LogbookLoanVehicle::whereIn('logbook_loan_id',$applicationsIds)
            ->where('reg_no',$regNo)
            ->count();

        if ($vehicleExists > 0)
            return response()->json([
                'success' => false,
                'message' => 'This vehicle is already in use for another logbook loan application',
            ], 200);



        $logbookLoanVehicle = new LogbookLoanVehicle();
        $logbookLoanVehicle->logbook_loan_id = $request->logbook_loan_id;
        $logbookLoanVehicle->vehicle_make_id = $request->vehicle_make_id;
        $logbookLoanVehicle->vehicle_model_id = $request->vehicle_model_id;
        $logbookLoanVehicle->yom = $request->yom;
        $logbookLoanVehicle->reg_no = $regNo;
        $logbookLoanVehicle->chassis_no = $request->chassis_no;
        $logbookLoanVehicle->logbook_url = $url;
        $logbookLoanVehicle->save();


        return response()->json([
            'success' => true,
            'message' => 'Vehicle has been added successfully',
        ], 200);
    }

    public function upload_named_files(Request  $request)
    {
        $this->validate($request, [
            'logbook_loan_id' =>'required|exists:logbook_loans,id',
            'field_name' =>'required',
            'upload_file' => 'required|file',
        ]);

        $user = auth()->user();


        $logbookLoan = LogbookLoan::find($request->logbook_loan_id);

        if ($logbookLoan->user_id != $user->id)
            return response()->json([
                'success' => false,
                'message' => 'You are not authorised to access this resource',
            ], 200);


        if (!($logbookLoan->status == "NEW" || $logbookLoan->status == "AMENDMENT")){
            return response()->json([
                'success' => false,
                'message' => 'Can not upload more documents. Current application status is '.$logbookLoan->status,
            ], 200);
        }

        $docFilePath = $request->file('upload_file')->storePublicly('logbook_docs', 's3');
        $url = Storage::disk('s3')->url($docFilePath);


        switch ($request->field_name) {
            case 'id_back_url':
                $logbookLoan->id_back_url = $url;
                break;
            case 'id_front_url';
                $logbookLoan->id_front_url = $url;
                break;
            case 'passport_photo_url':
                $logbookLoan->passport_photo_url = $url;
                break;
            case 'personal_kra_pin_url':
                $logbookLoan->personal_kra_pin_url = $url;
                break;
        }
        $logbookLoan->update();


        return response()->json([
            'success' => true,
            'message' => 'File has been uploaded successfully',
        ], 200);
    }

    public function submit_for_review(Request  $request)
    {
        $this->validate($request, [
            'logbook_loan_id' =>'required|exists:logbook_loans,id',
        ]);

        $user = auth()->user();

        $logbookLoan = LogbookLoan::find($request->logbook_loan_id);

        if ($logbookLoan->user_id != $user->id){
            return response()->json([
                'success' => false,
                'message' => 'You are not authorised to access this resource',
            ], 200);
        }


        if (!($logbookLoan->status == "NEW" || $logbookLoan->status == "AMENDMENT")){
            return response()->json([
                'success' => false,
                'message' => 'Can not submit application. Current application status is '.$logbookLoan->status,
            ], 200);
        }


        //check conditions depending on applicant type
        if ($logbookLoan->applicant_type == "INDIVIDUAL"){

            $vehiclesCount = LogbookLoanVehicle::where('logbook_loan_id',$logbookLoan->id)->count();

            if ($vehiclesCount == 0)
                return response()->json([
                    'success' => false,
                    'message' => 'Please add at least 1 vehicle before submitting.',
                ], 200);


            if ($logbookLoan->id_front_url == null)
                return response()->json([
                    'success' => false,
                    'message' => 'Please upload a picture of your ID/passport before submitting.',
                ], 200);

            if ($logbookLoan->passport_photo_url == null)
                return response()->json([
                    'success' => false,
                    'message' => 'Please upload your selfie before submitting.',
                ], 200);

            if ($logbookLoan->personal_kra_pin_url == null)
                return response()->json([
                    'success' => false,
                    'message' => 'Please upload your KRA PIN certificate before submitting.',
                ], 200);


            $logbookLoan->status = "IN REVIEW";
            $logbookLoan->update();

            return response()->json([
                'success' => true,
                'message' => 'Application has been submitted. You will be contacted with more information on your loan offer.',
            ], 200);

        }elseif ($logbookLoan->applicant_type == "COMPANY"){

            $vehiclesCount = LogbookLoanVehicle::where('logbook_loan_id',$logbookLoan->id)->count();

            if ($vehiclesCount == 0)
                return response()->json([
                    'success' => false,
                    'message' => 'Please add at least 1 vehicle before submitting.',
                ], 200);


            if ($logbookLoan->company_reg_no == null)
                return response()->json([
                    'success' => false,
                    'message' => 'Please update your company to include registration number before submitting.',
                ], 200);


            if ($logbookLoan->directors < 1)
                return response()->json([
                    'success' => false,
                    'message' => 'Please update your company to include at least one director',
                ], 200);


            if ($logbookLoan->directors > $logbookLoan->company_directors()->count())
                return response()->json([
                    'success' => false,
                    'message' => 'Please add directors to match the declared company directors before submitting',
                ], 200);


            if ($logbookLoan->company_reg_no_url == null)
                return response()->json([
                    'success' => false,
                    'message' => 'Please edit your company and upload a registration certificate before submitting.',
                ], 200);

            if ($logbookLoan->company_kra_pin_url == null)
                return response()->json([
                    'success' => false,
                    'message' => 'Please edit your company and upload a KRA pin before submitting.',
                ], 200);


            $logbookLoan->status = "IN REVIEW";
            $logbookLoan->update();

            return response()->json([
                'success' => true,
                'message' => 'Application has been submitted. You will be contacted with more information on your loan offer.',
            ], 200);
        }else{
            return response()->json([
                'success' => true,
                'message' => 'Not individual or company. Work in progress...'
            ], 200);
        }

    }

    public function add_logbook_application_director(Request  $request)
    {
        $this->validate($request, [
            'logbook_loan_id' =>'required|exists:logbook_loans,id',
            'first_name' =>'required',
            'surname' =>'required',
            'id_no' =>'required',
            'id_front_file' => 'required|file',
            'id_back_file' => 'file',
            'passport_photo_file' => 'required|file',
            'kra_pin_file' => 'required|file',
        ]);

        $user = auth()->user();


        $logbookLoan = LogbookLoan::find($request->logbook_loan_id);

        if ($logbookLoan->user_id != $user->id)
            return response()->json([
                'success' => false,
                'message' => 'You are not authorised to access this resource',
            ], 200);


        $directorExists = LogbookCompanyDirector::where('logbook_loan_id',$request->logbook_loan_id)
            ->where('id_no', $request->id_no)->count();

        if ($directorExists > 0)
            return response()->json([
                'success' => false,
                'message' => 'This director has already been added for this application',
            ], 200);


        $idFrontFilePath = $request->file('id_front_file')->storePublicly('logbook_loan_documents', 's3');
        $idFrontUrl = Storage::disk('s3')->url($idFrontFilePath);


        $selfieFilePath = $request->file('passport_photo_file')->storePublicly('logbook_loan_documents', 's3');
        $selfieUrl = Storage::disk('s3')->url($selfieFilePath);

        $kraFilePath = $request->file('kra_pin_file')->storePublicly('logbook_loan_documents', 's3');
        $kraUrl = Storage::disk('s3')->url($kraFilePath);

        if ($request->has('id_back_file')){
            $idBackFilePath = $request->file('id_back_file')->storePublicly('logbook_loan_documents', 's3');
            $idBackUrl = Storage::disk('s3')->url($idBackFilePath);
        }


        $lgCompanyDirector = new LogbookCompanyDirector();
        $lgCompanyDirector->logbook_loan_id = $request->logbook_loan_id;
        $lgCompanyDirector->first_name = $request->first_name;
        $lgCompanyDirector->surname = $request->surname;
        $lgCompanyDirector->id_no = $request->id_no;
        $lgCompanyDirector->id_front_url = $idFrontUrl;

        if ($request->has('id_back_file'))
            $lgCompanyDirector->id_back_url = $idBackUrl;

        $lgCompanyDirector->passport_photo_url = $selfieUrl;
        $lgCompanyDirector->kra_pin_url = $kraUrl;
        $lgCompanyDirector->save();


        return response()->json([
            'success' => true,
            'message' => 'Company director has been added successfully',
        ], 200);
    }

    public function get_logbook_application_directors($application_id)
    {
        $logbookLoan = LogbookLoan::find($application_id);

        if ($logbookLoan->user_id != auth()->user()->id)
            return response()->json([
                'success' => false,
                'message' => 'You are not authorised to access this resource',
            ], 200);

        return new GenericCollection(LogbookCompanyDirector::where('logbook_loan_id',$application_id)->orderBy('id', 'desc')->get());
    }

    public function update_logbook_application_company(Request  $request)
    {
        $this->validate($request, [
            'logbook_loan_id' =>'required|exists:logbook_loans,id',
            'company_name' =>'required',
            'directors' =>'required|numeric',
            'company_kra_pin' => 'required',
            'company_kra_pin_file' => 'file',
            'company_reg_file' => 'file',
            'company_reg_no' => 'required',
        ]);

        $user = auth()->user();

        $logbookLoan = LogbookLoan::find($request->logbook_loan_id);

        if ($logbookLoan->user_id != $user->id)
            return response()->json([
                'success' => false,
                'message' => 'You are not authorised to access this resource',
            ], 200);


        $logbookLoan->company_name = $request->company_name;
        $logbookLoan->directors = $request->directors;
        $logbookLoan->company_kra_pin = $request->company_kra_pin;
        $logbookLoan->company_reg_no = $request->company_reg_no;

        if ($request->has('company_kra_pin_file')){
            $kraPinFilePath = $request->file('company_kra_pin_file')->storePublicly('logbook_loan_documents', 's3');
            $kraPinUrl = Storage::disk('s3')->url($kraPinFilePath);

            $logbookLoan->company_kra_pin_url = $kraPinUrl;
        }

        if ($request->has('company_reg_file')){
            $regFilePath = $request->file('company_reg_file')->storePublicly('logbook_loan_documents', 's3');
            $regUrl = Storage::disk('s3')->url($regFilePath);

            $logbookLoan->company_reg_no_url = $regUrl;
        }

        $logbookLoan->update();

        return response()->json([
            'success' => true,
            'message' => 'Company details have been updated successfully',
            'data' => $logbookLoan
        ], 200);

    }



}
