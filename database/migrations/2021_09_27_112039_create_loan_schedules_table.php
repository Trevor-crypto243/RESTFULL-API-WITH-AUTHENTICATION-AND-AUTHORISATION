<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_schedules', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('loan_request_id');
            $table->foreign('loan_request_id')->references('id')->on('loan_requests');

            $table->dateTime('payment_date');
            $table->decimal('beginning_balance',12,2);
            $table->decimal('scheduled_payment',12,2);
            $table->decimal('interest_paid',12,2);
            $table->decimal('principal_paid',12,2);
            $table->decimal('ending_balance',12,2);
            $table->decimal('actual_payment_done',12,2)->default(0);

            $table->enum('status',['UNPAID','PAID','PARTIALLY_PAID'])->default('UNPAID');

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
        Schema::dropIfExists('loan_schedules');
    }
}
