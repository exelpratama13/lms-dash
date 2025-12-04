<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Change the column to be nullable
            $table->foreignId('course_batch_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Revert the column to be non-nullable
            // Note: This assumes existing data in the column is not null.
            // A default value might be needed in a real-world scenario if nulls exist.
            $table->foreignId('course_batch_id')->nullable(false)->change();
        });
    }
};
