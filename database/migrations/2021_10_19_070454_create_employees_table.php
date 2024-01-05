<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users');

            $table->unsignedBigInteger('employer_id');
            $table->foreign('employer_id')
                ->references('id')->on('employers');

            $table->string('payroll_no');

            $table->text('id_url');
            $table->text('id_filename');

            $table->text('id_back_name')->nullable();
            $table->text('id_back_url')->nullable();

            $table->text('passport_photo_url');
            $table->text('passport_photo_filename');

            $table->text('latest_payslip_url');
            $table->text('latest_payslip_filename');

            $table->date('limit_expiry');
            $table->date('employment_date');

            $table->string('nature_of_work');
            $table->string('position');
            $table->string('location');
            $table->decimal('gross_salary',12,2)->default(0);
            $table->decimal('basic_salary',12,2)->default(0);
            $table->decimal('net_salary',12,2)->default(0);
            $table->decimal('max_limit',12,2)->default(0);

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
        Schema::dropIfExists('employees');
    }
}
