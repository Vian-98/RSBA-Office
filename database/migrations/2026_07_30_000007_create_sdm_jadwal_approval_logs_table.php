<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sdm_jadwal_approval_log')) {
            return;
        }

        Schema::create('sdm_jadwal_approval_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jadwal_kerja_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->string('aksi', 60); // AJUKAN_KABID, AJUKAN_WADIR, DIKETAHUI_KABID, DISETUJUI_WADIR, REVISI_DRAFT, EDIT_PASCA_PUBLISH, DRAFT_CREATED
            $table->string('status_sebelumnya', 40)->nullable();
            $table->string('status_sesudah', 40)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            if (Schema::hasTable('sdm_jadwal_kerja')) {
                $table->foreign('jadwal_kerja_id')
                    ->references('id')->on('sdm_jadwal_kerja')
                    ->onDelete('cascade');
            }

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->foreign('karyawan_id')
                ->references('id')->on('sdm_karyawan')
                ->onDelete('set null');

            $table->index('jadwal_kerja_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sdm_jadwal_approval_log');
    }
};
