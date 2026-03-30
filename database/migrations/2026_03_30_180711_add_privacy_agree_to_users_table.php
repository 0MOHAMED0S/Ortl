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
        Schema::table('users', function (Blueprint $table) {
            // إضافة العمود كـ boolean، وقيمته الافتراضية true حتى لا تتعطل حسابات المستخدمين القدامى
            if (!Schema::hasColumn('users', 'privacy_agree')) {
                $table->boolean('privacy_agree')->default(true)->after('password');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'privacy_agree')) {
                $table->dropColumn('privacy_agree');
            }
        });
    }
};
