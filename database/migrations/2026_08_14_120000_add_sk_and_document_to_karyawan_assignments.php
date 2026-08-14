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
        // 1. Tambah no_sk dan document_id pada sdm_kary_jabatan
        if (Schema::hasTable('sdm_kary_jabatan')) {
            Schema::table('sdm_kary_jabatan', function (Blueprint $table) {
                if (!Schema::hasColumn('sdm_kary_jabatan', 'no_sk')) {
                    $table->string('no_sk', 100)->nullable()->after('bagian_id');
                }
                if (!Schema::hasColumn('sdm_kary_jabatan', 'document_id')) {
                    $table->unsignedBigInteger('document_id')->nullable()->after('no_sk');
                    $table->foreign('document_id')
                        ->references('id')
                        ->on('sdm_kary_document')
                        ->nullOnDelete();
                }
            });
        }

        // 2. Tambah no_sk dan document_id pada sdm_kary_ruangan
        if (Schema::hasTable('sdm_kary_ruangan')) {
            Schema::table('sdm_kary_ruangan', function (Blueprint $table) {
                if (!Schema::hasColumn('sdm_kary_ruangan', 'no_sk')) {
                    $table->string('no_sk', 100)->nullable()->after('ruangan_id');
                }
                if (!Schema::hasColumn('sdm_kary_ruangan', 'document_id')) {
                    $table->unsignedBigInteger('document_id')->nullable()->after('no_sk');
                    $table->foreign('document_id')
                        ->references('id')
                        ->on('sdm_kary_document')
                        ->nullOnDelete();
                }
            });
        }

        // 3. Tambah opsi 'sk' pada ENUM jenis di sdm_kary_document
        if (Schema::hasTable('sdm_kary_document')) {
            DB::statement("ALTER TABLE sdm_kary_document MODIFY COLUMN jenis ENUM('ijazah', 'sertifikat', 'str', 'sip', 'sk', 'pribadi', 'lain') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sdm_kary_ruangan')) {
            Schema::table('sdm_kary_ruangan', function (Blueprint $table) {
                if (Schema::hasColumn('sdm_kary_ruangan', 'document_id')) {
                    $table->dropForeign(['document_id']);
                    $table->dropColumn('document_id');
                }
                if (Schema::hasColumn('sdm_kary_ruangan', 'no_sk')) {
                    $table->dropColumn('no_sk');
                }
            });
        }

        if (Schema::hasTable('sdm_kary_jabatan')) {
            Schema::table('sdm_kary_jabatan', function (Blueprint $table) {
                if (Schema::hasColumn('sdm_kary_jabatan', 'document_id')) {
                    $table->dropForeign(['document_id']);
                    $table->dropColumn('document_id');
                }
                if (Schema::hasColumn('sdm_kary_jabatan', 'no_sk')) {
                    $table->dropColumn('no_sk');
                }
            });
        }

        if (Schema::hasTable('sdm_kary_document')) {
            DB::statement("ALTER TABLE sdm_kary_document MODIFY COLUMN jenis ENUM('ijazah', 'sertifikat', 'str', 'sip', 'pribadi', 'lain') NOT NULL");
        }
    }
};
