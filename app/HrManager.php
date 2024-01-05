<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HrManager extends Model
{
    public function user() {
        return $this->belongsTo(User::class,'user_id');
    }

    public function employer() {
        return $this->belongsTo(Employer::class,'employer_id');
    }
}
