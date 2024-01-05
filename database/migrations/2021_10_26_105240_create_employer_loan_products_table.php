<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployerLoanProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employer_loan_products', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('loan_product_id');
            $table->foreign('loan_product_id')
                ->references('id')->on('loan_products');

            $table->unsignedBigInteger('employer_id');
            $table->foreign('employer_id')
                ->references('id')->on('employers');


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
        Schema::dropIfExists('employer_loan_products');
    }
}
