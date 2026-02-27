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
        Schema::create('teacher_application_tracks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_application_id')
                ->constrained('teacher_applications')
                ->cascadeOnDelete();

            $table->foreignId('track_id')
                ->constrained('tracks')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['teacher_application_id', 'track_id'],
                'teacher_app_track_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_application_tracks');
    }
};
