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
        Schema::create('sdm_jabatan_tingkat', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->integer('urutan')->default(5);
            $table->boolean('is_penyusun_jadwal')->default(false);
            $table->timestamps();
        });

        // Seed default 5 Levels
        $now = now();
        DB::table('sdm_jabatan_tingkat')->insert([
            ['id' => 1, 'nama' => 'Direksi / Direktur Utama', 'urutan' => 1, 'is_penyusun_jadwal' => false, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'nama' => 'Wadir / Kepala Divisi', 'urutan' => 2, 'is_penyusun_jadwal' => false, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'nama' => 'Kepala Dept / Bidang / Bagian', 'urutan' => 3, 'is_penyusun_jadwal' => false, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'nama' => 'Koordinator / Kepala Ruangan', 'urutan' => 4, 'is_penyusun_jadwal' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'nama' => 'Pelaksana / Staf Operasional', 'urutan' => 5, 'is_penyusun_jadwal' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_jabatan_tingkat');
    }
};
