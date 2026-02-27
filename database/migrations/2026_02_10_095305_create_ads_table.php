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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable(); // Added for the lower text
            $table->string('image')->nullable();    // Optional if using background colors
            $table->string('bg_color')->default('linear-gradient(135deg, #2dd5a7 0%, #069382 100%)');

            // ✅ إضافة حقل الكوبون (اختياري)
            $table->foreignId('coupon_id')
                  ->nullable()
                  ->constrained('coupons')
                  ->nullOnDelete(); // إذا تم حذف الكوبون، اجعل هذا الحقل Null ولا تحذف الإعلان

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
