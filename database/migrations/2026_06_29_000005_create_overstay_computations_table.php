<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overstay_computations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detainee_id')->constrained('detainees')->onDelete('cascade');
            $table->integer('days_detained');
            $table->integer('max_penalty_days');
            $table->integer('overstay_days');
            $table->enum('alert_level', ['critical', 'at_risk', 'flagged', 'monitored', 'resolved']);
            $table->timestamp('computed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overstay_computations');
    }
};
