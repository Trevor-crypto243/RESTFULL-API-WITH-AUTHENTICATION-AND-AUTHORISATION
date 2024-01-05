<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvanceRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advance_repayments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');

            $table->unsignedBigInteger('employer_id');
            $table->foreign('employer_id')->references('id')->on('employers');

            $table->string('payroll_no');

            $table->double('amount',12,2);

            $table->boolean('employee_exists')->default(false);

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
        Schema::dropIfExists('inua_repayments');
    }
}
