<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    protected $fillable = ['created_by','action'];

    public function creator() {
        return $this->belongsTo(User::class,'created_by');
    }
}
