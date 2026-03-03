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
        Schema::table('students', function (Blueprint $table) {
            // ----------------------------------------------------
            // 1️⃣ تفضيلاتي التعليمية (Educational Preferences)
            // ----------------------------------------------------
            $table->string('age_group')->nullable()->after('profile_photo_path')->comment('الفئة العمرية');
            $table->string('reading_level')->nullable()->comment('مستوى القراءة والتسميع');
            $table->string('preferred_teacher_language')->nullable()->comment('لغة المعلم المفضلة');
            $table->string('reading_track')->nullable()->comment('مسار القراءة');
            $table->string('memorized_amount')->nullable()->comment('مقدار المحفوظ');

            // ----------------------------------------------------
            // 2️⃣ تفضيلات الجلسة (Session Preferences)
            // ----------------------------------------------------
            $table->string('plan_name')->nullable()->comment('اسم الخطة');
            $table->string('reading_type')->nullable()->comment('نوع القراءة');
            $table->string('teacher_response_speed')->nullable()->comment('سرعة الرد من قبل المعلم');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                // تفضيلاتي التعليمية
                'age_group',
                'reading_level',
                'preferred_teacher_language',
                'reading_track',
                'memorized_amount',

                // تفضيلات الجلسة
                'plan_name',
                'reading_type',
                'teacher_response_speed'
            ]);
        });
    }
};
