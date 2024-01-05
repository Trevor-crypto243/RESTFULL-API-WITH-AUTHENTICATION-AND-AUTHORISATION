<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProfileCreditScoreHistory extends Model
{
    public function customer_profile() {
        return $this->belongsTo(CustomerProfile::class,'customer_profile_id');
    }
}
