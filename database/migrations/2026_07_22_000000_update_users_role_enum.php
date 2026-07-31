<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotIn('role', ['admin', 'staff', 'lawyer', 'auditor', 'authorized_user'])
            ->update(['role' => 'authorized_user']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rolling back this migration would require reconstructing previous role values,
        // which is not safe when roles were normalized to a generic authorized_user.
    }
};
