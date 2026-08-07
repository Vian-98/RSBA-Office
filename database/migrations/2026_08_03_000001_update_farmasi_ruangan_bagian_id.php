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
        // Cari bagian yang bernama Farmasi atau Penunjang Medis / Farmasi
        $farmasiBagian = DB::table('bagian')
            ->where('nama', 'LIKE', '%Farmasi%')
            ->first();

        if ($farmasiBagian) {
            DB::table('ruangan')
                ->where(function ($q) {
                    $q->where('nama', 'LIKE', '%Farmasi%')
                      ->orWhere('nama', 'LIKE', '%Apotek%');
                })
                ->whereNull('bagian_id')
                ->update(['bagian_id' => $farmasiBagian->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback update bila diperlukan
    }
};
