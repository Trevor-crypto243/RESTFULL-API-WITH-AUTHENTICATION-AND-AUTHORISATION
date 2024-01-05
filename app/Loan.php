<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $table = 'loan_requests';

    public function product() {
        return $this->belongsTo(LoanProduct::class,'loan_product_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function request_fees() {
        return $this->hasMany(LoanRequestFee::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->isoFormat('MMM Do YYYY HH:mm:ss');
    }

    public function getApprovedDateAttribute($value)
    {
        if ($value!=null)
            return Carbon::parse($value)->isoFormat('MMM Do YYYY HH:mm:ss');
        else
            return null;
    }

}
