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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10);
            $table->string('paymob_order_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->foreignId('coupon_id')
                ->nullable()
                ->constrained('coupons')
                ->onDelete('set null');
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
