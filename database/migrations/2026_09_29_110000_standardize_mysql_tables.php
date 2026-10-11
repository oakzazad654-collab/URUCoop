<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class StandardizeMysqlTables extends Migration
{
    private $tables = [
        'users',
        'password_resets',
        'failed_jobs',
        'internship_applications',
        'companies',
        'cwie_documents',
        'approval_histories',
        'faculties',
        'departments',
        'notifications',
        'jobs',
    ];

    public function up()
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE `{$table}` ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    }

    public function down()
    {
        // Table engine and collation standardization is intentionally not reversed.
    }
}
