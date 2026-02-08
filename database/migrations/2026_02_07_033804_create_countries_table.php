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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');                  // Country name
            $table->string('code', 2);              // ISO code, e.g., "US"
            $table->string('currency_code');        // e.g., "USD"
            $table->string('currency_name');        // e.g., "US Dollar"
            $table->string('currency_symbol')->nullable(); // e.g., "$"
            $table->decimal('rate_to_usd', 15, 6)->nullable(); // 1 USD = X local
            $table->string('phone_code')->nullable(); // e.g., +20
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
