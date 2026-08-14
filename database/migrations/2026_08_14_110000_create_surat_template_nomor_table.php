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
        Schema::create('surat_template_nomor', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_surat', 50)->unique();
            $table->string('format_nomor', 255);
            $table->string('keterangan', 255)->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        // Insert default templates
        DB::table('surat_template_nomor')->insert([
            [
                'jenis_surat'  => 'balasan_pkl',
                'format_nomor' => '{no}/S4/B-PKL/PBA-{kode_jabatan}/{tanggal}',
                'keterangan'   => 'Format Nomor Surat Balasan Praktik Kerja Lapangan (PKL)',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'jenis_surat'  => 'balasan_penelitian',
                'format_nomor' => '{no}/S4/B-PNL/PBA-{kode_jabatan}/{tanggal}',
                'keterangan'   => 'Format Nomor Surat Balasan Presurvey dan Penelitian',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'jenis_surat'  => 'perintah_tugas',
                'format_nomor' => '{no}/S4/SPT/PBA-{kode_jabatan}/{tanggal}',
                'keterangan'   => 'Format Nomor Surat Perintah Tugas (SPT)',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_template_nomor');
    }
};
