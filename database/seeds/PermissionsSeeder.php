<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $permission = new \App\Permission();
        $permission->id = 1;
        $permission->name = 'Manage Users'; //1
        $permission->save();


        $permission = new \App\Permission();
        $permission->id = 2;
        $permission->name = 'View Audit Logs'; //2
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 3;
        $permission->name = 'View customers'; //3
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 4;
        $permission->name = 'Messaging'; //4
        $permission->save();


        $permission = new \App\Permission();
        $permission->id = 6;
        $permission->name = 'Reports'; //6
        $permission->save();


        $permission = new \App\Permission();
        $permission->id = 9;
        $permission->name = 'View salary advance requests'; //9
        $permission->save();


        $permission = new \App\Permission();
        $permission->id = 10;
        $permission->name = 'Approve salary advance requests'; //10
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 11;
        $permission->name = 'Manage Partners/Employers'; //11
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 12;
        $permission->name = 'Approve/reject loans'; //12
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 13;
        $permission->name = 'View Loans'; //13
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 14;
        $permission->name = 'Manage Loan Products'; //14
        $permission->save();


        $permission = new \App\Permission();
        $permission->id = 16;
        $permission->name = 'Suspense Reconciliations'; //16
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 17;
        $permission->name = 'C2B Reconciliations'; //17
        $permission->save();


        $permission = new \App\Permission();
        $permission->id = 19;
        $permission->name = 'Edit employer'; //19
        $permission->save();



        $permission = new \App\Permission();
        $permission->id = 22;
        $permission->name = 'View wallet'; //22
        $permission->save();



        $permission = new \App\Permission();
        $permission->id = 25;
        $permission->name = 'View Dashboard'; //25
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 26;
        $permission->name = 'Manage Vehicle makes and models'; //26
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 27;
        $permission->name = 'Force withdraw from wallet'; //27
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 28;
        $permission->name = 'View Logbook applications'; //28
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 29;
        $permission->name = 'Edit Logbook Vehicles'; //29
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 30;
        $permission->name = 'Edit Logbook Application'; //30
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 31;
        $permission->name = 'Comment on Logbook Applications'; //31
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 32;
        $permission->name = 'Upload additional file for Logbook Applications'; //32
        $permission->save();


        $permission = new \App\Permission();
        $permission->id = 33;
        $permission->name = 'Submit Logbook Applications for Review'; //33
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 34;
        $permission->name = 'Submit Logbook Applications for Approval'; //34
        $permission->save();


        $permission = new \App\Permission();
        $permission->id = 35;
        $permission->name = 'Approve and disburse Logbook Applications'; //35
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 36;
        $permission->name = 'Reject Logbook Applications'; //36
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 37;
        $permission->name = 'Update customer limits'; //37
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 38;
        $permission->name = 'Suspend/Block customer profile'; //38
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 39;
        $permission->name = 'Freeze/Activate Wallets'; //39
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 40;
        $permission->name = 'Reject Salary Advance Requests'; //40
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 41;
        $permission->name = 'Send Amendments'; //41
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 42;
        $permission->name = 'Send to HR'; //42
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 43;
        $permission->name = 'View Managed Customers'; //43
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 44;
        $permission->name = 'Create Managed Customers'; //44
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 45;
        $permission->name = 'Apply and manage customers\' salary advance applications'; //45
        $permission->save();


        $permission = new \App\Permission();
        $permission->id = 46;
        $permission->name = 'Manage logbook loan application deductions'; //46
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 47;
        $permission->name = 'View Banks and Branches'; //47
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 48;
        $permission->name = 'Create, edit and delete Banks and Branches'; //48
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 49;
        $permission->name = 'View Bank Accounts'; //49
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 50;
        $permission->name = 'Approve and Disapprove Bank Accounts'; //50
        $permission->save();

        $permission = new \App\Permission();
        $permission->id = 51;
        $permission->name = 'Do B2C Reconciliations'; //51
        $permission->save();

    }
}
