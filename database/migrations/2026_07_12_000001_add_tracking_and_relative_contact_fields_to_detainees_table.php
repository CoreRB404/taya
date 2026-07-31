<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detainees', function (Blueprint $table) {
            $table->string('tracking_code')->nullable()->unique()->after('status');
            $table->string('relative_name')->nullable()->after('tracking_code');
            $table->string('relative_phone')->nullable()->after('relative_name');
            $table->string('relative_email')->nullable()->after('relative_phone');
            $table->boolean('tracking_enabled')->default(true)->after('relative_email');
        });
    }

    public function down(): void
    {
        Schema::table('detainees', function (Blueprint $table) {
            $table->dropColumn(['tracking_code', 'relative_name', 'relative_phone', 'relative_email', 'tracking_enabled']);
        });
    }
};
