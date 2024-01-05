<?php

use Illuminate\Database\Seeder;


class BanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Bank::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $json = File::get("database/data/banks.json");
        $banks = json_decode($json);

        foreach ($banks as $key => $value) {
            \App\Bank::create([
                "swift_code" => $value->swift_code,
                "bank_name" => $value->bank_name
            ]);
        }
    }
}
