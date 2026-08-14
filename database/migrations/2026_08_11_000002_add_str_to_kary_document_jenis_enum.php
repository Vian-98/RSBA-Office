<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE sdm_kary_document MODIFY COLUMN jenis ENUM('ijazah', 'sertifikat', 'str', 'sip', 'pribadi', 'lain') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE sdm_kary_document MODIFY COLUMN jenis ENUM('ijazah', 'sertifikat', 'sip', 'pribadi', 'lain') NOT NULL");
        }
    }
};

