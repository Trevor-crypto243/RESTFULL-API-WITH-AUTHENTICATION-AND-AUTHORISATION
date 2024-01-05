<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    public function getCreatedAtAttribute($value)
    {
        $date = Carbon::parse($value);
        return $date->isoFormat('MMM Do YYYY HH:mm:ss');
    }
    public function getAmountAttribute($value)
    {
        return number_format($value);
    }

}
