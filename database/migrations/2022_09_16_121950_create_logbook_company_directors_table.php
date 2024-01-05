<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogbookCompanyDirectorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logbook_company_directors', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('logbook_loan_id');
            $table->foreign('logbook_loan_id')
                ->references('id')->on('logbook_loans');

            $table->string('first_name');
            $table->string('surname');
            $table->string('id_no');
            $table->text('id_front_url');
            $table->text('id_back_url')->nullable();
            $table->text('passport_photo_url');
            $table->text('kra_pin_url');


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
        Schema::dropIfExists('logbook_company_directors');
    }
}
