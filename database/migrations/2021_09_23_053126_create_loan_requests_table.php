<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('loan_product_id');
            $table->foreign('loan_product_id')->references('id')->on('loan_products');

            $table->double('interest_rate',12,2);

            $table->double('amount_requested',12,2);
            $table->double('amount_disbursable',12,2);
            $table->double('fees',12,2);
            $table->integer('period_in_months');
            $table->enum('approval_status', ['PENDING','APPROVED','REJECTED'])->default('PENDING');
            $table->enum('repayment_status',['PENDING','PARTIALLY_PAID','PAID','CANCELLED'])->default('PENDING');
            $table->dateTime('approved_date')->nullable();
            $table->text('reject_reason')->nullable();


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
        Schema::dropIfExists('loan_requests');
    }
}
