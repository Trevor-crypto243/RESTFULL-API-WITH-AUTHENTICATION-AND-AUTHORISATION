<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuspenseAmountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suspense_amounts', function (Blueprint $table) {
            $table->id();
            $table->string('phone_no');
            $table->string('name');
            $table->string('transaction_code');
            $table->double('amount',12,2);
            $table->boolean('refunded')->default(false);
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
        Schema::dropIfExists('suspense_amounts');
    }
}
