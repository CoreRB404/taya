<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computation_id')->constrained('overstay_computations')->onDelete('cascade');
            $table->foreignId('detainee_id')->constrained('detainees')->onDelete('cascade');
            $table->enum('alert_level', ['critical', 'at_risk', 'flagged', 'monitored', 'resolved']);
            $table->text('recommended_action');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('admin_override')->default(false);
            $table->text('override_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
