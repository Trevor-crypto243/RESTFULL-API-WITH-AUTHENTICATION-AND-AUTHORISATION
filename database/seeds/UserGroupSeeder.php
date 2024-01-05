<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('user_groups')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $userGroup = new \App\UserGroup();
        $userGroup->name = 'SUPER ADMIN';
        $userGroup->save();

        $userGroup = new \App\UserGroup();
        $userGroup->name = 'HR manager';
        $userGroup->save();

        $userGroup = new \App\UserGroup();
        $userGroup->name = 'Quicksava Admin';
        $userGroup->save();

        $userGroup = new \App\UserGroup();
        $userGroup->name = 'Customer';
        $userGroup->save();

    }
}
