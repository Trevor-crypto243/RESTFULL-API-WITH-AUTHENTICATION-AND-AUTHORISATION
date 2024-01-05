<?php

use Illuminate\Database\Seeder;

class BankBranchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\BankBranch::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $json = File::get("database/data/bank_branches.json");
        $banks = json_decode($json);

        foreach ($banks as $key => $value) {

            $bank = \App\Bank::where('bank_name',$value->bank_name)->first();

            if (!is_null($bank)){
                \App\BankBranch::create([
                    "bank_id" => $bank->id,
                    "sort_code" => $value->sort_code,
                    "branch_name" => $value->branch_name
                ]);
            }
        }
    }
}
