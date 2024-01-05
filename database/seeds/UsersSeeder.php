<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //        DB::statement('SET session_replication_role = \'replica\';');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        //        DB::statement('SET session_replication_role = \'origin\';');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $user = new \App\User();
        $user->name = 'SUPER ADMIN';
        $user->user_group = 1; //super admin
        $user->wallet_id = 1;
        $user->name = "Super";
        $user->surname = "Admin";
        $user->phone_no = "+254710000000";
        $user->id_no = "ADMIN123";
        $user->email = "admin@quicksava.com";
        $user->password = bcrypt("Qu1cksava23");
        $user->save();
    }
}
