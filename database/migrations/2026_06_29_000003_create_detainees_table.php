<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detainees', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->text('charge_description');
            $table->foreignId('charge_rpc_code')->constrained('penalty_references')->onDelete('restrict');
            $table->date('commitment_date');
            $table->foreignId('facility_id')->constrained('facilities')->onDelete('restrict');
            $table->enum('status', ['active', 'released', 'resolved', 'archived'])->default('active');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detainees');
    }
};
