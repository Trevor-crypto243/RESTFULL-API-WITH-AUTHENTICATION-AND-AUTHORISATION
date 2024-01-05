<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_fees', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('loan_product_id');
            $table->foreign('loan_product_id')->references('id')->on('loan_products');

            $table->string('name');

            $table->double('amount',12,2);

            $table->enum('amount_type',['PERCENTAGE',"AMOUNT"]);

            $table->enum('frequency',['MONTHLY',"ONE-OFF"]);


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
        Schema::dropIfExists('loan_fees');
    }
}
