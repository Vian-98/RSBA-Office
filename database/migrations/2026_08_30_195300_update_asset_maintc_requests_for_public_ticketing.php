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
        Schema::table('asset_maintc_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_id')->nullable()->change();
            $table->unsignedBigInteger('user_req_id')->nullable()->change();

            if (!Schema::hasColumn('asset_maintc_requests', 'ruangan_id')) {
                $table->unsignedBigInteger('ruangan_id')->nullable()->after('nomor_tiket');
                $table->foreign('ruangan_id')->references('id')->on('ruangan')->onDelete('set null');
            }

            if (!Schema::hasColumn('asset_maintc_requests', 'jenis')) {
                $table->enum('jenis', ['umum', 'it'])->default('umum')->after('ruangan_id');
            }

            if (!Schema::hasColumn('asset_maintc_requests', 'pelapor_nama')) {
                $table->string('pelapor_nama', 100)->nullable()->after('jenis');
            }

            if (!Schema::hasColumn('asset_maintc_requests', 'pelapor_kontak')) {
                $table->string('pelapor_kontak', 100)->nullable()->after('pelapor_nama');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_maintc_requests', function (Blueprint $table) {
            if (Schema::hasColumn('asset_maintc_requests', 'ruangan_id')) {
                $table->dropForeign(['ruangan_id']);
                $table->dropColumn('ruangan_id');
            }
            if (Schema::hasColumn('asset_maintc_requests', 'jenis')) {
                $table->dropColumn('jenis');
            }
            if (Schema::hasColumn('asset_maintc_requests', 'pelapor_nama')) {
                $table->dropColumn('pelapor_nama');
            }
            if (Schema::hasColumn('asset_maintc_requests', 'pelapor_kontak')) {
                $table->dropColumn('pelapor_kontak');
            }
        });
    }
};
