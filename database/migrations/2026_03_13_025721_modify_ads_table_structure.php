<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            // 1. حذف الأعمدة القديمة التي لا تحتاجها
            $table->dropColumn(['title', 'subtitle', 'bg_color']);

            // إذا كان حقل الكوبون موجوداً كـ Foreign Key يجب حذفه أولاً
            $table->dropForeign(['coupon_id']);
            $table->dropColumn('coupon_id');

            // 2. تعديل حقل الصورة ليصبح مطلوباً (Required)
            // ملاحظة: إذا كان العمود موجوداً نستخدم change()، وإذا لم يكن موجوداً ننشئه.
            // هنا سنفترض أننا نريد التأكد من وجوده كـ string مطلوب:
            $table->string('image')->change();

            // 3. إضافة حقل الرابط (Link) ويكون Nullable
            $table->string('link')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            // عكس العمليات في حال أردت التراجع
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('bg_color')->default('linear-gradient(...)');
            $table->foreignId('coupon_id')->nullable()->constrained('coupons');
            $table->dropColumn('link');
        });
    }
};
