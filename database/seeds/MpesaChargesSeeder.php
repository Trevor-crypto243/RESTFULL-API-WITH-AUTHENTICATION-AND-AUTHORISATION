<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MpesaChargesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('mpesa_charges')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $charge = new \App\MpesaCharge();
        $charge->min = 10;
        $charge->max = 100;
        $charge->charge = 0;
        $charge->save();

        $charge = new \App\MpesaCharge();
        $charge->min = 101;
        $charge->max = 1500;
        $charge->charge = 4;
        $charge->save();

        $charge = new \App\MpesaCharge();
        $charge->min = 1501;
        $charge->max = 5000;
        $charge->charge = 8;
        $charge->save();

        $charge = new \App\MpesaCharge();
        $charge->min = 5001;
        $charge->max = 20000;
        $charge->charge = 10;
        $charge->save();

        $charge = new \App\MpesaCharge();
        $charge->min = 20001;
        $charge->max = 150000;
        $charge->charge = 12;
        $charge->save();
    }
}
