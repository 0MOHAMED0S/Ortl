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
        Schema::create('teacher_applications', function (Blueprint $table) {
            $table->id();

            // Personal
            $table->string('full_name');
            $table->enum('gender', ['male', 'female']);
            $table->string('email');
            $table->string('phone');
            $table->string('origin_country')->nullable();
            $table->string('residence_location');

            // Academic
            $table->string('qualification');
            $table->json('languages');

            // Experience
            $table->unsignedInteger('experience_years')->nullable();
            $table->unsignedInteger('work_hours')->nullable();
            $table->enum('online_experience', ['beginner', 'intermediate', 'expert'])->nullable();
            $table->enum('internet_quality', ['weak', 'acceptable', 'good', 'excellent'])->nullable();
            $table->enum('tech_skills', ['beginner', 'intermediate', 'advanced'])->nullable();

            // Attachments
            $table->text('ijazas_text')->nullable();
            $table->string('cv_pdf_path')->nullable();

            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'not_active'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_applications');
    }
};
