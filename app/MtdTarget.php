<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MtdTarget extends Model
{
    protected $fillable = ['employer_id','year', 'month', 'target_loans', 'target_loans_value'];

    public function employer() {
        return $this->belongsTo(Employer::class,'employer_id');
    }
}
