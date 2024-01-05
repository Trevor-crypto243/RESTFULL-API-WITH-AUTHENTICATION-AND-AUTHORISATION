<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogbookLoanAdditionalFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logbook_loan_additional_files', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('logbook_loan_id');
            $table->foreign('logbook_loan_id')
                ->references('id')->on('logbook_loans');

            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')
                ->references('id')->on('users');

            $table->string('file_name');

            $table->text('file_url');

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
        Schema::dropIfExists('logbook_loan_additional_files');
    }
}
