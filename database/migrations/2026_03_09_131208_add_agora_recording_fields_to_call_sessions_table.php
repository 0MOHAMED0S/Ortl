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
        Schema::table('slot_bookings', function (Blueprint $table) {
            $table->string('agora_resource_id')->nullable()->after('status');
            $table->string('agora_sid')->nullable()->after('agora_resource_id');
            $table->text('recording_url')->nullable()->after('agora_sid'); // يفضل استخدام text للروابط الطويلة
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_sessions', function (Blueprint $table) {
            $table->dropColumn(['agora_resource_id', 'agora_sid', 'recording_url']);
        });
    }
};
