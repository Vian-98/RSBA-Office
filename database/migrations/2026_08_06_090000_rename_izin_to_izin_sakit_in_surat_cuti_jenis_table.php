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
        DB::table('surat_cuti_jenis')
            ->where('id', 2)
            ->orWhere('nama', 'Izin')
            ->update(['nama' => 'Izin Sakit']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('surat_cuti_jenis')
            ->where('id', 2)
            ->orWhere('nama', 'Izin Sakit')
            ->update(['nama' => 'Izin']);
    }
};
