<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained('alerts')->onDelete('cascade');
            $table->foreignId('detainee_id')->constrained('detainees')->onDelete('cascade');
            $table->enum('action_type', ['motion_for_release', 'habeas_corpus', 'pao_referral', 'ngo_referral', 'case_review', 'other']);
            $table->foreignId('filed_by')->constrained('users')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamp('filed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_actions');
    }
};
