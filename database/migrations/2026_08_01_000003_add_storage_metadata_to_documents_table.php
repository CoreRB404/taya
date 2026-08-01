<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Existing records point to the legacy private local disk. New
            // records explicitly retain the disk used for their object.
            $table->string('storage_disk', 32)->default('local')->after('file_path');
            $table->string('original_name')->nullable()->after('storage_disk');
            $table->string('mime_type', 100)->nullable()->after('original_name');
            $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['storage_disk', 'original_name', 'mime_type', 'file_size']);
        });
    }
};
