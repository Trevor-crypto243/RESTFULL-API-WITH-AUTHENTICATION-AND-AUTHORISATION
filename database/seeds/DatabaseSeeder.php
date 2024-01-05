<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(WalletsSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(UserGroupSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(AlertsSeeder::class);
        $this->call(MpesaChargesSeeder::class);
        $this->call(PaybillBalanceSeeder::class);
        $this->call(BanksSeeder::class);
        $this->call(BankBranchesSeeder::class);
    }
}
