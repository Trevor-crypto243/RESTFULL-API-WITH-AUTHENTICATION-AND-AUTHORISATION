<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class PaybillBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('paybill_balances')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $pb = new \App\PaybillBalance();
        $pb->type = 'B2C';
        $pb->shortcode = '000000';
        $pb->mmf = 0.0;
        $pb->utility = 0.0;
        $pb->save();

        $pb = new \App\PaybillBalance();
        $pb->type = 'C2B';
        $pb->shortcode = '111111';
        $pb->mmf = 0.0;
        $pb->utility = 0.0;
        $pb->save();
    }
}
