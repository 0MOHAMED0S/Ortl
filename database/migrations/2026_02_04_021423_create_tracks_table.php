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
        Schema::create('tracks', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('icon')->nullable();
                $table->string('target_group')->nullable();
                $table->string('marketing_value')->nullable();
                $table->text('description');
                $table->enum('status', ['active', 'stopped'])->default('active');
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
