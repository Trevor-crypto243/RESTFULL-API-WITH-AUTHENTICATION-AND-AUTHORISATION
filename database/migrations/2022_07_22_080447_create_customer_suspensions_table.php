<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerSuspensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_suspensions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('customer_profile_id');
            $table->foreign('customer_profile_id')->references('id')->on('customer_profiles');

            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');

            $table->string('action_type'); //suspend, block unsuspend, unblock, etc
            $table->text('reason');

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
        Schema::dropIfExists('customer_suspensions');
    }
}
