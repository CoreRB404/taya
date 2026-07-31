<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign('facility_id')->references('id')->on('facilities')->restrictOnDelete();
            }
            $table->index(['is_active', 'role']);
        });
        Schema::table('detainees', function (Blueprint $table) {
            $table->index(['facility_id', 'status']);
            $table->index('commitment_date');
        });
        Schema::table('alerts', function (Blueprint $table) {
            $table->index(['alert_level', 'resolved_at']);
        });
        Schema::table('detainee_phases', function (Blueprint $table) {
            $table->index(['completed', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::table('detainee_phases', fn (Blueprint $table) => $table->dropIndex(['completed', 'due_date']));
        Schema::table('alerts', fn (Blueprint $table) => $table->dropIndex(['alert_level', 'resolved_at']));
        Schema::table('detainees', function (Blueprint $table) {
            $table->dropIndex(['facility_id', 'status']);
            $table->dropIndex(['commitment_date']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'role']);
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['facility_id']);
            }
        });
    }
};
