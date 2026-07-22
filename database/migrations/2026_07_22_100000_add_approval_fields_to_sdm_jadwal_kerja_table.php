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
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE sdm_jadwal_kerja MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'draft'");
        }

        Schema::table('sdm_jadwal_kerja', function (Blueprint $table) {
            $table->foreignId('diketahui_oleh')->nullable()->after('dibuat_oleh')->constrained('sdm_karyawan')->nullOnDelete();
            $table->timestamp('diketahui_at')->nullable()->after('diketahui_oleh');
            $table->foreignId('disetujui_oleh')->nullable()->after('diketahui_at')->constrained('sdm_karyawan')->nullOnDelete();
            $table->timestamp('disetujui_at')->nullable()->after('disetujui_oleh');
            $table->text('catatan_revisi')->nullable()->after('disetujui_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_jadwal_kerja', function (Blueprint $table) {
            $table->dropForeign(['diketahui_oleh']);
            $table->dropForeign(['disetujui_oleh']);
            $table->dropColumn([
                'diketahui_oleh',
                'diketahui_at',
                'disetujui_oleh',
                'disetujui_at',
                'catatan_revisi',
            ]);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE sdm_jadwal_kerja MODIFY COLUMN status ENUM('draft', 'published', 'locked') NOT NULL DEFAULT 'draft'");
        }
    }
};
