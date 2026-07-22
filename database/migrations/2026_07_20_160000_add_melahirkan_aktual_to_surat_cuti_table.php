<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat_cuti', function (Blueprint $table) {
            $table->date('tgl_melahirkan_aktual')->nullable()->after('tgl_akhir');
            $table->boolean('is_penyesuaian_melahirkan')->default(false)->after('tgl_melahirkan_aktual');
            $table->text('catatan_penyesuaian')->nullable()->after('is_penyesuaian_melahirkan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_cuti', function (Blueprint $table) {
            $table->dropColumn(['tgl_melahirkan_aktual', 'is_penyesuaian_melahirkan', 'catatan_penyesuaian']);
        });
    }
};
