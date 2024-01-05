<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    public function get_permission() {
        return $this->belongsTo(Permission::class,'permission_id');
    }
}
