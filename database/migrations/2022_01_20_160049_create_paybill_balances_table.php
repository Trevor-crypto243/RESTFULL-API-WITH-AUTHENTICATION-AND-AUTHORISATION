<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaybillBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paybill_balances', function (Blueprint $table) {
            $table->id();
            $table->enum('type',['B2C','C2B']);
            $table->string('shortcode')->unique();
            $table->double('mmf',12,2);
            $table->double('utility',12,2);
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
        Schema::dropIfExists('paybill_balances');
    }
}
