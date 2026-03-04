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
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->boolean('is_recorded')->default(true)->comment('هل الجلسة مسجلة؟');
            $table->string('agora_resource_id')->nullable()->comment('معرف طلب التسجيل من أجورا');
            $table->string('agora_sid')->nullable()->comment('معرف جلسة التسجيل الفعالة لإيقافها لاحقاً');
            $table->string('recording_url')->nullable()->comment('رابط الفيديو بعد انتهاء التسجيل');
        });
        Schema::table('call_sessions', function (Blueprint $table) {
            $table->boolean('is_recorded')->default(true);
            $table->string('agora_resource_id')->nullable();
            $table->string('agora_sid')->nullable();
            $table->string('recording_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'is_recorded',
                'agora_resource_id',
                'agora_sid',
                'recording_url'
            ]);
        });

        Schema::table('call_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'is_recorded',
                'agora_resource_id',
                'agora_sid',
                'recording_url'
            ]);
        });
    }
};
