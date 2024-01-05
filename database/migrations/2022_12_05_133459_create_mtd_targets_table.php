<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMtdTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mtd_targets', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employer_id');
            $table->foreign('employer_id')
                ->references('id')->on('employers');

            $table->integer('year');
            $table->integer('month');

            $table->integer('target_loans')->default(0);
            $table->double('target_loans_value',13,2)->default(0);

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
        Schema::dropIfExists('mtd_targets');
    }
}
