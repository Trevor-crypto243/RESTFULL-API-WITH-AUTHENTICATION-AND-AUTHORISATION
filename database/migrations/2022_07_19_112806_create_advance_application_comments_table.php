<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvanceApplicationCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advance_application_comments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');

            $table->unsignedBigInteger('advance_application_id');
            $table->foreign('advance_application_id')->references('id')->on('advance_applications');

            $table->enum('comment_origin',['EMPLOYER','SYSTEM']);

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
        Schema::dropIfExists('inua_application_comments');
    }
}
