<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DashLogbookLoan extends Model
{

    protected $table = 'logbook_loans';

    public function user() {
        return $this->belongsTo(User::class,'user_id');
    }

    public function product() {
        return $this->belongsTo(LoanProduct::class,'loan_product_id');
    }

    public function company_directors() {
        return $this->hasMany(LogbookCompanyDirector::class,'logbook_loan_id');
    }

    public function deductions() {
        return $this->hasMany(LogbookDeduction::class,'logbook_loan_id');
    }

    public function vehicles() {
        return $this->hasMany(LogbookLoanVehicle::class,'logbook_loan_id');
    }
}
