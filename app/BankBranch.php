<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BankBranch extends Model
{
    protected $fillable = ['bank_id','sort_code','branch_name'];

    public function bank() {
        return $this->belongsTo(Bank::class,'bank_id');
    }
}
