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
        Schema::create('recitation_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->string('channel_name')->unique();

            $table->dateTime('start_at');
            $table->dateTime('end_at');

            $table->integer('duration_minutes');
            $table->integer('max_participants')->default(100);

            $table->enum('status', ['scheduled', 'live', 'ended'])->default('scheduled');

            $table->boolean('is_teacher_joined')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recitation_sessions');
    }
};
