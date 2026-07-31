<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detainees', function (Blueprint $table) {
            $table->unsignedInteger('bail_amount')->nullable()->after('commitment_date');
            $table->enum('bail_status', ['not_posted', 'posted', 'unable_to_pay', 'pending_review'])->default('not_posted')->after('bail_amount');
            $table->timestamp('bail_posted_at')->nullable()->after('bail_status');
            $table->text('bail_notes')->nullable()->after('bail_posted_at');
        });
    }

    public function down(): void
    {
        Schema::table('detainees', function (Blueprint $table) {
            $table->dropColumn(['bail_amount', 'bail_status', 'bail_posted_at', 'bail_notes']);
        });
    }
};
