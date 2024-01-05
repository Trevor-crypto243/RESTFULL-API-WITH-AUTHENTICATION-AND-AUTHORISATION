<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LoanRequest extends Model
{
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

    public function toArray() {
        //$data = parent::toArray();
        $data['id'] = $this->id;
        $data['loan_product_id'] = $this->loan_product_id;
        $data['interest_rate'] = optional($this->product)->interest_rate;
        $data['amount_requested'] = $this->amount_requested;
        $data['amount_disbursable'] = $this->amount_disbursable;
        $data['fees'] = $this->fees;
        $data['period_in_months'] = $this->period_in_months;
        $data['approval_status'] = $this->approval_status;
        $data['repayment_status'] = $this->repayment_status;
        $data['approved_date'] = $this->approved_date;
        $data['reject_reason'] = $this->reject_reason;
        $data['created_at'] = $this->created_at;

        $data['product_name'] = optional($this->product)->name;
        $data['product_description'] = optional($this->product)->description;
        $data['request_fees'] = $this->request_fees;
        return $data;
    }
}
