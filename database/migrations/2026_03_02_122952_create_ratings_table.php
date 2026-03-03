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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The Student
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade'); // The Teacher
            $table->unsignedTinyInteger('rating'); // 1 to 5
            $table->text('comment')->nullable();

            // Polymorphic columns: rateable_id and rateable_type
            $table->morphs('rateable');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
