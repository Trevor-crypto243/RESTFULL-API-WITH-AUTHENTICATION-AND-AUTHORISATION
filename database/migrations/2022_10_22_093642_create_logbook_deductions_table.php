<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogbookDeductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logbook_deductions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('logbook_loan_id');
            $table->foreign('logbook_loan_id')
                ->references('id')->on('logbook_loans');

            $table->string('deduction_name');
            $table->decimal('amount',13,2);
            $table->string('type');

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
        Schema::dropIfExists('logbook_deductions');
    }
}
