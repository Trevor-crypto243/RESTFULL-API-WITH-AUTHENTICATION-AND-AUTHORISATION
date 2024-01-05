<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    public function user() {
        return $this->belongsTo(User::class,'user_id');
    }

    public function employer() {
        return $this->belongsTo(Employer::class,'employer_id');
    }

    public function toArray() {
        $data = parent::toArray();
        $data['monthly_limit'] = number_format(($this->max_limit*100)/113.33);
        $data['employer_name'] = optional($this->employer)->business_name;
        $data['business_logo_url'] = optional($this->employer)->business_logo_url;
        $data['business_address'] = optional($this->employer)->business_address;
        $data['business_address'] = optional($this->employer)->business_address;
        $data['business_email'] = optional($this->employer)->business_email;
        $data['business_phone_no'] = optional($this->employer)->business_phone_no;
        return $data;
    }
}
