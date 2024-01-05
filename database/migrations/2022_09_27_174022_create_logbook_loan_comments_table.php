<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogbookLoanCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logbook_loan_comments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('logbook_loan_id');
            $table->foreign('logbook_loan_id')
                ->references('id')->on('logbook_loans');

            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')
                ->references('id')->on('users');

            $table->text('comment');

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
        Schema::dropIfExists('logbook_loan_comments');
    }
}
