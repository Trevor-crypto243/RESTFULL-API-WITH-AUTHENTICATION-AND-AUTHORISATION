<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LoanSchedule extends Model
{
    public function getPaymentDateAttribute($value)
    {
        return Carbon::parse($value)->isoFormat('MMM Do YYYY');
    }

    public function loan() {
        return $this->belongsTo(LoanRequest::class,'loan_request_id');
    }


    public function toArray() {
        $data = parent::toArray();
        $data['scheduled_payment'] = number_format($this->scheduled_payment,2);
        $data['outstanding_balance'] = number_format($this->scheduled_payment - $this->actual_payment_done,2);
        return $data;
    }
}
