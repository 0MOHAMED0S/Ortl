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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Link to User
            $table->foreignId('country_id')->constrained()->onDelete('cascade'); // Link to Country
            $table->string('phone');
            $table->string('address')->nullable();
            $table->string('qualification')->nullable(); // الشهادة
            $table->string('professional_status')->nullable(); // الحالة المهنية
            $table->enum('gender', ['male', 'female']);
            $table->string('profile_photo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
