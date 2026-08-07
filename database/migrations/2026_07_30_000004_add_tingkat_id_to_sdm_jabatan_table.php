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
        Schema::table('sdm_jabatan', function (Blueprint $table) {
            $table->unsignedBigInteger('tingkat_id')->nullable()->default(5)->after('bagian_id');
            $table->foreign('tingkat_id')->references('id')->on('sdm_jabatan_tingkat')->onDelete('set null');
        });

        // Seed mapping tingkat_id berdasarkan pola nama jabatan eksisting
        $jabatans = DB::table('sdm_jabatan')->get();
        foreach ($jabatans as $j) {
            $namaLower = strtolower($j->nama);
            $tingkatId = 5; // Default Staf/Pelaksana

            if (str_contains($namaLower, 'direktur utama') || str_contains($namaLower, 'dirut')) {
                $tingkatId = 1;
            } elseif (str_contains($namaLower, 'wadir') || str_contains($namaLower, 'wakil direktur') || str_contains($namaLower, 'divisi')) {
                $tingkatId = 2;
            } elseif (str_contains($namaLower, 'kabid') || str_contains($namaLower, 'kepala bidang') || str_contains($namaLower, 'kabag') || str_contains($namaLower, 'kepala bagian') || str_contains($namaLower, 'kepala dept')) {
                $tingkatId = 3;
            } elseif (str_contains($namaLower, 'koordinator') || str_contains($namaLower, 'karu') || str_contains($namaLower, 'kepala ruangan') || str_contains($namaLower, 'koor')) {
                $tingkatId = 4;
            }

            DB::table('sdm_jabatan')->where('id', $j->id)->update(['tingkat_id' => $tingkatId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_jabatan', function (Blueprint $table) {
            $table->dropForeign(['tingkat_id']);
            $table->dropColumn('tingkat_id');
        });
    }
};
