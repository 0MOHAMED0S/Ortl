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
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('package_id')->nullable();
            $table->integer('minutes');
            $table->decimal('price', 8, 2);
            $table->string('recipient_name');
            $table->string('occasion');
            $table->text('message')->nullable();
            $table->string('coupon_code')->unique()->nullable();
            $table->string('transaction_id')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->enum('status', ['active', 'claimed'])->default('active');
            $table->foreignId('claimed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    
    public function down(): void
    {
        Schema::dropIfExists('gift_cards');
    }
};
