<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployerLoanProduct extends Model
{
    public function employer() {
        return $this->belongsTo(Employer::class,'employer_id');
    }

    public function loan_product() {
        return $this->belongsTo(LoanProduct::class,'loan_product_id');
    }

    public function toArray() {
        //$data = parent::toArray();
        $data = $this->loan_product;

        return $data;
    }
}
