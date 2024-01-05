<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleMake extends Model
{
    public function models() {
        return $this->hasMany(VehicleModel::class,'make_id');
    }
}
