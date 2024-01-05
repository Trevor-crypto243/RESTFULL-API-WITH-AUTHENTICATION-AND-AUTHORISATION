<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeIncomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_incomes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employer_id');
            $table->foreign('employer_id')
                ->references('id')->on('employers');

            $table->string('payroll_no');
            $table->string('id_no');

            $table->decimal('gross_salary',12,2)->default(0);
            $table->decimal('basic_salary',12,2)->default(0);
            $table->decimal('net_salary',12,2)->default(0);

            $table->date('employment_date');


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
        Schema::dropIfExists('employee_incomes');
    }
}
