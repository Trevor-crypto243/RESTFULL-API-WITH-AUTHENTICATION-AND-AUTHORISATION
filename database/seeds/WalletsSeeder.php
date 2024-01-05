<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WalletsSeeder extends Seeder
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
        DB::table('wallets')->truncate();
        //        DB::statement('SET session_replication_role = \'origin\';');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $wallet = new \App\Wallet();
        $wallet->current_balance = 0.0;
        $wallet->previous_balance = 0.0;
        $wallet->save();
    }
}
