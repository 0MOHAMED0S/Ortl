<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn(['title', 'subtitle', 'bg_color']);
            $table->dropForeign(['coupon_id']);
            $table->dropColumn('coupon_id');
            $table->string('image')->change();
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
