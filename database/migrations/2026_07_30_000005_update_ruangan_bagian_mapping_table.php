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
        if (!Schema::hasColumn('ruangan', 'bagian_id')) {
            Schema::table('ruangan', function (Blueprint $table) {
                $table->unsignedBigInteger('bagian_id')->nullable()->after('nama');
                $table->foreign('bagian_id')->references('id')->on('bagian')->onDelete('set null');
            });
        }

        $existingBagianIds = DB::table('bagian')->pluck('id')->toArray();

        // 1. Keperawatan (bagian_id = 2)
        $keperawatanRuanganIds = [1, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38];
        if (in_array(2, $existingBagianIds)) {
            DB::table('ruangan')->whereIn('id', $keperawatanRuanganIds)->update(['bagian_id' => 2]);
        }

        // 2. Pelayanan Medis (bagian_id = 10)
        $medisRuanganIds = [13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 39, 40, 41, 42, 44, 45, 46, 47, 59];
        if (in_array(10, $existingBagianIds)) {
            DB::table('ruangan')->whereIn('id', $medisRuanganIds)->update(['bagian_id' => 10]);
        }

        // 3. SDM & Umum (bagian_id = 11)
        $sdmUmumRuanganIds = [43, 48, 50, 52, 53, 54, 55, 56, 57, 58];
        if (in_array(11, $existingBagianIds)) {
            DB::table('ruangan')->whereIn('id', $sdmUmumRuanganIds)->update(['bagian_id' => 11]);
        }

        // 4. Keuangan (bagian_id = 5)
        $keuanganRuanganIds = [49, 51];
        if (in_array(5, $existingBagianIds)) {
            DB::table('ruangan')->whereIn('id', $keuanganRuanganIds)->update(['bagian_id' => 5]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('ruangan', 'bagian_id')) {
            Schema::table('ruangan', function (Blueprint $table) {
                $table->dropForeign(['bagian_id']);
                $table->dropColumn('bagian_id');
            });
        }
    }
};
