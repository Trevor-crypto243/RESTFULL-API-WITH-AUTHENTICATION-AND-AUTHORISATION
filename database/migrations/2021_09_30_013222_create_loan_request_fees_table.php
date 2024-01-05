<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanRequestFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_request_fees', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('loan_request_id');
            $table->foreign('loan_request_id')->references('id')->on('loan_requests');

            $table->string('fee');
            $table->double('amount',10,2);
            $table->enum('frequency',['MONTHLY','ONE-OFF'])->default('ONE-OFF');
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
        Schema::dropIfExists('loan_request_fees');
    }
}
