<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalty_references', function (Blueprint $table) {
            $table->id();
            $table->string('rpc_code');
            $table->string('charge_name');
            $table->decimal('max_penalty_years', 8, 2);
            $table->integer('max_penalty_months')->nullable();
            $table->enum('law_source', ['RPC', 'RA', 'PD', 'EO', 'OTHER']);
            $table->date('last_validated')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalty_references');
    }
};
