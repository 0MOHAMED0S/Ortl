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
            $table->foreignId('package_id')->nullable(); // إذا كان لديك جدول للباقات، أو يمكنك وضع الدقائق مباشرة

            // تفاصيل الهدية من الواجهة
            $table->integer('minutes');
            $table->decimal('price', 8, 2);
            $table->string('recipient_name');
            $table->string('occasion'); // رسمي، رمضاني، عيديات
            $table->text('message')->nullable();

            // الدفع والكوبون
            $table->string('coupon_code')->unique()->nullable(); // الكود الذي سيتم مشاركته (مثال: GIFT-A1B2C3)
            $table->string('transaction_id')->nullable(); // رقم عملية الدفع في PayTabs
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->enum('status', ['active', 'claimed'])->default('active');

            // بيانات المستلم
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
