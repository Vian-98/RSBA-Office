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
        if (Schema::hasTable('surat_cuti')) {
            Schema::table('surat_cuti', function (Blueprint $table) {
                if (!Schema::hasColumn('surat_cuti', 'qr_hash')) {
                    $table->string('qr_hash', 64)->nullable()->unique()->after('status')->comment('Hash verifikasi Sistem PKCS12 p12');
                }
                if (!Schema::hasColumn('surat_cuti', 'signed_at')) {
                    $table->timestamp('signed_at')->nullable()->after('qr_hash')->comment('Waktu dokumen disahkan secara final');
                }
                if (!Schema::hasColumn('surat_cuti', 'is_valid')) {
                    $table->boolean('is_valid')->default(true)->after('signed_at')->comment('Status legalitas dokumen');
                }
            });
        }

        if (Schema::hasTable('surat_sp3')) {
            Schema::table('surat_sp3', function (Blueprint $table) {
                if (!Schema::hasColumn('surat_sp3', 'qr_hash')) {
                    $table->string('qr_hash', 64)->nullable()->unique()->after('created_by')->comment('Hash verifikasi Sistem PKCS12 p12');
                }
                if (!Schema::hasColumn('surat_sp3', 'signed_at')) {
                    $table->timestamp('signed_at')->nullable()->after('qr_hash')->comment('Waktu dokumen disahkan secara final');
                }
                if (!Schema::hasColumn('surat_sp3', 'is_valid')) {
                    $table->boolean('is_valid')->default(true)->after('signed_at')->comment('Status legalitas dokumen');
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
                $columns = array_filter(['qr_hash', 'signed_at', 'is_valid'], fn($col) => Schema::hasColumn('surat_cuti', $col));
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('surat_sp3')) {
            Schema::table('surat_sp3', function (Blueprint $table) {
                $columns = array_filter(['qr_hash', 'signed_at', 'is_valid'], fn($col) => Schema::hasColumn('surat_sp3', $col));
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
