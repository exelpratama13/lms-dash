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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('score');
            $table->boolean('passed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     */
    public function down()
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
