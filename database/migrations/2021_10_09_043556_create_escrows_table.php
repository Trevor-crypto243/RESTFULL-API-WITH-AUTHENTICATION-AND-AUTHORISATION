<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEscrowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('escrows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->foreign('wallet_id')
                ->references('id')->on('wallets')
                ->onUpdate('cascade')
                ->onDelete('no action');

            $table->decimal('amount',13,2);
            $table->string('msisdn');
            $table->string('conversation_id')->unique();
            $table->boolean('complete')->default(false);
            $table->enum('status',['SUCCEEDED', 'FAILED'])->default('SUCCEEDED');
            $table->string('description')->nullable();
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
        Schema::dropIfExists('escrows');
    }
}
