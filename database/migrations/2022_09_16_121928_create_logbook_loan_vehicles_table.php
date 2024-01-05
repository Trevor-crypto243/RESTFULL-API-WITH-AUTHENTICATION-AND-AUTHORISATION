<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogbookLoanVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logbook_loan_vehicles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('logbook_loan_id');
            $table->foreign('logbook_loan_id')
                ->references('id')->on('logbook_loans');

            $table->unsignedBigInteger('vehicle_make_id');
            $table->foreign('vehicle_make_id')
                ->references('id')->on('vehicle_makes');

            $table->unsignedBigInteger('vehicle_model_id');
            $table->foreign('vehicle_model_id')
                ->references('id')->on('vehicle_models');

            $table->string('yom');
            $table->string('reg_no');
            $table->string('chassis_no');
            $table->text('logbook_url');

            $table->string('insurance_company')->nullable();
            $table->date('insurance_expiry_date')->nullable();
            $table->enum('premium_paid_by',['OWNER','COMPANY'])->default('OWNER');
            $table->integer('premium_amount_paid')->default(0);
            $table->text('icf_confirmation_form_url')->nullable();

            $table->integer('forced_sale_value')->nullable();
            $table->integer('market_value')->nullable();
            $table->text('valuation_report_url')->nullable();
            $table->date('valuation_date')->nullable();





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
        Schema::dropIfExists('logbook_loan_vehicles');
    }
}
