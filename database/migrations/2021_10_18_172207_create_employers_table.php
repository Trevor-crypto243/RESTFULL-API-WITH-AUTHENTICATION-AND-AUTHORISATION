<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('wallet_id')->unique();
            $table->foreign('wallet_id')
                ->references('id')->on('wallets');

            $table->string('business_name');
            $table->string('business_desc');
            $table->text('business_logo_url');
            $table->text('business_logo_filename');
            $table->string('business_address');
            $table->string('business_reg_no');
            $table->string('business_email');
            $table->string('business_phone_no');

            $table->boolean('salary_advance')->default(true);
            $table->boolean('invoice_discounting')->default(false);

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
        Schema::dropIfExists('employers');
    }
}
