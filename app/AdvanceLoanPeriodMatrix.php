<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdvanceLoanPeriodMatrix extends Model
{
    public function employer() {
        return $this->belongsTo(Employer::class,'employer_id');
    }
}
