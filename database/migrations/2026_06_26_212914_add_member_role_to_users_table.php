<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        $usersTable = DB::connection()->getQueryGrammar()->wrapTable('users');

        DB::statement("ALTER TABLE {$usersTable} MODIFY role ENUM('admin', 'treasurer', 'staff', 'member') NOT NULL DEFAULT 'staff'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        $usersTable = DB::connection()->getQueryGrammar()->wrapTable('users');

        DB::statement("ALTER TABLE {$usersTable} MODIFY role ENUM('admin', 'treasurer', 'staff') NOT NULL DEFAULT 'staff'");
    }
};
