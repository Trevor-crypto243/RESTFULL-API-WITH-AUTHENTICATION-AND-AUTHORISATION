<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = ['swift_code','bank_name'];

    public function branches() {
        return $this->hasMany(BankBranch::class,'bank_id');
    }
}
