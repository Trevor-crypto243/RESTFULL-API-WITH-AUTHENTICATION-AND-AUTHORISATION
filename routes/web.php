<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
Route::get('/logout', 'Auth\LoginController@logout');
Route::get('/privacy', 'PrivacyController@privacy');


Route::group(['middleware' => ['auth']], function () {
    Route::get('/','HomeController@index');

    Route::get('/dashboard/get_total_customers','HomeController@get_total_customers');
    Route::get('/dashboard/get_checkoff_customers','HomeController@get_checkoff_customers');
    Route::get('/dashboard/get_approved_today','HomeController@get_approved_today');
    Route::get('/dashboard/get_paid_today','HomeController@get_paid_today');
    Route::get('/dashboard/get_total_disbursed','HomeController@get_total_disbursed');
    Route::get('/dashboard/get_total_repaid','HomeController@get_total_repaid');
    Route::get('/dashboard/get_due_today','HomeController@get_due_today');
    Route::get('/dashboard/get_overdue','HomeController@get_overdue');
    Route::get('/dashboard/get_wallets_amount','HomeController@get_wallets_amount');
    Route::get('/dashboard/get_total_wallet_withdrawals','HomeController@get_total_wallet_withdrawals');
    Route::get('/dashboard/get_todays_wallet_withdrawals','HomeController@get_todays_wallet_withdrawals');



    //bulk sms
    Route::get('/bulk/messaging', 'SmsController@bulk_sms')->middleware('perm:4');
    Route::post('/bulk/messaging/custom', 'SmsController@create_custom_bulk_sms')->middleware('perm:4');
    Route::post('/bulk/messaging/group', 'SmsController@create_group_bulk_sms')->middleware('perm:4');
    Route::post('/bulk/messaging/specify', 'SmsController@create_specified_bulk_sms')->middleware('perm:4');
    Route::post('/bulk/messaging/upload', 'SmsController@create_upload_bulk_sms')->middleware('perm:4');
    Route::get('ajax/messaging/bulk', 'SmsController@bulkSmsDT')->name('bulk-sms-dt')->middleware('perm:4');


    //customers
    Route::get('/customers', 'CustomerController@customers')->middleware('perm:3');
    Route::get('customers/all/export', 'CustomerController@exportAllCustomers')->middleware('perm:3');

    Route::get('/customers/checkoff', 'CustomerController@checkoff_customers')->middleware('perm:3');
    Route::get('/customers/new-leads', 'CustomerController@new_leads')->middleware('perm:3');

    Route::get('ajax/customers/checkoff', 'CustomerController@checkoffCustomersDT')->name('checkoff-customers-dt')->middleware('perm:3');
    Route::get('customers/checkoff/export', 'CustomerController@exportCheckoffCustomers')->middleware('perm:3');

    Route::get('ajax/customers/new-leads', 'CustomerController@LeadsDT')->name('leads-dt')->middleware('perm:3');


    Route::post('/customers/search', 'CustomerController@search_customers')->middleware('perm:3');
    Route::get('ajax/customers/search/{idNo}/{phoneNo}', 'CustomerController@searchCustomersDT')->name('search-customers-dt')->middleware('perm:3');


    Route::get('/customers/checkoff/summary', 'CustomerController@checkoff_customers_summary')->middleware('perm:3');
    Route::get('ajax/customers/checkoff/summary', 'CustomerController@checkoffCustomersSummaryDT')->name('checkoff-customers-summary-dt')->middleware('perm:3');
    Route::get('customers/checkoff/summary/export', 'CustomerController@exportCheckoffCustomersSummary')->middleware('perm:3');

    Route::get('customers/details/{_id}', 'CustomerController@customer_details')->name('customer-details')->middleware('perm:3');
    Route::get('ajax/customer/loans/{_user_id}', 'CustomerController@customerLoansDT')->name('customer-loans-dt')->middleware('perm:3');
    Route::post('customer/overdraft/update', 'CustomerController@update_customer_overdraft')->middleware('perm:37');
    Route::post('customer/suspend', 'CustomerController@suspend_customer')->middleware('perm:38');
    Route::post('customer/unsuspend', 'CustomerController@unsuspend_customer')->middleware('perm:38');
    Route::post('customer/block', 'CustomerController@block_customer')->middleware('perm:38');
    Route::post('customer/unblock', 'CustomerController@unblock_customer')->middleware('perm:38');

    Route::get('/customers/managed', 'CustomerController@managed_customers')->middleware('perm:43');
    Route::get('/customers/managed/filter/{idNo}', 'CustomerController@managed_customers_idno')->middleware('perm:43');
    Route::get('ajax/customers/managed/{filter}', 'CustomerController@managedCustomersDT')->name('managed-customers-dt')->middleware('perm:43');


    Route::get('/customers/managed/new', 'CustomerController@new_managed_customer')->middleware('perm:44');
    Route::get('ajax/customers/managed/otp/{phone_no}', 'CustomerController@managed_customer_otp')->middleware('perm:44');
    Route::post('/customers/managed/create', 'CustomerController@create_managed_customer')->middleware('perm:44');

    Route::post('/customers/managed/inua/apply', 'CustomerController@apply_managed_inua')->middleware('perm:45');
    Route::post('/customers/managed/inua/amend', 'CustomerController@amend_managed_inua')->middleware('perm:45');


    Route::get('employees/details/{id}', 'CustomerController@employee_details')->name('employee-details')->middleware('perm:3');
    Route::post('employees/update_limit', 'CustomerController@update_employee_limit')->middleware('perm:3');
    Route::post('employees/update_employer', 'CustomerController@update_employer')->middleware('perm:3');



    Route::group(['middleware' => ['perm:14']], function () {
        //loan products
        Route::get('/products/loans', 'LoansController@products');//->middleware('perm:1');
        Route::post('products/loans', 'LoansController@create_loan_product');//->middleware('perm:12');
        Route::get('ajax/products/loans', 'LoansController@loanProductsDT')->name('loan-products-dt');//->middleware('perm:1');
        Route::get('products/loans/{_id}', 'LoansController@loan_product_details')->name('loan-product-details');//->middleware('perm:1');
        Route::post('/products/limits/update', 'LoansController@update_product_min_max');//->middleware('perm:1');
        Route::post('/products/closing/update', 'LoansController@update_product_closing_date');//->middleware('perm:1');
        Route::post('/products/period/update', 'LoansController@update_product_period');//->middleware('perm:1');
        Route::post('/products/organisations/add', 'LoansController@add_org_loan_product');//->middleware('perm:1');
        Route::post('/products/organisations/delete', 'LoansController@delete_org_loan_product');//->middleware('perm:1');

        Route::post('products/loans/fees/create', 'LoansController@create_loan_product_fee');//->middleware('perm:12');
        Route::post('products/loans/fees/delete', 'LoansController@delete_loan_product_fee');//->middleware('perm:12');

        Route::post('products/interest/matrix/update', 'LoansController@update_interest_matrix');//->middleware('perm:12');
        Route::get('products/interest/matrix/get/{id}', 'LoansController@edit_matrix')->name('edit-matrix');//->middleware('perm:12');

    });




    Route::group(['middleware' => ['perm:13']], function () {
        //loans
        Route::get('/loans/all', 'LoansController@all_loans');//->middleware('perm:1');
        Route::get('ajax/loans/all', 'LoansController@allLoansDT')->name('all-loans-dt');//->middleware('perm:1');
        Route::get('loans/all/export', 'LoansController@exportAllLoans');

//        Route::get('/loans/requests', 'LoansController@new_loans');//->middleware('perm:1');
//        Route::get('ajax/loans/requests', 'LoansController@newLoansDT')->name('new-loans-dt');//->middleware('perm:1');
//        Route::get('loans/requests/export', 'LoansController@exportNewLoans');

//        Route::get('/loans/approved', 'LoansController@approved_loans');//->middleware('perm:1');
//        Route::get('ajax/loans/approved', 'LoansController@approvedLoansDT')->name('approved-loans-dt');//->middleware('perm:1');
//        Route::get('loans/approved/export', 'LoansController@exportApprovedLoans');

//        Route::get('/loans/rejected', 'LoansController@rejected_loans');//->middleware('perm:1');
//        Route::get('ajax/loans/rejected', 'LoansController@rejectedLoansDT')->name('rejected-loans-dt');//->middleware('perm:1');
//        Route::get('loans/rejected/export', 'LoansController@exportRejectedLoans');

        Route::get('/loans/due_today', 'LoansController@due_today_loans');//->middleware('perm:1');
        Route::get('ajax/loans/due_today', 'LoansController@dueTodayloansDT')->name('due-today-loans-dt');//->middleware('perm:1');
        Route::get('loans/due_today/export', 'LoansController@exportDueTodayLoans');


        Route::get('/loans/repaid', 'LoansController@repaid_loans');//->middleware('perm:1');
        Route::get('ajax/loans/repaid', 'LoansController@repaidLoansDT')->name('repaid-loans-dt');//->middleware('perm:1');
        Route::get('loans/repaid/export', 'LoansController@exportRepaidLoans');

        Route::get('/loans/repaid/today', 'LoansController@repaid_loans_today');//->middleware('perm:1');
        Route::get('ajax/loans/repaid/today', 'LoansController@repaidLoansTodayDT')->name('repaid-loans-today-dt');//->middleware('perm:1');
        Route::get('loans/repaid/today/export', 'LoansController@exportTodayRepaidLoans');

        Route::get('/loans/approved_today', 'LoansController@approved_today_loans');//->middleware('perm:1');
        Route::get('ajax/loans/approved_today', 'LoansController@approvedTodayloansDT')->name('approved-today-loans-dt');//->middleware('perm:1');
        Route::get('loans/approved_today/export', 'LoansController@exportApprovedToday');

        Route::get('/loans/overdue', 'LoansController@overdue_loans');//->middleware('perm:1');
        Route::get('ajax/loans/overdue', 'LoansController@overdueLoansDT')->name('overdue-loans-dt');//->middleware('perm:1');
        Route::get('loans/overdue/export', 'LoansController@exportOverdue');

        Route::get('/loans/details/{id}', 'LoansController@loan_details')->name('loan-details');//->middleware('perm:1');
        Route::get('ajax/loans/repayments/{id}', 'LoansController@loanRepaymentsDT')->name('ajax-loan-repayments');//->middleware('perm:1');
        Route::get('ajax/loans/schedule/{id}', 'LoansController@loanScheduleDT')->name('ajax-loan-schedule');//->middleware('perm:1');

        Route::post('/loans/repay/wallet', 'LoansController@wallet_loan_repay');//->middleware('perm:1');


    });

    Route::post('/loans/action/approve', 'LoansController@approve_loan')->middleware('perm:12');
    Route::post('/loans/action/reject', 'LoansController@reject_loan')->middleware('perm:12');



    //reconciliations
    Route::get('/recon/suspense', 'ReconController@suspense')->middleware('perm:16');
    Route::get('ajax/recon/suspense', 'ReconController@suspenseDT')->name('suspense-dt')->middleware('perm:16');
    Route::get('recon/suspense/refund/{id}', 'ReconController@refundSuspense')->name('suspense-refund')->middleware('perm:16');

    Route::get('/recon/c2b', 'ReconController@c2b')->middleware('perm:17');
    Route::get('ajax/recon/c2b', 'ReconController@c2bDT')->name('c2b-dt')->middleware('perm:17');
    Route::post('recon/c2b/create', 'ReconController@create_c2b')->middleware('perm:17');


    Route::get('/recon/b2c', 'ReconController@b2c')->middleware('perm:51');
    Route::get('ajax/recon/b2c', 'ReconController@b2cDT')->name('b2c-dt')->middleware('perm:51');
    Route::post('recon/b2c/update', 'ReconController@reconcile_b2c')->middleware('perm:51');


//    Route::get('/recon/bulk_disburse', 'ReconController@bulk_disburse')->middleware('perm:18');
//    Route::get('ajax/recon/bulk_disbursements', 'ReconController@disburseDT')->name('disburse-dt')->middleware('perm:18');
//    Route::post('recon/bulk_disburse/single', 'ReconController@create_single_bulk_disburse')->middleware('perm:18');
//    Route::post('recon/bulk_disburse/upload', 'ReconController@create_upload_bulk_disburse')->middleware('perm:18');


    Route::get('reports/advance_repayments', 'ReportsController@advance_repayments')->middleware('perm:6');
    Route::get('ajax/reports/advance_repayments', 'ReportsController@advance_repaymentsDT')->name('repayments-dt')->middleware('perm:6');

    Route::get('reports/insurance_data', 'ReportsController@insurance_data')->middleware('perm:6');
    Route::get('ajax/reports/insurance_data', 'ReportsController@insurance_dataDT')->name('insurance-data-dt')->middleware('perm:6');

    Route::get('reports/running_lb', 'ReportsController@running_lb')->middleware('perm:6');
    Route::get('ajax/reports/running_lb', 'ReportsController@running_lbDT')->name('running-lb-dt')->middleware('perm:6');

    Route::get('reports/repayments', 'ReportsController@repayments')->middleware('perm:6');
    Route::get('ajax/reports/repayments_paid', 'ReportsController@repaymentsDT')->name('repayments-paid-dt')->middleware('perm:6');

    Route::get('reports/mtd', 'ReportsController@mtd')->middleware('perm:6');
    Route::post('reports/mtd', 'ReportsController@mtd_filter')->middleware('perm:6');
    Route::get('ajax/reports/mtd/{employer_id}', 'ReportsController@mtdDt')->name('mtd-dt')->middleware('perm:6');

    Route::get('reports/mtd/range', 'ReportsController@mtd_range')->middleware('perm:6');
    Route::get('ajax/reports/mtd_range', 'ReportsController@mtd_rangeDT')->name('mtd-range-dt')->middleware('perm:6');


    Route::get('reports/ageing', 'ReportsController@ageing')->middleware('perm:6');
    Route::get('ajax/reports/ageing', 'ReportsController@ageingDT')->name('ageing-dt')->middleware('perm:6');


    Route::group(['middleware' => ['perm:11']], function () {

        //partners
        Route::get('partners', 'PartnerController@employers');//->middleware('perm:1');
        Route::post('partners', 'PartnerController@create_employer');//->middleware('perm:12');
        Route::get('ajax/partners', 'PartnerController@employersDT')->name('employers-dt');//->middleware('perm:1');

        Route::get('partners/edit/{_id}', 'PartnerController@partner_details')->name('edit-employer-details')->middleware('perm:19');
        Route::put('partners', 'PartnerController@update_partner')->middleware('perm:19');

        Route::get('partners/{id}', 'PartnerController@employer_details')->name('employer-details');//->middleware('perm:1');
        Route::get('ajax/partners/employees/{id}', 'PartnerController@partnerEmployeesDT')->name('ajax-partner-employees');//->middleware('perm:1');
        Route::get('ajax/partners/employees/incomes/{id}', 'PartnerController@partnerEmployeeIncomesDT')->name('ajax-partner-employee-incomes');//->middleware('perm:1');
        Route::post('partners/employees/incomes/upload', 'PartnerController@upload_incomes');//->middleware('perm:1');
        Route::post('partners/employees/repayments/upload', 'PartnerController@upload_repayments');//->middleware('perm:1');
        Route::get('ajax/partners/advance/repayments/{id}', 'PartnerController@partnerAdvanceRepaymentsDT')->name('ajax-advance-repayments');//->middleware('perm:1');
        Route::post('partners/employers/hr/create', 'PartnerController@create_hr');//->middleware('perm:1');
        Route::get('ajax/partners/employer/hr/{id}', 'PartnerController@partnerEmployerHrDT')->name('ajax-partner-employer-hr');//->middleware('perm:1');
        Route::delete('partners/employer/hr/delete/{id}', 'PartnerController@delete_hr')->name('delete-hr');//->middleware('perm:12');

        Route::post('partners/advance/matrix', 'PartnerController@create_advance_period_matrix');//->middleware('perm:1');
        Route::delete('partners/advance/matrix', 'PartnerController@delete_advance_period_matrix');//->middleware('perm:1');

        Route::post('partners/advance/disable', 'PartnerController@disable_advance');//->middleware('perm:1');
        Route::post('partners/advance/enable', 'PartnerController@enable_advance');//->middleware('perm:1');


        Route::get('ajax/partners/mtd/targets/{id}', 'PartnerController@mtdTargetsDT')->name('ajax-partner-mtd-targets');//->middleware('perm:1');
        Route::post('partners/mtd', 'PartnerController@new_mtd_target');//->middleware('perm:1');
        Route::get('partners/mtd/{_id}', 'PartnerController@mtd_details')->name('mtd-details');
        Route::put('partners/mtd', 'PartnerController@update_mtd');
        Route::delete('partners/mtd/delete/{id}', 'PartnerController@delete_mtd')->name('delete-mtd');

    });


    //salary advance
    Route::get('advance/requests/new', 'SalaryAdvanceController@new_requests')->middleware('perm:9');
    Route::post('advance/requests/new', 'SalaryAdvanceController@get_new_requests')->middleware('perm:9');

    Route::get('advance/requests/progressing', 'SalaryAdvanceController@progress_requests')->middleware('perm:9');
    Route::post('advance/requests/progressing', 'SalaryAdvanceController@get_progress_requests')->middleware('perm:9');

    Route::get('advance/requests/amending', 'SalaryAdvanceController@amending_requests')->middleware('perm:9');
    Route::post('advance/requests/amending', 'SalaryAdvanceController@get_amending_requests')->middleware('perm:9');

    Route::get('advance/requests/accepted', 'SalaryAdvanceController@accepted_requests')->middleware('perm:9');
    Route::post('advance/requests/accepted', 'SalaryAdvanceController@get_accepted_requests')->middleware('perm:9');

    Route::get('advance/requests/rejected', 'SalaryAdvanceController@rejected_requests')->middleware('perm:9');
    Route::post('advance/requests/rejected', 'SalaryAdvanceController@get_rejected_requests')->middleware('perm:9');

    Route::post('ajax/advance/requests', 'SalaryAdvanceController@requestsDT')->name('advance-requests-dt')->middleware('perm:9');

    Route::get('advance/requests/details/{id}', 'SalaryAdvanceController@request_details')->name('advance-application-details')->middleware('perm:9');


    Route::get('advance/requests/user/{id}', 'SalaryAdvanceController@user_requests')->middleware('perm:9');
    Route::get('ajax/advance/requests/{userId}', 'SalaryAdvanceController@userRequestsDT')->name('user-advance-requests-dt');//->middleware('perm:9');

    Route::post('advance/requests/approve', 'SalaryAdvanceController@approve_request')->middleware('perm:10');
    Route::post('advance/requests/reject', 'SalaryAdvanceController@reject_request')->middleware('perm:40');
    Route::post('advance/requests/amendment', 'SalaryAdvanceController@request_amendment')->middleware('perm:41');
    Route::post('advance/requests/send_to_hr', 'SalaryAdvanceController@send_to_hr')->middleware('perm:42');




    //banks
    Route::get('/bank/banks', 'BankController@banks') ->middleware('perm:47');
    Route::post('bank/banks', 'BankController@create_bank')->middleware('perm:48');
    Route::get('ajax/bank/banks', 'BankController@banksDT')->name('banks-dt')->middleware('perm:47');
    Route::get('bank/banks/{_id}', 'BankController@bank_details')->name('bank-details')->middleware('perm:48');
    Route::put('bank/banks', 'BankController@update_bank')->middleware('perm:48');

    //bank branches
    Route::post('bank/branches', 'BankController@create_branch')->middleware('perm:47');
    Route::get('ajax/bank/branches', 'BankController@bankBranchesDT')->name('branches-dt')->middleware('perm:47');
    Route::get('bank/branches/{_id}', 'BankController@branch_details')->name('branch-details')->middleware('perm:48');
    Route::put('bank/branches', 'BankController@update_branch')->middleware('perm:48');
    Route::delete('bank/branches/delete/{id}', 'BankController@delete_branch')->name('delete-branch')->middleware('perm:48');



    //bank accounts
    Route::get('/bank/accounts', 'BankController@bank_accounts') ->middleware('perm:49');
    Route::get('ajax/bank/accounts', 'BankController@bank_accountsDT')->name('bank-accounts-dt')->middleware('perm:49');
    Route::post('bank/accounts/approve', 'BankController@approve_account')->middleware('perm:50');
    Route::post('bank/accounts/disapprove', 'BankController@disapprove_account')->middleware('perm:50');


    //auto logbook
    Route::get('/auto/models', 'LogbookController@makes') ->middleware('perm:26');

    Route::post('auto/makes', 'LogbookController@create_make')->middleware('perm:26');
    Route::get('ajax/auto/makes', 'LogbookController@makesDT')->name('makes-dt')->middleware('perm:26');
    Route::get('auto/makes/{_id}', 'LogbookController@make_details')->name('vehicle-make-details')->middleware('perm:26');
    Route::put('auto/makes', 'LogbookController@update_make')->middleware('perm:26');
    Route::delete('auto/makes/delete/{id}', 'LogbookController@delete_make')->name('delete-make')->middleware('perm:26');

    Route::post('auto/models', 'LogbookController@create_model');//->middleware('perm:17');
    Route::get('ajax/auto/models', 'LogbookController@modelsDT')->name('models-dt');//->middleware('perm:17');
    Route::get('auto/models/{_id}', 'LogbookController@model_details')->name('vehicle-model-details')->middleware('perm:26');
    Route::put('auto/models', 'LogbookController@update_model')->middleware('perm:26');
    Route::delete('auto/models/delete/{id}', 'LogbookController@delete_model')->name('delete-model')->middleware('perm:26');


    Route::get('auto/applications', 'LogbookController@logbook_applications')->middleware('perm:28');
 
    //Adding a new applicant routes
    Route::get('auto/add-applicant', 'LogbookController@add_applicant');
    Route::post('auto/add-applicant', 'LogbookController@add_applicant_details');



    Route::get('ajax/auto/applications/{status}', 'LogbookController@logbook_applicationsDT')->name('logbook-applications-dt')->middleware('perm:28');
    Route::get('auto/applications/{application_id}', 'LogbookController@logbook_application_details')->middleware('perm:28');
    Route::get('auto/applications/vehicles/{application_id}', 'LogbookController@logbook_application_vehiclesDT')->name('logbook-vehicles-dt')->middleware('perm:28');

    Route::post('auto/applications/deduction/add', 'LogbookController@add_deduction')->middleware('perm:46');
    Route::post('auto/applications/deduction/delete', 'LogbookController@delete_deduction')->middleware('perm:46');

    Route::post('auto/applications/update', 'LogbookController@update_logbook_application')->middleware('perm:30');
    Route::post('auto/applications/comment', 'LogbookController@comment_on_logbook_application')->middleware('perm:31');
    Route::post('auto/applications/file', 'LogbookController@upload_additional_file')->middleware('perm:32');

    Route::post('auto/applications/submit/review', 'LogbookController@submit_for_review')->middleware('perm:33');
    Route::post('auto/applications/submit/approval', 'LogbookController@submit_for_approval')->middleware('perm:34');

    Route::post('auto/applications/reject', 'LogbookController@reject_application')->middleware('perm:36');
    Route::post('auto/applications/approve', 'LogbookController@approve_application')->middleware('perm:35');


    Route::get('auto/applications/vehicles/edit/{_id}', 'LogbookController@vehicle_details')->name('edit-vehicle-details')->middleware('perm:29');
    Route::put('auto/applications/vehicles', 'LogbookController@update_vehicle')->middleware('perm:29');

    Route::get('/auto/models/json/{make_id}','LogbookController@get_models_json')->middleware('perm:28');





//wallets
    Route::get('wallet/{company}/{id}', 'WalletsController@wallet')->middleware('perm:22');
    Route::get('ajax/wallet/transactions/{id}', 'WalletsController@wallet_transactionsDT')->name('transactions-dt')->middleware('perm:22');
    Route::post('wallet/freeze', 'WalletsController@freeze_wallet')->middleware('perm:39');
    Route::post('wallet/activate', 'WalletsController@activate_wallet')->middleware('perm:39');

    Route::post('wallet/withdraw', 'WalletsController@withdraw')->middleware('perm:27');

    Route::get('wallets', 'WalletsController@all_wallets')->middleware('perm:22');
    Route::get('ajax/wallets', 'WalletsController@allWalletsDT')->name('all-wallets-dt')->middleware('perm:22');
    Route::get('wallets/all/export', 'WalletsController@exportAllWallets')->middleware('perm:22');

    Route::get('wallets/transactions/today', 'WalletsController@today_transactions')->middleware('perm:22');
    Route::get('ajax/wallets/transactions/today', 'WalletsController@todayTransactionsDT')->name('today-transactions-dt')->middleware('perm:22');
    Route::get('wallets/transactions/today/export', 'WalletsController@exportTodayWalletTransactions')->middleware('perm:22');

    Route::get('wallets/transactions/all', 'WalletsController@all_wallet_transactions')->middleware('perm:22');
    Route::get('ajax/wallets/transactions/all', 'WalletsController@allWalletTransactionsDT')->name('all-transactions-dt')->middleware('perm:22');
    Route::get('wallets/transactions/all/export', 'WalletsController@exportAllWalletTransactions')->middleware('perm:22');



    //users and user management
    Route::get('/users', 'UserController@users')->middleware('perm:1');
    Route::post('/users/search', 'UserController@search_users')->middleware('perm:1');

    Route::post('/enroll', 'UserController@register_user')->middleware('perm:1');
    Route::get('users/edit/{_id}', 'UserController@edit_user')->name('edit-user')->middleware('perm:1');
    Route::get('users/delete/{_id}', 'UserController@delete')->name('delete-user')->middleware('perm:1');

    Route::post('/delete', 'UserController@delete_user')->middleware('perm:1');
    Route::put('/enroll','UserController@update_user')->middleware('perm:1');


    Route::get('/user_groups', 'UserController@user_groups')->middleware('perm:1');
    Route::post('/user_groups', 'UserController@new_user_group')->middleware('perm:1');
    Route::get('user_groups/{_id}', 'UserController@get_group_details')->middleware('perm:1');
    Route::post('user_groups/update', 'UserController@update_group_details')->middleware('perm:1');
    Route::post('user_groups/delete', 'UserController@delete_group')->middleware('perm:1');

    Route::get('ajax/users/groups/details/{id}', 'UserController@userGroupDetailsDT')->middleware('perm:1');
    Route::get('users/groups/{id}','UserController@user_group_details')->middleware('perm:1');
    Route::post('/users/groups/permissions/add','UserController@add_group_permission')->middleware('perm:1');
    Route::get('/users/groups/permissions/delete/{id}','UserController@delete_group_permission')->middleware('perm:1');


    Route::get('edit-profile', 'UserController@editProfile');
    Route::get('my-profile', 'UserController@myProfile');
    Route::put('edit-profile', 'UserController@updateProfile');
    Route::put('change-password', 'UserController@updatePassword');

    //Audit Logs
    Route::get('/audit_logs', 'UserController@audit_logs')->middleware('perm:2');



});


