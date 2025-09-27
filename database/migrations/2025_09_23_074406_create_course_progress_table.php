<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('course_progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('progress_percentage')->default(0);
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('course_batch_id')->constrained('course_batches')->onDelete('cascade');
            $table->foreignId('course_section_id')->constrained('course_sections')->onDelete('cascade');
            $table->foreignId('course_content_id')->constrained('course_contents')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_progresses');
    }
};
