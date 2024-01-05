<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('max_period_months');
            $table->double('interest_rate',12,2);
            $table->enum('fee_application', ['BEFORE DISBURSEMENT', 'AFTER DISBURSEMENT']);
            $table->text('description');
            $table->double('min_amount',12,2);
            $table->double('max_amount',12,2);
            $table->integer('closing_date')->default(24);
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
        Schema::dropIfExists('loan_products');
    }
}
