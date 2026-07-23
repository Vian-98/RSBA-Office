<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom docstore_key ke tabel surat_cuti dan surat_sp3.
     *
     * docstore_key adalah UUID yang diberikan oleh docstore (bank surat) setelah
     * sync berhasil. Key ini digunakan oleh office untuk:
     * 1. Menarik data print dari docstore (bukan dari DB office)
     * 2. Embed ke QR code surat tercetak (agar scan QR → verify langsung ke docstore)
     *
     * Jika docstore_key NULL, berarti surat belum pernah di-sync ke docstore.
     */
    public function up(): void
    {
        if (Schema::hasTable('surat_cuti')) {
            Schema::table('surat_cuti', function (Blueprint $table) {
                if (!Schema::hasColumn('surat_cuti', 'docstore_key')) {
                    $table->string('docstore_key', 36)
                        ->nullable()
                        ->after('qr_hash')
                        ->comment('UUID dari docstore — referensi bank surat (source of truth)');
                }
                if (!Schema::hasColumn('surat_cuti', 'docstore_synced_at')) {
                    $table->timestamp('docstore_synced_at')
                        ->nullable()
                        ->after('docstore_key')
                        ->comment('Waktu terakhir berhasil sync ke docstore');
                }
            });
        }

        if (Schema::hasTable('surat_sp3')) {
            Schema::table('surat_sp3', function (Blueprint $table) {
                if (!Schema::hasColumn('surat_sp3', 'docstore_key')) {
                    $table->string('docstore_key', 36)
                        ->nullable()
                        ->after('qr_hash')
                        ->comment('UUID dari docstore — referensi bank surat (source of truth)');
                }
                if (!Schema::hasColumn('surat_sp3', 'docstore_synced_at')) {
                    $table->timestamp('docstore_synced_at')
                        ->nullable()
                        ->after('docstore_key')
                        ->comment('Waktu terakhir berhasil sync ke docstore');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('surat_cuti')) {
            Schema::table('surat_cuti', function (Blueprint $table) {
                $columns = array_filter(
                    ['docstore_key', 'docstore_synced_at'],
                    fn($col) => Schema::hasColumn('surat_cuti', $col)
                );
                if (!empty($columns)) {
                    $table->dropColumn(array_values($columns));
                }
            });
        }

        if (Schema::hasTable('surat_sp3')) {
            Schema::table('surat_sp3', function (Blueprint $table) {
                $columns = array_filter(
                    ['docstore_key', 'docstore_synced_at'],
                    fn($col) => Schema::hasColumn('surat_sp3', $col)
                );
                if (!empty($columns)) {
                    $table->dropColumn(array_values($columns));
                }
            });
        }
    }
};
