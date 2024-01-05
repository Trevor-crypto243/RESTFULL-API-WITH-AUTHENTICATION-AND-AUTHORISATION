<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvanceLoanPeriodMatricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advance_loan_period_matrices', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employer_id');
            $table->foreign('employer_id')
                ->references('id')->on('employers');

            $table->integer('employment_period_from');
            $table->integer('employment_period_to');
            $table->integer('max_loan_period');

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
        Schema::dropIfExists('inua_loan_period_matrices');
    }
}
