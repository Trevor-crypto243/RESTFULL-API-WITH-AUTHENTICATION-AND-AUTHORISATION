<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','surname','wallet_id','user_group', 'email','id_no','phone_no', 'password',

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role() {
        return $this->belongsTo(UserGroup::class,'user_group');
    }

    public function wallet() {
        return $this->belongsTo(Wallet::class,'wallet_id');
    }

    public function loans() {
        return $this->hasMany(LoanRequest::class);
    }

    public function customerProfile() {
        return $this->hasOne(CustomerProfile::class);
    }

    public function toArray() {
        $data = parent::toArray();
        $data['max_limit'] = optional($this->customerProfile)->max_limit;
        $data['is_checkoff'] = optional($this->customerProfile)->is_checkoff;
        $data['wallet_balance'] = number_format(optional($this->wallet)->current_balance);
        $data['status'] = optional($this->customerProfile)->status;
        $data['blocked_expiry_date'] = optional($this->customerProfile)->blocked_expiry_date	;

        return $data;
    }

}
