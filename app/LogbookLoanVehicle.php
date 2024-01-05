<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogbookLoanVehicle extends Model
{

    public function make() {
        return $this->belongsTo(VehicleMake::class,'vehicle_make_id');
    }

    public function model() {
        return $this->belongsTo(VehicleModel::class,'vehicle_model_id');
    }


    public function toArray() {
        $data = parent::toArray();
        $data['make'] = optional($this->make)->make;
        $data['model'] = optional($this->model)->model;
        return $data;
    }
}
