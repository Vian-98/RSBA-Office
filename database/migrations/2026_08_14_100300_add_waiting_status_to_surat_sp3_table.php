<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `surat_sp3` MODIFY COLUMN `status` ENUM('pending', 'waiting', 'approved', 'rejected', 'manual') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `surat_sp3` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'manual') NOT NULL DEFAULT 'pending'");
        }
    }
};
