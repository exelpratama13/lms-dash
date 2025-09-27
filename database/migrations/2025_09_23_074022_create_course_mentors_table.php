<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_mentors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('job')->nullable();
            $table->text('about')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     */
    public function down()
    {
        Schema::dropIfExists('course_mentors');
    }
};
