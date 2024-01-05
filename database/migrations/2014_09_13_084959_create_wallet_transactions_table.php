<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->foreign('wallet_id')
                ->references('id')->on('wallets')
                ->onUpdate('cascade')
                ->onDelete('no action');

            $table->decimal('amount',13,2);
            $table->decimal('previous_balance',13,2);
            $table->enum('transaction_type',['CR','DR']);
            $table->string('source');
            $table->text('trx_id');
            $table->text('narration');

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
        Schema::dropIfExists('wallet_transactions');
    }
}
