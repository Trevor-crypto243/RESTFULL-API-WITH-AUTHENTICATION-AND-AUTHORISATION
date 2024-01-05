<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Escrow extends Model
{
    public function wallet() {
        return $this->belongsTo(Wallet::class,'wallet_id');
    }
}
