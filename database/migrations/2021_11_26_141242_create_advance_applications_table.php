<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvanceApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advance_applications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('employer_id');
            $table->foreign('employer_id')->references('id')->on('employers');

            $table->unsignedBigInteger('loan_product_id');
            $table->foreign('loan_product_id')->references('id')->on('loan_products');

            $table->double('amount_requested',12,2);
            $table->integer('period_in_months');

            $table->string('purpose');

            $table->text('payslip_url')->nullable();

            $table->enum('quicksava_status',['PENDING','ACCEPTED','PROCESSING','REJECTED','AMENDMENT'])->default('PENDING');
            $table->enum('hr_status',['PENDING','ACCEPTED','REJECTED','AMENDMENT'])->default('PENDING');
            $table->text('quicksava_comments')->nullable();
            $table->text('hr_comments')->nullable();

            $table->enum('payment_status',['PENDING','PARTIALLY_PAID','PAID','CANCELLED'])->default('PENDING');

            $table->unsignedBigInteger('created_by')->nullable();

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
        Schema::dropIfExists('inua_applications');
    }
}
