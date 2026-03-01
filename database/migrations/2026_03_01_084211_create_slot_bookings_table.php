<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // هذا هو الجدول الوسيط (Pivot)
        Schema::create('slot_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // الطالب
            $table->foreignId('teacher_slot_id')->constrained('teacher_slots')->onDelete('cascade'); // الموعد

            $table->integer('deducted_minutes')->default(0); // حفظ عدد الدقائق التي تم خصمها وقت الحجز (مفيد للتقارير والاسترجاع)
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');

            $table->timestamps();

            // Index لتسريع استرجاع حجوزات الطالب
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_bookings');
    }
};
