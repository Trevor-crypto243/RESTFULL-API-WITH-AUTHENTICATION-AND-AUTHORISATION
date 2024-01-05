<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogbookLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logbook_loans', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users');

            $table->unsignedBigInteger('loan_product_id')->nullable();

            $table->unsignedBigInteger('loan_request_id')->nullable();

            $table->enum('applicant_type',['COMPANY','INDIVIDUAL']);
            $table->enum('status',['NEW','IN REVIEW','AMENDMENT','OFFER','ACTIVE','REJECTED','PAID','CANCELLED'])->default('NEW');
            $table->enum('source_of_business',['AGENT','WALK-IN','CLIENT-REFERRAL','APP'])->default('APP');
            $table->string('lead_originator')->nullable();
            $table->enum('payment_mode',['e-Wallet','M-PESA','PESALINK','EFT','RTGS'])->default('M-PESA');


            $table->decimal('requested_amount',13,2);
            $table->decimal('approved_amount',13,2)->nullable();
            $table->integer('payment_period');
            $table->text('loan_purpose');

            $table->string('personal_kra_pin')->nullable();
            $table->text('personal_kra_pin_url')->nullable();
            $table->text('id_front_url')->nullable();
            $table->text('id_back_url')->nullable();
            $table->text('passport_photo_url')->nullable();


            $table->string('company_name')->nullable();
            $table->integer('directors')->nullable();
            $table->string('company_kra_pin')->nullable();
            $table->text('company_kra_pin_url')->nullable();
            $table->string('company_reg_no')->nullable();
            $table->string('company_reg_no_url')->nullable();

            $table->text('reject_reason')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->unsignedBigInteger('submitted_for_review_by')->nullable();
            $table->unsignedBigInteger('submitted_for_approval_by')->nullable();

            $table->text('loan_form_url')->nullable();
            $table->text('offer_letter_url')->nullable();

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
        Schema::dropIfExists('logbook_loans');
    }
}
