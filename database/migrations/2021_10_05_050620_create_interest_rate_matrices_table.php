<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInterestRateMatricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interest_rate_matrices', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('loan_product_id');
            $table->foreign('loan_product_id')->references('id')->on('loan_products');

            $table->enum('loan_period', ['1_MONTH','2_MONTHS','3_5_MONTHS','6_12_MONTHS','12_PLUS_MONTHS']);

            $table->double('new_client_interest',12,2);
            $table->double('existing_client_interest',12,2);

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
        Schema::dropIfExists('interest_rate_matrices');
    }
}
