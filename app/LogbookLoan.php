<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LogbookLoan extends Model
{

    public function user() {
        return $this->belongsTo(User::class,'user_id');
    }

    public function company_directors() {
        return $this->hasMany(LogbookCompanyDirector::class,'logbook_loan_id');
    }

    public function product() {
        return $this->belongsTo(LoanProduct::class,'loan_product_id');
    }

    public function vehicles() {
        return $this->hasMany(LogbookLoanVehicle::class,'logbook_loan_id');
    }

    public function toArray()
    {
        return collect($this->getAttributes())->except([ 'updated_at'])->filter();
    }
}
