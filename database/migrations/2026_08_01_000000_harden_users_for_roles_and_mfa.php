<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $definition = DB::selectOne("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'users'")?->sql ?? '';

            if (str_contains($definition, 'CHECK')) {
                DB::statement('PRAGMA foreign_keys=OFF');
                DB::statement('CREATE TABLE users_role_upgrade (
                    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    name VARCHAR NOT NULL,
                    email VARCHAR NOT NULL UNIQUE,
                    password VARCHAR NOT NULL,
                    role VARCHAR(30) NOT NULL DEFAULT \'staff\',
                    facility_id INTEGER,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    mfa_enabled TINYINT(1) NOT NULL DEFAULT 0,
                    mfa_code_hash VARCHAR,
                    mfa_expires_at DATETIME,
                    mfa_last_sent_at DATETIME,
                    email_verified_at DATETIME,
                    remember_token VARCHAR,
                    created_at DATETIME,
                    updated_at DATETIME
                )');
                DB::statement('INSERT INTO users_role_upgrade
                    (id, name, email, password, role, facility_id, email_verified_at, remember_token, created_at, updated_at)
                    SELECT id, name, email, password, role, facility_id, email_verified_at, remember_token, created_at, updated_at FROM users');
                DB::statement('DROP TABLE users');
                DB::statement('ALTER TABLE users_role_upgrade RENAME TO users');
                DB::statement('CREATE INDEX users_role_index ON users (role)');
                DB::statement('PRAGMA foreign_keys=ON');

                return;
            }
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(30) USING role::text");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'staff'");
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(30) NOT NULL DEFAULT 'staff'");
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('facility_id');
            }
            if (! Schema::hasColumn('users', 'mfa_enabled')) {
                $table->boolean('mfa_enabled')->default(false)->after('is_active');
                $table->string('mfa_code_hash')->nullable()->after('mfa_enabled');
                $table->timestamp('mfa_expires_at')->nullable()->after('mfa_code_hash');
                $table->timestamp('mfa_last_sent_at')->nullable()->after('mfa_expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'mfa_enabled', 'mfa_code_hash', 'mfa_expires_at', 'mfa_last_sent_at']);
        });
    }
};
