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
        Schema::create('sdm_cuti_bersama', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('jenis_cuti_id')->nullable();
            $table->boolean('potong_cuti_tahunan')->default(true);
            $table->string('status')->default('draft'); // draft, disimulasikan, diterapkan, dibatalkan
            $table->unsignedBigInteger('diproses_oleh')->nullable();
            $table->timestamp('diproses_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('jenis_cuti_id')->references('id')->on('surat_cuti_jenis')->onDelete('set null');
            $table->foreign('diproses_oleh')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('sdm_cuti_bersama_tanggal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cuti_bersama_id');
            $table->date('tanggal');
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->foreign('cuti_bersama_id')->references('id')->on('sdm_cuti_bersama')->onDelete('cascade');
        });

        Schema::table('surat_cuti', function (Blueprint $table) {
            $table->string('sumber')->default('manual')->after('status');
            $table->unsignedBigInteger('cuti_bersama_id')->nullable()->after('sumber');

            $table->foreign('cuti_bersama_id')->references('id')->on('sdm_cuti_bersama')->onDelete('set null');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE sdm_jadwal_kerja_detail MODIFY COLUMN status_kehadiran ENUM('belum_dicek', 'hadir', 'terlambat', 'pulang_cepat', 'tidak_hadir', 'cuti', 'izin', 'perlu_verifikasi', 'cuti_bersama') DEFAULT 'belum_dicek'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_cuti', function (Blueprint $table) {
            $table->dropForeign(['cuti_bersama_id']);
            $table->dropColumn(['sumber', 'cuti_bersama_id']);
        });

        Schema::dropIfExists('sdm_cuti_bersama_tanggal');
        Schema::dropIfExists('sdm_cuti_bersama');
    }
};
