<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    public function user() {
        return $this->belongsTo(User::class,'user_id');
    }

    public function company() {
        return $this->belongsTo(Company::class,'company_id');
    }

    public function bank() {
        return $this->belongsTo(Bank::class,'bank_id');
    }

    public function branch() {
        return $this->belongsTo(BankBranch::class,'bank_branch_id');
    }

    public function toArray() {
        $data = parent::toArray();
        $data['bank_name'] = optional($this->bank)->bank_name;
        $data['swift_code'] = optional($this->bank)->swift_code;
        $data['branch_name'] = optional($this->branch)->branch_name;
        $data['sort_code'] = optional($this->branch)->sort_code;

        return $data;
    }
}
