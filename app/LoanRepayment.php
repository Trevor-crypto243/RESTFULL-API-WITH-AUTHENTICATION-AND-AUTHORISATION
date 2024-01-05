<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanRepayment extends Model
{
    public function loan_request() {
        return $this->belongsTo(LoanRequest::class);
    }
}
