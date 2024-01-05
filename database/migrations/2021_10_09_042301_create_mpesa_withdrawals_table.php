<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMpesaWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpesa_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount',13,2);
            $table->string('receipt');
            $table->string('msisdn');
            $table->string('date_time');
            $table->string('name');
            $table->boolean('recipient_registered');
            $table->decimal('utility_balance',13,2);
            $table->decimal('mmf_balance',13,2);
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
        Schema::dropIfExists('mpesa_withdrawals');
    }
}
