<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        schema::create("leads",function(Blueprint $table){
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('msisdn');
            $table->string('id_no');
            $table->string('loan_product_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

        });
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads');
    }
}
