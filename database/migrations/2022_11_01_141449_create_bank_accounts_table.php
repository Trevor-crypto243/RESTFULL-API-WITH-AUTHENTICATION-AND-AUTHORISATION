<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('company_id')->nullable();

            $table->enum('type',['INDIVIDUAL','COMPANY'])->default('INDIVIDUAL');

            $table->unsignedBigInteger('bank_id');
            $table->foreign('bank_id')->references('id')->on('banks');

            $table->unsignedBigInteger('bank_branch_id');
            $table->foreign('bank_branch_id')->references('id')->on('bank_branches');

            $table->text('account_name');

            $table->string('account_number');

            $table->text('atm_url');

            $table->boolean('approved')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_accounts');
    }
}
