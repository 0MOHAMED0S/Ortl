<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slot_bookings', function (Blueprint $table) {
            // فحص الأعمدة قبل إضافتها لمنع خطأ (Duplicate column name) في بيئة الإنتاج

            if (!Schema::hasColumn('slot_bookings', 'channel_name')) {
                $table->string('channel_name')->unique()->nullable()->after('status');
            }
            if (!Schema::hasColumn('slot_bookings', 'agora_resource_id')) {
                $table->string('agora_resource_id')->nullable()->after('channel_name');
            }
            if (!Schema::hasColumn('slot_bookings', 'agora_sid')) {
                $table->string('agora_sid')->nullable()->after('agora_resource_id');
            }
            if (!Schema::hasColumn('slot_bookings', 'recording_url')) {
                $table->text('recording_url')->nullable()->after('agora_sid');
            }
            if (!Schema::hasColumn('slot_bookings', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('recording_url');
            }
            if (!Schema::hasColumn('slot_bookings', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('started_at');
            }
            if (!Schema::hasColumn('slot_bookings', 'actual_duration')) {
                $table->integer('actual_duration')->default(0)->after('ended_at');
            }
            if (!Schema::hasColumn('slot_bookings', 'student_joined_at')) {
                $table->timestamp('student_joined_at')->nullable()->after('actual_duration');
            }

            // 🌟 العمود الجديد الخاص بالمعلم
            if (!Schema::hasColumn('slot_bookings', 'teacher_joined_at')) {
                $table->timestamp('teacher_joined_at')->nullable()->after('student_joined_at');
            }
        });

        // تحديث حالات الـ ENUM بطريقة آمنة
        DB::statement("ALTER TABLE slot_bookings MODIFY COLUMN status ENUM('scheduled', 'ongoing', 'completed', 'cancelled', 'missed') DEFAULT 'scheduled'");
    }

    public function down(): void
    {
        Schema::table('slot_bookings', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('slot_bookings', 'channel_name')) $columnsToDrop[] = 'channel_name';
            if (Schema::hasColumn('slot_bookings', 'agora_resource_id')) $columnsToDrop[] = 'agora_resource_id';
            if (Schema::hasColumn('slot_bookings', 'agora_sid')) $columnsToDrop[] = 'agora_sid';
            if (Schema::hasColumn('slot_bookings', 'recording_url')) $columnsToDrop[] = 'recording_url';
            if (Schema::hasColumn('slot_bookings', 'started_at')) $columnsToDrop[] = 'started_at';
            if (Schema::hasColumn('slot_bookings', 'ended_at')) $columnsToDrop[] = 'ended_at';
            if (Schema::hasColumn('slot_bookings', 'actual_duration')) $columnsToDrop[] = 'actual_duration';
            if (Schema::hasColumn('slot_bookings', 'student_joined_at')) $columnsToDrop[] = 'student_joined_at';
            if (Schema::hasColumn('slot_bookings', 'teacher_joined_at')) $columnsToDrop[] = 'teacher_joined_at';

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        DB::statement("ALTER TABLE slot_bookings MODIFY COLUMN status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled'");
    }
};
