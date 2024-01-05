<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model
{
    public function user() {
        return $this->belongsTo(User::class,'user_id');
    }

    public function credit_history() {
        return $this->hasMany(ProfileCreditScoreHistory::class);
    }
}
