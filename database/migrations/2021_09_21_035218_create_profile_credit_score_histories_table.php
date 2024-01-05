<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfileCreditScoreHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profile_credit_score_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('customer_profile_id');
            $table->foreign('customer_profile_id')
                ->references('id')
                ->on('customer_profiles')
                ->onDelete('cascade');

            $table->float('previous_max_limit',12,2);
            $table->float('current_max_limit',12,2);
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
        Schema::dropIfExists('profile_credit_score_histories');
    }
}
