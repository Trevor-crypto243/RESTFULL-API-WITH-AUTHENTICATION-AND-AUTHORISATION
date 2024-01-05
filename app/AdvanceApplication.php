<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AdvanceApplication extends Model
{
    public function user() {
        return $this->belongsTo(User::class,'user_id');
    }

    public function product() {
        return $this->belongsTo(LoanProduct::class,'loan_product_id');
    }

    public function employer() {
        return $this->belongsTo(Employer::class,'employer_id');
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->isoFormat('MMM Do YYYY HH:mm:ss');
    }

    public function toArray() {
        $data = parent::toArray();
        $data['amount_requested_formatted'] = number_format($this->amount_requested);
        $data['product_name'] = optional($this->product)->name;
        $data['employer_name'] = optional($this->employer)->business_name;
        return $data;
    }
}
