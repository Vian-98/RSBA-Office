<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan nilai 'manual' ke kolom ENUM status pada surat_cuti_approval.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `surat_cuti_approval` MODIFY `status` ENUM('waiting', 'approved', 'rejected', 'manual') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ubah dulu baris yang ber-status 'manual' ke 'approved' sebelum hapus enum value
        DB::statement("UPDATE `surat_cuti_approval` SET `status` = 'approved' WHERE `status` = 'manual'");
        DB::statement("ALTER TABLE `surat_cuti_approval` MODIFY `status` ENUM('waiting', 'approved', 'rejected') NOT NULL");
    }
};
