<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserGroup extends Model
{
    public function users() {
        return $this->hasMany(User::class,'user_group');
    }

    public function has_perm(Array $permissions_array, $role_id = false)
    {
        if (!$role_id)
            $role_id = auth()->user()->user_group;
        if ($role_id == 1)
            return true;
        $available = DB::table('user_permissions')
            ->select('id')
            ->where('group_id', '=', $role_id)
            ->whereIn('permission_id', $permissions_array)
            ->get();

        return count($available) > 0;
    }
}
