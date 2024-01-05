<?php

namespace App\Http\Controllers\API;

use App\CustomerProfile;
use App\Employee;
use App\EmployeeIncome;
use App\Employer;
use App\EmployerLoanProduct;
use App\Http\Controllers\Controller;
use App\Http\Resources\GenericCollection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    public function employers()
    {
        return new GenericCollection(Employer::orderBy('id', 'desc')->where('salary_advance', true)->get());
    }

    public function my_employers()
    {
        return new GenericCollection(Employee::where('user_id',auth()->user()->id)->orderBy('id', 'desc')->get());
    }

    public function invoice_merchants()
    {
        return new GenericCollection(Employer::orderBy('id', 'desc')->where('invoice_discounting', true)->get());
    }


    public function add_my_employer(Request  $request)
    {
        $data = request()->validate([
            'position'  => 'required',
            'nature_of_work'  => 'required',
            'payroll_no'  => 'required',
            'employer_id'  => 'required',
            'selfie_image'  => 'required',
            'id_image'  => 'required',
            'payslip_file'  => 'required',
            'location'  => 'required',
            'id_back_image'  => 'required',
        ]);

        $exists = Employee::where('user_id', auth()->user()->id)
            ->where('employer_id', $request->employer_id)
            ->first();

        if (is_null($exists)){
            //continue

            $income = EmployeeIncome::where('employer_id', $request->employer_id)
                ->where('payroll_no',$request->payroll_no)
                ->where('id_no', auth()->user()->id_no)
                ->first();

            if (is_null($income)){
                return response()->json([
                    'success' => false,
                    'message' => "We could not find your profile under this employer with the details provided. Please contact our Customer Care via 0700000000 for assistance."
                ], 200);
            }else{

                DB::transaction(function() use ($income,$request) {
                    $selfie_imageFilePath = $request->file('selfie_image')->storePublicly('selfies', 's3');
                    $id_imageFilePath = $request->file('id_image')->storePublicly('id_photos', 's3');
                    $id_back_imageFilePath = $request->file('id_back_image')->storePublicly('id_photos', 's3');
                    $payslipFilePath = $request->file('payslip_file')->storePublicly('payslips', 's3');


                    $employee = new Employee();
                    $employee->user_id = auth()->user()->id;
                    $employee->employer_id = $request->employer_id;
                    $employee->payroll_no = $request->payroll_no;
                    $employee->location = $request->location;

                    $employee->id_url = Storage::disk('s3')->url($id_imageFilePath);
                    $employee->id_filename = basename($id_imageFilePath);

                    $employee->id_back_url = Storage::disk('s3')->url($id_back_imageFilePath);
                    $employee->id_back_name = basename($id_back_imageFilePath);

                    $employee->passport_photo_url = Storage::disk('s3')->url($selfie_imageFilePath);
                    $employee->passport_photo_filename = basename($selfie_imageFilePath);

                    $employee->latest_payslip_url = Storage::disk('s3')->url($payslipFilePath);
                    $employee->latest_payslip_filename = basename($payslipFilePath);

                    $employee->limit_expiry = Carbon::now()->addMonths(6);
                    $employee->nature_of_work = $request->nature_of_work;
                    $employee->position = $request->position;


//                if (is_null($income)){
//                    $employee->employment_date = Carbon::now();
//                    $employee->saveOrFail();
//                }else{
//                    $employee->employment_date = $income->employment_date;
//                    $employee->gross_salary = $income->gross_salary;
//                    $employee->basic_salary = $income->basic_salary;
//                    $employee->net_salary = $income->net_salary;
//                    $employee->max_limit = $income->net_salary - (0.33 * $income->basic_salary);
//                    $employee->saveOrFail();
//                }

                    $limit = $income->net_salary - $income->basic_salary/3;

                    $employee->employment_date = $income->employment_date;
                    $employee->gross_salary = $income->gross_salary;
                    $employee->basic_salary = $income->basic_salary;
                    $employee->net_salary = $income->net_salary;
                    $employee->max_limit = $limit >= 0 ? $limit : 0;
                    $employee->saveOrFail();


                    $custProfile = CustomerProfile::where('user_id', auth()->user()->id)->first();

                    if (!is_null($custProfile)){
                        $custProfile->is_checkoff = true;
                        $custProfile->update();
                    }

                });
                return response()->json([
                    'success' => true,
                    'message' => "Application has been received and processed successfully. You can now apply for salary advance"
                ], 200);
            }

        }else{

            return response()->json([
                'success' => false,
                'message' => "You are already registered under this employer. Please proceed to Salary Advance for loan application"
            ], 200);

        }




    }

    public function get_employer_loan_products($employer_id)
    {
        $products = EmployerLoanProduct::where('employer_id',$employer_id)->orderBy('id','desc')->get();
        return new GenericCollection($products);
    }
}