Route::namespace('HR')
    ->prefix('hr')
    ->middleware('auth')
    ->group(function () {

        Route::get('employees/all', 'EmployeesController@all_employees');
        Route::get('ajax/employees/all', 'EmployeesController@allEmployeesDT')->name('all-employees-dt');//->middleware('perm:1');


        Route::get('employees/details/{id}', 'EmployeesController@employee_details')->name('hr-employee-details');
        Route::get('ajax/employees/loans/{_user_id}', 'EmployeesController@employeeLoansDT')->name('employee-loans-dt');//->middleware('perm:1');

        Route::post('employees/update_limit', 'EmployeesController@update_limit');


        Route::get('advance/pending', 'SalaryAdvanceController@pendingAdvance');
        Route::get('advance/pending/export', 'SalaryAdvanceController@exportPendingAdvance');
        Route::get('ajax/advance/requests/pending', 'SalaryAdvanceController@pendingAdvanceDT')->name('pending-advance-requests-dt');//->middleware('perm:1');

        Route::get('advance/approved', 'SalaryAdvanceController@approvedAdvance');
        Route::get('advance/approved/export', 'SalaryAdvanceController@exportApprovedAdvance');
        Route::get('ajax/advance/requests/approved', 'SalaryAdvanceController@approvedAdvanceDT')->name('approved-advance-requests-dt');//->middleware('perm:1');

        Route::get('advance/rejected', 'SalaryAdvanceController@rejectedAdvance');
        Route::get('advance/rejected/export', 'SalaryAdvanceController@exportRejectedAdvance');
        Route::get('ajax/advance/requests/rejected', 'SalaryAdvanceController@rejectedAdvanceDT')->name('rejected-advance-requests-dt');//->middleware('perm:1');

        Route::get('advance/amendment', 'SalaryAdvanceController@amendmentAdvance');
        Route::get('advance/amendment/export', 'SalaryAdvanceController@exportAmendmentAdvance');
        Route::get('ajax/advance/requests/amendment', 'SalaryAdvanceController@amendmentAdvanceDT')->name('amendment-advance-requests-dt');//->middleware('perm:1');

        Route::get('advance/details/{application_id}', 'SalaryAdvanceController@advance_application_details')->name('advance-hr-application-details');

        Route::post('advance/requests/approve', 'SalaryAdvanceController@approve_request');//->middleware('perm:1');
        Route::post('advance/requests/reject', 'SalaryAdvanceController@reject_request');//->middleware('perm:1');
        Route::post('advance/requests/amendment', 'SalaryAdvanceController@request_amendment');//->middleware('perm:1');


    });



