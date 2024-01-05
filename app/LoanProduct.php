<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    public function fees() {
        return $this->hasMany(LoanFee::class);
    }

    public function matrices() {
        return $this->hasMany(InterestRateMatrix::class);
    }

    public function toArray() {
        $data = parent::toArray();
        $data['fees'] = $this->fees;

        return $data;
    }
}
