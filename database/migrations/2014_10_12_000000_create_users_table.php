<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id')->unique();
            $table->foreign('wallet_id')
                ->references('id')->on('wallets');

            $table->unsignedBigInteger('user_group');
            $table->foreign('user_group')->references('id')->on('user_groups');


            $table->string('name');
            $table->string('surname');
            $table->string('email')->unique();
            $table->string('phone_no')->unique();
            $table->string('id_no')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

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
        Schema::dropIfExists('users');
    }
}