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
        Schema::create('teacher_slots', function (Blueprint $table) {
        $table->id();
        $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
        $table->date('date');
        $table->time('start_time');
        $table->time('end_time');
        $table->boolean('is_booked')->default(false);
        $table->timestamps();

        // إضافة Index لتسريع عملية البحث
        $table->index(['teacher_id', 'date', 'is_booked']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_slots');
    }
};
