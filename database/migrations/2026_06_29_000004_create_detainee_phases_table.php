<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detainee_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detainee_id')->constrained('detainees')->onDelete('cascade');
            $table->tinyInteger('phase_number');
            $table->string('phase_name');
            $table->date('due_date');
            $table->integer('day_count');
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('flagged')->default(false);
            $table->text('flag_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detainee_phases');
    }
};
