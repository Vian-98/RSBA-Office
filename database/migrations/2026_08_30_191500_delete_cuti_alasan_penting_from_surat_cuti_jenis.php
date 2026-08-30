<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('surat_cuti_jenis')) {
            DB::table('surat_cuti_jenis')
                ->where('id', 4)
                ->orWhere('nama', 'like', '%alasan penting%')
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('surat_cuti_jenis')) {
            $exists = DB::table('surat_cuti_jenis')->where('id', 4)->exists();
            if (!$exists) {
                DB::table('surat_cuti_jenis')->insert([
                    'id' => 4,
                    'nama' => 'Cuti Alasan Penting',
                    'lama' => 30,
                    'periode' => 'Y',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
