<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_batches', function (Blueprint $table) {
            $table->foreignId('pricing_id')
                ->nullable()
                ->after('quota')
                ->constrained('pricings')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('course_batches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pricing_id');
        });
    }
};