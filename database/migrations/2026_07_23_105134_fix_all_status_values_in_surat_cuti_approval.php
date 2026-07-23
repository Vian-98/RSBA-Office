<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sesuaikan ENUM status di surat_cuti_approval agar mencakup semua nilai
     * yang ada di StatusApproval enum: pending, waiting, approved, rejected, manual.
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE `surat_cuti_approval`
             MODIFY `status` ENUM('pending','waiting','approved','rejected','manual') NOT NULL"
        );
    }

    public function down(): void
    {
        // Kembalikan nilai non-standard ke 'waiting' sebelum mempersempit enum
        DB::statement("UPDATE `surat_cuti_approval` SET `status` = 'waiting' WHERE `status` NOT IN ('waiting','approved','rejected')");
        DB::statement(
            "ALTER TABLE `surat_cuti_approval`
             MODIFY `status` ENUM('waiting','approved','rejected') NOT NULL"
        );
    }
};
