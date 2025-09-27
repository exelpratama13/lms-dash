<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('booking_trx_id')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('pricing_id')->constrained('pricings')->onDelete('cascade');
            $table->foreignId('course_batch_id')->constrained('course_batches')->onDelete('cascade');
            $table->integer('sub_total_amount');
            $table->integer('grand_total_amount');
            $table->integer('total_tax_amount');
            $table->boolean('is_paid')->default(false);
            $table->string('payment_type')->nullable();
            $table->string('proof')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
