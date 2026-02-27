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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('teacher_application_id')->constrained('teacher_applications')->onDelete('cascade');
            $table->unsignedInteger('minutes')->default(0);
            $table->decimal('salary', 8, 2); // الراتب
            $table->string('profile_photo_path'); // مسار الصورة

            // إضافة حقل حالة الاتصال
            $table->boolean('is_online')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
