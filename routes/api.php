<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group([
    'prefix' => 'auth',
    'namespace' => 'API'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');

    Route::post('send_otp', 'AuthController@send_otp');
    Route::post('verify_otp', 'AuthController@verify_otp');
    Route::post('reset_password', 'AuthController@reset_password');
});

//Loans
Route::group([
    'prefix'=>"loans",
    'namespace'=>'API'
],function(){
    Route::post('new-lead','LoansController@leads');
    Route::post('upload-documents','LoansController@upload_documents');

});


Route::group([
    'middleware' => [
        'auth:api',
    ],
    'namespace' => 'API'
], function() {
    Route::get('auth/user', 'AuthController@user');

    //loans
    Route::get('loans/get/count', 'LoansController@get_loan_count');
    Route::get('loans/get/my', 'LoansController@get_my_loans');
    Route::post('loans/apply', 'LoansController@create_loan');
    Route::post('loans/calculate', 'LoansController@calculate_loan');
    Route::get('loans/details/{loan_id}', 'LoansController@get_loan_details');
    Route::post('loans/pay', 'LoansController@pay_loan');


    Route::get('wallet/transactions', 'WalletController@get_transactions');

    Route::get('wallet/bank_accounts', 'BankController@bank_accounts');
    Route::get('wallet/bank_accounts/{company_id}', 'BankController@company_bank_accounts');

    Route::post('wallet/bank_account/create', 'BankController@create_bank_account');
    Route::post('wallet/bank_account/update', 'BankController@update_bank_account');
    Route::post('wallet/bank_account/delete', 'BankController@delete_bank_account');
    Route::post('wallet/bank_account/withdraw', 'BankController@withdraw_to_bank_account');

    Route::get('banks', 'BankController@banks');
    Route::get('branches/{bank_id}', 'BankController@bank_branches');




    Route::get('partners/employers', 'PartnerController@employers');
    Route::get('partners/my_employers', 'PartnerController@my_employers');
    Route::post('partners/add/my_employer', 'PartnerController@add_my_employer');

    //employer loans (Salary Advance)
    Route::get('partners/employer/loan_products/{employer_id}', 'PartnerController@get_employer_loan_products');


    Route::post('advance/calculate', 'AdvanceController@calculate_advance_loan');
    Route::post('advance/create_request', 'AdvanceController@create_request');
    Route::post('advance/update_request', 'AdvanceController@update_request');
    Route::get('advance/get_my_requests', 'AdvanceController@get_my_requests');

    Route::get('partners/invoice_merchants', 'PartnerController@invoice_merchants');



    //invoice discounting
    //companies
//    Route::get('companies', 'CompanyController@companies');
//    Route::post('companies/add', 'CompanyController@add_company');
//    Route::post('companies/letter/upload', 'CompanyController@upload_company_letter');

//    Route::get('company/details/{id}', 'CompanyController@get_company_details');
//
//
//    Route::get('companies/directors/{company_id}', 'CompanyController@get_company_directors');
//    Route::post('companies/director/add', 'CompanyController@add_company_director');
//
//    Route::get('companies/invoice_discounts/{company_id}', 'CompanyController@get_company_invoice_discounts');
//    Route::post('companies/invoice_discount/apply', 'CompanyController@apply_invoice_discount');
//    Route::post('companies/invoice_discount/add_invoice', 'CompanyController@add_invoice');
//    Route::post('companies/invoice_discount/edit_invoice', 'CompanyController@edit_invoice');
//
//    Route::post('companies/invoice_discount/calculate', 'CompanyController@calculate_invoice_discount');
//    Route::post('companies/invoice_discount/reject', 'CompanyController@reject_invoice_discount');
//    Route::post('companies/invoice_discount/accept', 'CompanyController@accept_invoice_discount');
//    Route::post('companies/invoice_discount/submit', 'CompanyController@submit_for_review');
//    Route::get('companies/invoice_discount/details/{invoice_discount_id}', 'CompanyController@get_invoice_discount_details');
//    Route::get('companies/invoice_discount/invoices/{invoice_discount_id}', 'CompanyController@get_id_invoices');
//
//
//    Route::get('companies/wallet/transactions/{_walletId}', 'WalletController@get_company_transactions');
//    Route::get('companies/wallet/{_walletId}', 'WalletController@get_company_wallet');



    Route::post('wallet/withdraw', 'PaymentsController@withdraw');//->middleware('throttle:4,1');

    //auto logbook
    Route::get('auto/makes', 'LogbookController@makes');
    Route::get('auto/models/{make_id}', 'LogbookController@models');

    Route::post('auto/logbook', 'LogbookController@new_logbook_request');
    Route::get('auto/logbook', 'LogbookController@logbook_applications');

    Route::post('auto/logbook/vehicles', 'LogbookController@add_logbook_application_vehicle');
    Route::get('auto/logbook/vehicles/{application_id}', 'LogbookController@get_logbook_application_vehicles');
    Route::post('auto/logbook/files/named', 'LogbookController@upload_named_files');
    Route::post('auto/logbook/application/submit', 'LogbookController@submit_for_review');

    Route::post('auto/logbook/company/update', 'LogbookController@update_logbook_application_company');
    Route::post('auto/logbook/directors', 'LogbookController@add_logbook_application_director');
    Route::get('auto/logbook/directors/{application_id}', 'LogbookController@get_logbook_application_directors');


});












Route::post('ack_payment', 'API\PaymentsController@ack_payment');
Route::post('ack_disbursement', 'API\PaymentsController@ack_disbursement');

Route::post('wallet/top_up', 'API\PaymentsController@top_up');

Route::post('paybill_balance/b2c', 'API\PaymentsController@update_b2c_balance');

Route::post('app/version', 'API\AuthController@check_app_version');


