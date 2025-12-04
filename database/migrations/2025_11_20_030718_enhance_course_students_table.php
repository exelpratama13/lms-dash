<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_students', function (Blueprint $table) {
            $table->foreignId('pricing_id')
                ->nullable()
                ->after('course_batch_id')
                ->constrained('pricings')
                ->onDelete('set null');

            $table->timestamp('access_starts_at')
                ->default(now())
                ->after('pricing_id');

            $table->enum('enrollment_type', ['batch', 'on_demand'])
                ->default('on_demand')
                ->after('access_expires_at');

            $table->boolean('is_active')
                ->default(true)
                ->after('enrollment_type');

            // Add indexes
            $table->index(['user_id', 'course_id', 'is_active']);
            $table->index(['course_batch_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('course_students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pricing_id');
            $table->dropColumn([
                'pricing_id',
                'access_starts_at',
                'enrollment_type',
                'is_active'
            ]);
            $table->dropIndex(['user_id', 'course_id', 'is_active']);
            $table->dropIndex(['course_batch_id', 'is_active']);
        });
    }
};