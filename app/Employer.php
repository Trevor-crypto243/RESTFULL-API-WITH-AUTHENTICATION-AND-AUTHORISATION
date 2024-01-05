<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    public function loan_period_matrices() {
        return $this->hasMany(AdvanceLoanPeriodMatrix::class,'employer_id');
    }

    public function employees() {
        return $this->hasMany(Employee::class,'employer_id');
    }
}
