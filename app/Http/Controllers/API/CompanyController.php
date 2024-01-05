<?php

namespace App\Http\Controllers\API;

use App\Company;
use App\CompanyDirector;
use App\CompanyManager;
use App\CustomerProfile;
use App\Employer;
use App\Http\Controllers\Controller;
use App\Http\Resources\GenericCollection;
use App\Http\Resources\GenericResource;
use App\InterestRateMatrix;
use App\Invoice;
use App\InvoiceDiscount;
use App\LoanProduct;
use App\LoanRequest;
use App\LoanRequestFee;
use App\LoanSchedule;
use App\MpesaCharge;
use App\Notifications\NewIdfApplication;
use App\User;
use App\Wallet;
use App\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class CompanyController extends Controller
{
    public function companies()
    {
        return new GenericCollection(CompanyManager::where('user_id',auth()->user()->id)->orderBy('id', 'desc')->get());
    }


    public function get_company_directors($company_id)
    {
        return new GenericCollection(CompanyDirector::where('company_id',$company_id)->orderBy('id', 'desc')->get());
    }

    public function add_company(Request  $request)
    {
        $data = request()->validate([
            'type'  => 'required',
            'business_name'  => 'required',
            'reg_no'  => 'required',
            'directors'  => 'required',
            'tax_pin'  => 'required',
            'business_registration_date'  => 'required',
            'tax_pin_file'  => 'required',
            'irrevocable_file'  => 'required',
            'registration_certificate_file'  => 'required',
        ],[
            'reg_no.required' => 'The company/business registration number is required'
        ]);

        $exists = Company::where('reg_no', $request->reg_no)->first();

        if (!is_null($exists))
            return response()->json([
                'success' => false,
                'message' => "A company/business with a similar registration number is already registered",
            ], 200);



        DB::transaction(function() use ($request) {
            $tax_pin_fileFilePath = $request->file('tax_pin_file')->storePublicly('tax_certificates', 's3');
            $irrevocable_fileFilePath = $request->file('irrevocable_file')->storePublicly('letters', 's3');
            $registration_certificate_fileFilePath = $request->file('registration_certificate_file')->storePublicly('reg_certificates', 's3');

            $articles_fileFilePath= null;
            $cr12_fileFilePath= null;

            if ($request->type == 'LIMITED COMPANY'){
                $articles_fileFilePath = $request->file('articles_file')->storePublicly('articles_of_association', 's3');
                $cr12_fileFilePath = $request->file('cr12_file')->storePublicly('cr12', 's3');
                $resolution_fileFilePath = $request->file('resolution_file')->storePublicly('resolutions', 's3');
            }

            $wallet = new Wallet();
            $wallet->current_balance = 0;
            $wallet->previous_balance = 0;
            $wallet->saveOrFail();

            $company = new Company();
            $company->wallet_id = $wallet->id;
            $company->owner_id = auth()->user()->id;
            $company->type = $request->type;
            $company->business_name = $request->business_name;
            $company->reg_no = $request->reg_no;
            $company->directors = $request->directors;
            $company->tax_pin = $request->tax_pin;
            $company->business_registration_date = $request->business_registration_date;
            $company->tax_compliance_url = 'N/A';

            $company->tax_pin_url = Storage::disk('s3')->url($tax_pin_fileFilePath);
            $company->irrevocable_letter = Storage::disk('s3')->url($irrevocable_fileFilePath);

            $company->registration_certificate_url = Storage::disk('s3')->url($registration_certificate_fileFilePath);
            if ($request->type == 'LIMITED COMPANY'){
                $company->articles_url = Storage::disk('s3')->url($articles_fileFilePath);
                $company->cr12_url = Storage::disk('s3')->url($cr12_fileFilePath);
                $company->board_resolution = Storage::disk('s3')->url($resolution_fileFilePath);
            }

            $company->saveOrFail();

            $companyManager = new CompanyManager();
            $companyManager->user_id = auth()->user()->id;
            $companyManager->company_id = $company->id;
            $companyManager->saveOrFail();

        });

        $company = Company::where('owner_id', auth()->user()->id)->orderBy('id','desc')->first();

        if (is_null($company)){
            return response()->json([
                'success' => false,
                'message' => "A fatal error occurred when creating company/business. Please try again",
            ], 200);
        }else{
            return response()->json([
                'success' => true,
                'message' => "Company/business has been registered successfully. Please upload director details to complete registration",
                'data' => $company
            ], 200);
        }



    }

    public function upload_company_letter(Request  $request)
    {
        $data = request()->validate([
            'company_id'  => 'required',
            'irrevocable_file'  => 'required',
        ]);

        $exists = Company::find($request->company_id);

        if (is_null($exists))
            return response()->json([
                'success' => false,
                'message' => "Company does not exist. Please contact system admin",
            ], 200);


        DB::transaction(function() use ($request) {
            $irrevocable_fileFilePath = $request->file('irrevocable_file')->storePublicly('letters', 's3');

            $company = Company::find($request->company_id);
            $company->irrevocable_letter = Storage::disk('s3')->url($irrevocable_fileFilePath);
            $company->update();

        });

        return response()->json([
            'success' => true,
            'message' => "Letter of irrevocable instructions has been uploaded successfully.",
        ], 200);

    }

    public function add_company_director(Request  $request)
    {
        $data = request()->validate([
            'company_id'  => 'required',
            'name'  => 'required',
            'id_no'  => 'required',
            'tax_pin'  => 'required',

            'id_front_file'  => 'required',
            'selfie_image_file'  => 'required',
            'tax_pin_file'  => 'required',
        ]);

        $company = Company::find($request->company_id);
        if (is_null($company))
            abort(404,"Company not found");

        DB::transaction(function() use ($request, $company) {
            $tax_pin_fileFilePath = $request->file('tax_pin_file')->storePublicly('tax_certificates', 's3');
            $selfie_image_fileFilePath = $request->file('selfie_image_file')->storePublicly('selfies', 's3');
            $id_front_fileFilePath = $request->file('id_front_file')->storePublicly('id_photos', 's3');

            if ($request->id_back_file !=null)
                $id_back_fileFilePath = $request->file('id_back_file')->storePublicly('id_photos', 's3');
            else
                $id_back_fileFilePath = null;


            $companyDirector = new CompanyDirector();
            $companyDirector->company_id = $request->company_id;
            $companyDirector->name = $request->name;
            $companyDirector->id_no = $request->id_no;
            $companyDirector->phone_no = $request->phone_no;
            $companyDirector->tax_pin = $request->tax_pin;

            $companyDirector->id_front_url = Storage::disk('s3')->url($id_front_fileFilePath);

            if ($id_back_fileFilePath!=null)
                $companyDirector->id_back_url = Storage::disk('s3')->url($id_back_fileFilePath);

            $companyDirector->tax_pin_url = Storage::disk('s3')->url($tax_pin_fileFilePath);
            $companyDirector->passport_photo_url = Storage::disk('s3')->url($selfie_image_fileFilePath);


            $companyDirector->saveOrFail();

            //check if complete directors
            $count = CompanyDirector::where('company_id', $request->company_id)->count();

            if ($count >= $company->directors && $company->status  == "INCOMPLETE" ){
                $company->status = "ACTIVE";
                $company->update();
            }

        });

        return response()->json([
            'success' => true,
            'message' => "Company director has been registered successfully",
            'data' => $company
        ], 200);


    }

    public function apply_invoice_discount(Request  $request)
    {
        $data = request()->validate([
            'company_id'  => 'required',
            'employer_id'  => 'required',
        ]);

        $company = Company::find($request->company_id);
        if (is_null($company))
            abort(404,"Company not found");

        $employer = Employer::find($request->employer_id);
        if (is_null($employer))
            abort(404,"Merchant  not found");


        $user = auth()->user();

        $customerProfile = CustomerProfile::where('user_id',$user->id)->first();

        if (is_null($customerProfile))
            return response()->json([
                'success' => false,
                'message' => 'Profile not found. Please contact customer service for assistance.',
            ], 200);


        if ($customerProfile->status != 'active')
            return response()->json([
                'success' => false,
                'message' => 'Unable to apply for invoice discounting loan. Your profile is '.$customerProfile->status,
            ], 200);


        $invoiceDiscount = new InvoiceDiscount();

        DB::transaction(function() use ($request, $company, $employer, $invoiceDiscount) {


            $invoiceDiscount->company_id = $request->company_id;
            $invoiceDiscount->employer_id = $request->employer_id;
            $invoiceDiscount->created_by = auth()->user()->id;


            $invoiceDiscount->agent_code = $request->has('agent_code') ? $request->agent_code : "N/A";

            $invoiceDiscount->contract_link = 'N/A';
            $invoiceDiscount->irrevocable_letter_link = 'N/A';
//            $invoiceDiscount->irrevocable_letter_link = Storage::disk('s3')->url($irrevocable_letter_fileFilePath);

            $invoiceDiscount->saveOrFail();
        });

        return response()->json([
            'success' => true,
            'message' => "Application has been opened, please upload invoices to continue",
            'data' => $invoiceDiscount
        ], 200);


    }

    public function add_invoice(Request  $request)
    {
        $data = request()->validate([
            'invoice_discount_id'  => 'required',
            'invoice_number'  => 'required',
            'invoice_date'  => 'required',
            'invoice_amount'  => 'required',
//            'invoice_file'  => 'required',
        ]);

        $invoiceDiscount = InvoiceDiscount::find($request->invoice_discount_id);
        if (is_null($invoiceDiscount))
            abort(404,"Invoice discount not found");

        if ($invoiceDiscount->created_by != auth()->user()->id)
            return response()->json([
                'success' => false,
                'message' => "You are not authorised to access this resource",
            ], 200);

        $exists = Invoice::where('invoice_discount_id',$request->invoice_discount_id)
            ->where('invoice_number', $request->invoice_number)
            ->first();
        if (!is_null($exists))
            return response()->json([
                'success' => false,
                'message' => "You have already uploaded an invoice with this invoice number",
            ], 200);

        $exists2 = Invoice::where('invoice_number', $request->invoice_number)
            ->whereIn('approval_status',['PENDING','APPROVED'])
            ->get();

        $abort = 0;
        foreach ($exists2 as $exists2A){
            if ($exists2A->invoice_discount->company_id == $invoiceDiscount->company_id)
                $abort = 1;
        }

        if ($abort == 1){
            return response()->json([
                'success' => false,
                'message' => "This invoice has either been approved or is pending approval",
            ], 200);
        }

        DB::transaction(function() use ($request) {
//            $invoice_fileFilePath = $request->file('invoice_file')->storePublicly('invoices', 's3');


            $invoice = new Invoice();
            $invoice->invoice_discount_id = $request->invoice_discount_id;
            $invoice->invoice_number = $request->invoice_number;
            $invoice->invoice_amount = $request->invoice_amount;
            $invoice->invoice_date = $request->invoice_date;
            if ($request->has('invoice_branch'))
                $invoice->invoice_branch =  $request->invoice_branch;
            if ($request->has('grn_no'))
                $invoice->grn_no =  $request->grn_no;
            if ($request->has('delivery_note_no'))
                $invoice->delivery_note_no =  $request->delivery_note_no;
            if ($request->has('lpo_no'))
                $invoice->lpo_no =  $request->lpo_no;

            $invoice->invoice_link = "N/A";// Storage::disk('s3')->url($invoice_fileFilePath);
            $invoice->saveOrFail();
        });

        return response()->json([
            'success' => true,
            'message' => "Invoice has been uploaded successfully",
        ], 200);


    }

    public function edit_invoice(Request  $request)
    {
        $data = request()->validate([
            'invoice_discount_id'  => 'required',
            'invoice_number'  => 'required',
            'invoice_date'  => 'required',
            'invoice_amount'  => 'required',
            'invoice_id'  => 'required',
        ]);

        $invoiceDiscount = InvoiceDiscount::find($request->invoice_discount_id);
        if (is_null($invoiceDiscount))
            abort(404,"Invoice discount not found");


        $invoice = Invoice::find($request->invoice_id);
        if (is_null($invoice))
            abort(404,"Invoice not found");



        $exists = Invoice::where('invoice_discount_id',$request->invoice_discount_id)
            ->where('invoice_number', $request->invoice_number)
            ->whereNotIn('id', [$invoice->id])
            ->first();
        if (!is_null($exists))
            return response()->json([
                'success' => false,
                'message' => "You have already uploaded an invoice with this invoice number",
            ], 200);



        $exists2 = Invoice::where('invoice_number', $request->invoice_number)
            ->whereIn('approval_status',['PENDING','APPROVED'])
            ->whereNotIn('id', [$invoice->id])
            ->get();

        $abort = 0;
        foreach ($exists2 as $exists2A){
            if ($exists2A->invoice_discount->company_id == $invoiceDiscount->company_id)
                $abort = 1;
        }

        if ($abort == 1){
            return response()->json([
                'success' => false,
                'message' => "This invoice has either been approved or is pending approval",
            ], 200);
        }

        $invoice->invoice_number = $request->invoice_number;
        $invoice->invoice_amount = $request->invoice_amount;
        $invoice->invoice_date = $request->invoice_date;
        if ($request->has('invoice_branch'))
            $invoice->invoice_branch =  $request->invoice_branch;
        if ($request->has('grn_no'))
            $invoice->grn_no =  $request->grn_no;
        if ($request->has('delivery_note_no'))
            $invoice->delivery_note_no =  $request->delivery_note_no;
        if ($request->has('lpo_no'))
            $invoice->lpo_no =  $request->lpo_no;
        $invoice->update();

        return response()->json([
            'success' => true,
            'message' => "Invoice has been updated successfully",
        ], 200);


    }

    public function submit_for_review(Request  $request)
    {
        $data = request()->validate([
            'invoice_discount_id'  => 'required',
        ]);

        $invoiceDiscount = InvoiceDiscount::find($request->invoice_discount_id);
        if (is_null($invoiceDiscount))
            abort(404,"Invoice discount not found");

        if ($invoiceDiscount->created_by != auth()->user()->id)
            return response()->json([
                'success' => false,
                'message' => "You are not allowed to access this resource, please contact system admin",
            ], 200);

        $invoiceDiscount->submitted = true;
        $invoiceDiscount->update();

        //notify all Quicksava admins
        foreach (User::where('user_group',1)->orWhere('user_group',3)->orWhere('user_group',6)->get() as $notifiable){
            $notifiable->notify(new NewIdfApplication($invoiceDiscount->company->business_name));
        }

        return response()->json([
            'success' => true,
            'message' => "Invoice application has been submitted successfully. You will get a notification once it's approved",
        ], 200);

    }

    public function get_id_invoices($invoice_discount_id)
    {
        return new GenericCollection(Invoice::where('invoice_discount_id',$invoice_discount_id)->orderBy('id', 'desc')->get());
    }

    public function get_invoice_discount_details($invoice_discount_id)
    {
        $id = InvoiceDiscount::find($invoice_discount_id);

        if (is_null($id))
            return response()->json([
                'success' => false,
                'message' => "Requested invoice discount does not exist"
            ], 200);
        else
            return new GenericResource($id);

    }

    public function get_company_details($company_id)
    {
        $company = Company::find($company_id);

        if (is_null($company))
            return response()->json([
                'success' => false,
                'message' => "Requested company does not exist"
            ], 200);
        else
            return new GenericResource($company);

    }

    public function get_company_invoice_discounts($company_id)
    {
        return new GenericCollection(InvoiceDiscount::where('company_id',$company_id)->orderBy('id', 'desc')->paginate(20));
    }

    public function calculate_invoice_discount(Request $request){

        $this->validate($request, [
            'invoice_discount_id' =>'required|exists:invoice_discounts,id',
        ]);

        $invoiceDiscount = InvoiceDiscount::find($request->invoice_discount_id);

        $user = User::find($invoiceDiscount->created_by);
        $loanProduct = LoanProduct::find($invoiceDiscount->loan_product_id);

        if (is_null($loanProduct))
            return response()->json([
                'success' => false,
                'message' => 'Loan product has not been assigned',
                'errors' => 'Please contact system administrator to approve your Invoice discount',
            ], 200);



        $responseArray = array();
        $feesArray = array();

//        DB::transaction(function() use($request, $loanProduct, $user) {

        $todaysDate = Carbon::now()->isoFormat('D');
        Log::info("todays date....".$todaysDate);


        $this_month = Carbon::parse($invoiceDiscount->expected_payment_date)->floorMonth();
        $now = Carbon::now()->floorMonth();
        $periodInMonths = $now->diffInMonths($this_month);

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

        $isNew = !(LoanRequest::where('user_id', $user->id)
                ->whereIn('repayment_status', ['PARTIALLY_PAID', 'PAID'])
                ->count() > 0);

        if ($isNew)
            $interestRate = optional(InterestRateMatrix::where('loan_period',$period)
                ->where('loan_product_id',$invoiceDiscount->loan_product_id)
                ->first())
                ->new_client_interest;
        else
            $interestRate = optional(InterestRateMatrix::where('loan_period',$period)
                ->where('loan_product_id',$invoiceDiscount->loan_product_id)
                ->first())
                ->existing_client_interest;

        $responseArray['interest_rate'] = $interestRate."%";
        $m = $periodInMonths > 1 ? " Months" :" Month";
        $responseArray['period'] = $periodInMonths.$m;
        $offerAmount = $invoiceDiscount->approved_amount * 0.8;
        $responseArray['invoice_amount'] = optional($user->wallet)->currency." ".number_format($invoiceDiscount->approved_amount);
        $responseArray['offer_amount'] = optional($user->wallet)->currency." ".number_format($offerAmount);


        $totalFees = 0;
        $extraMonthlyFees = 0;

        foreach ($loanProduct->fees as $fee) {

            $amt = $fee->amount_type == 'PERCENTAGE' ? $fee->amount/100 * $offerAmount : $fee->amount;
            $type = $fee->amount_type == 'PERCENTAGE' ? $fee->name. " (".$fee->amount."% ".$fee->frequency.")" :  $fee->name." (".$fee->frequency.")";

            if ($fee->amount_type == 'PERCENTAGE' && $fee->frequency == 'MONTHLY'){
                $amt = $amt*$request->period_in_months;
            }

            array_push($feesArray,["fee"=>$type,"amount"=>number_format($amt)]);

            if ($fee->frequency == 'MONTHLY'){
                $extraMonthlyFees += $amt*$request->period_in_months;
            }


            $totalFees += $fee->frequency == "ONE-OFF" ? $amt : 0;
        }

        if ($totalFees > 0){
            $amount_disbursable = $offerAmount - $totalFees;
        }else{
            $amount_disbursable = $offerAmount;
        }


        //interest accruing monthly
        $interestAccrued = (($offerAmount * $interestRate)/100) * $periodInMonths;
        $totalAmount = $offerAmount+$interestAccrued+$extraMonthlyFees;



        $responseArray['total_fees'] = optional($user->wallet)->currency." ".number_format($totalFees);
        $responseArray['amount_disbursable'] = optional($user->wallet)->currency." ".number_format($amount_disbursable);
        $responseArray['amount_payable'] = optional($user->wallet)->currency." ".number_format($totalAmount);
        $responseArray['remmitable_amount'] = optional($user->wallet)->currency." ".number_format($invoiceDiscount->approved_amount-$totalAmount);
        $responseArray['fees'] = $feesArray;

        return response()->json([
            'success' => true,
            'data' => $responseArray,
        ], 200);
    }

    public function reject_invoice_discount(Request $request){

        $this->validate($request, [
            'invoice_discount_id' =>'required|exists:invoice_discounts,id',
        ]);

        $invoiceDiscount = InvoiceDiscount::find($request->invoice_discount_id);

        if ($invoiceDiscount->created_by != auth()->user()->id)
            return response()->json([
                'success' => false,
                'message' => 'Unable to reject offer',
                'errors' => 'You can only reject offers of discount requests that you created.',
            ], 200);


        $invoiceDiscount->offer_status = 'REJECTED';
        $invoiceDiscount->payment_status = 'CANCELLED';
        $invoiceDiscount->reject_reason = 'Offer rejected by customer';
        $invoiceDiscount->update();

        send_sms(
            auth()->user()->phone_no,
            "Dear customer, you have successfully rejected the loan offer for invoice #".
            $invoiceDiscount->invoice_number.
            " invoiced to ".
            optional($invoiceDiscount->employer)->business_name);

        return response()->json([
            'success' => true,
            'message' => 'You have successfully rejected the loan offer for this invoice discount',
        ], 200);
    }

    public function accept_invoice_discount(Request $request){

        $this->validate($request, [
            'invoice_discount_id' =>'required|exists:invoice_discounts,id',
        ]);

        $invoiceDiscount = InvoiceDiscount::find($request->invoice_discount_id);

        if ($invoiceDiscount->created_by != auth()->user()->id)
            return response()->json([
                'success' => false,
                'message' => 'Unable to accept offer',
                'errors' => 'You can only accept offers of discount requests that you created.',
            ], 200);


        $user = auth()->user();
        $loanProduct = LoanProduct::find($invoiceDiscount->loan_product_id);
        if (is_null($loanProduct)){
            return response()->json([
                'success' => false,
                'message' => 'Unable to accept loan offer',
                'errors' => 'Invalid loan product, please contact system admin',
            ], 200);
        }

        $oldestInvoice = Invoice::where('invoice_discount_id',$request->invoice_discount_id)
            ->where('approval_status','APPROVED')
            ->orderBy('expected_payment_date','desc')
            ->first();

        $this_month = Carbon::parse($oldestInvoice->expected_payment_date)->floorMonth();
        $now = Carbon::now()->floorMonth();
        $periodInMonths = $now->diffInMonths($this_month);

        if ($periodInMonths == 0)
            $periodInMonths = 1;

        if ($periodInMonths > $loanProduct->max_period_months){
            return response()->json([
                'success' => false,
                'message' => 'Invalid loan duration',
                'errors' => 'Loan period in months can not be greater than '.$loanProduct->max_period_months.' months',
            ], 200);
        }

        DB::transaction(function() use($request, $loanProduct,$invoiceDiscount, $user, $periodInMonths) {

            $offerAmount = $invoiceDiscount->approved_amount*0.8;

            $exists = LoanRequest::where('user_id', $user->id)
                ->where('loan_product_id',$invoiceDiscount->loan_product_id)
                ->where('amount_requested', $offerAmount)
                ->where('repayment_status','PENDING')
                ->whereDate('created_at',Carbon::now())
                ->first();


            if (is_null($exists)){


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

                $isNew = !(LoanRequest::where('user_id', $user->id)
                        ->whereIn('repayment_status', ['PARTIALLY_PAID', 'PAID'])
                        ->count() > 0);

                if ($isNew)
                    $interestRate = optional(InterestRateMatrix::where('loan_period',$period)
                        ->where('loan_product_id',$invoiceDiscount->loan_product_id)
                        ->first())->new_client_interest;
                else
                    $interestRate = optional(InterestRateMatrix::where('loan_period',$period)
                        ->where('loan_product_id',$invoiceDiscount->loan_product_id)
                        ->first())->existing_client_interest;


                $loan = new LoanRequest();
                $loan->user_id = $user->id;
                $loan->loan_product_id = $invoiceDiscount->loan_product_id;
                $loan->amount_requested = $offerAmount;
                $loan->amount_disbursable = $offerAmount;
                $loan->interest_rate = $interestRate;
                $loan->fees = 0;
                $loan->approval_status="APPROVED";
                $loan->repayment_status="PENDING";
                $loan->period_in_months = $periodInMonths;
                $loan->saveOrFail();


                //update IDF
                $invoiceDiscount->offer_status = 'ACCEPTED';
                $invoiceDiscount->loan_request_id = $loan->id;
                $invoiceDiscount->update();

                $totalFees = 0;

                foreach ($loanProduct->fees as $fee) {

                    $amt = $fee->amount_type == 'PERCENTAGE' ? $fee->amount/100 * $offerAmount : $fee->amount;
                    $type = $fee->amount_type == 'PERCENTAGE' ? $fee->name. " (".$fee->amount."%)" :  $fee->name;

                    if ($fee->amount_type == 'PERCENTAGE' && $fee->frequency == 'MONTHLY'){
                        $amt = $amt*$periodInMonths;
                    }

                    $loanRequestFee = new LoanRequestFee();
                    $loanRequestFee->loan_request_id = $loan->id;
                    $loanRequestFee->fee = $type;
                    $loanRequestFee->amount = $amt;
                    $loanRequestFee->frequency = $fee->frequency;
                    $loanRequestFee->saveOrFail();

                    $totalFees += $fee->frequency == "ONE-OFF" ? $amt : 0;
                }

                $disbursable = $offerAmount - $totalFees;

                if ($totalFees > 0){
                    $loan->amount_disbursable = $disbursable;
                    $loan->fees = $totalFees;
                    $loan->update();
                }

                //move to wallet

                $wallet = $invoiceDiscount->company->wallet;

                $prevBal = $wallet->current_balance;
                $newBal = $prevBal+$disbursable;

                $wallet->current_balance = $newBal;
                $wallet->previous_balance = $prevBal;
                $wallet->update();

                $receipt = $this->randomID();

                //save to wallet transactions
                $walletTransaction = new WalletTransaction();
                $walletTransaction->wallet_id = $wallet->id;
                $walletTransaction->amount = $disbursable;
                $walletTransaction->previous_balance = $prevBal;
                $walletTransaction->transaction_type = 'CR';
                $walletTransaction->source = 'Loan approval';
                $walletTransaction->trx_id = $receipt;
                $walletTransaction->narration ="Invoice discount loan offered";
                $walletTransaction->saveOrFail();


                //direct withdrawal
                $charge = MpesaCharge::where('min', '<=',$disbursable)->where('max', '>=',$disbursable)->first();
                if (!is_null($charge)){
                    $withdrawalAmount  = $disbursable - $charge->charge;
                    $timestamp = Carbon::now()->getTimestamp();

                    $payload = array(
                        "wallet_id"=>$wallet->id,
                        "recipient"=> optional(optional($invoiceDiscount->company)->owner)->phone_no,
                        "amount"=>floor($withdrawalAmount),
                        "randomID"=>$timestamp."M-PESA withdrawal",
                    );

                    $connection = new AMQPStreamConnection('localhost', 5672,
                        config('app.AMQP_USER'), config('app.AMQP_PASSWORD'));
                    $channel = $connection->channel();
                    $channel->queue_declare('Quicksava_B2C_QUEUE', false, true, false, false);
                    $msg = new AMQPMessage(json_encode($payload), array('delivery_mode' => 2)
                    );
                    $channel->basic_publish($msg, '', 'Quicksava_B2C_QUEUE');
                    $channel->close();
                    $connection->close();
                }


                //interest accruing monthly
                $interestAccrued = (($offerAmount * $interestRate)/100) * $periodInMonths;
                Log::info("interest....". $interestAccrued);

                //extra monthly fees
                $extraMonthlyFees = 0;
                foreach (LoanRequestFee::where('loan_request_id', $loan->id)->where('frequency','MONTHLY')->get() as $requestFee){
                    $extraMonthlyFees += $requestFee->amount;
                }
                $totalMonthlyFees = $extraMonthlyFees * $periodInMonths;

                $beginningBalance = $offerAmount+$interestAccrued+$totalMonthlyFees;

                $invoice = Invoice::where('invoice_discount_id', $request->invoice_discount_id)
                    ->where('approval_status','APPROVED')
                    ->orderBy('expected_payment_date', 'DESC')
                    ->first();


                $loanSchedule = new LoanSchedule();
                $loanSchedule->loan_request_id = $loan->id;
                $loanSchedule->payment_date = $invoice->expected_payment_date;
                $loanSchedule->beginning_balance = $beginningBalance;
                $loanSchedule->scheduled_payment = $beginningBalance;
                $loanSchedule->interest_paid = $interestAccrued;
                $loanSchedule->principal_paid = $offerAmount;
                $loanSchedule->ending_balance = 0;
                $loanSchedule->saveOrFail();


                send_sms($user->phone_no, "Your invoice discount of approved amount Ksh. ".number_format($invoiceDiscount->approved_amount,2)." has been processed successfully. 80% of the amount less all the processing fee (KES ".number_format($disbursable).")has been deposited to your company wallet. The remaining 20% will be deposited to your company wallet less the interest once the the invoice has been paid");

            }
        });


        return response()->json([
            'success' => true,
            'message' => 'Invoice discount offer has been accepted successfully',
        ], 200);

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
