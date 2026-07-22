<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `surat_sp3` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'manual') NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE `surat_sp3_approval` MODIFY COLUMN `status` ENUM('approved', 'rejected', 'manual') NOT NULL DEFAULT 'approved'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `surat_sp3` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE `surat_sp3_approval` MODIFY COLUMN `status` ENUM('approved', 'rejected') NOT NULL DEFAULT 'approved'");
        }
    }
};
