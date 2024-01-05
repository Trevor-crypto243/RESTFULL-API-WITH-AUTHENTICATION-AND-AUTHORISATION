<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('loan_request_id');
            $table->foreign('loan_request_id')->references('id')->on('loan_requests');

            $table->decimal('amount_repaid',13,2);
            $table->decimal('outstanding_balance',13,2);
            $table->string('transaction_receipt_number');
            $table->string('payment_channel');
            $table->text('description')->nullable();

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
        Schema::dropIfExists('loan_repayments');
    }
}
