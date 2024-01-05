<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlertsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('alerts')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        $alert = new \App\Alert();
        $alert->type = 'DAILY_DISBURSEMENT';
        $alert->recipient = '254713653112';
        $alert->save();

        $alert = new \App\Alert();
        $alert->type = 'HOURLY_BALANCE';
        $alert->recipient = '254713653112';
        $alert->save();


        $alert = new \App\Alert();
        $alert->type = 'FRAUD_ALERT';
        $alert->recipient = '254713653112';
        $alert->save();



    }

}
