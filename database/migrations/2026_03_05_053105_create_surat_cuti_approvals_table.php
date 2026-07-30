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
        Schema::create('surat_cuti_approval', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_cuti_id');
            $table->unsignedBigInteger('disetujui_oleh');
            $table->enum('status', ['waiting', 'approved', 'rejected']);
            $table->string('keterangan')->nullable();
            $table->longText('signature_hash')->nullable();
            $table->string('approved_at', 25)->nullable();
            $table->timestamps();

            $table->foreign('surat_cuti_id')
                ->references('id')
                ->on('surat_cuti')
                ->onDelete('cascade');

            $table->foreign('disetujui_oleh')
                ->references('id')
                ->on('sdm_karyawan')
                ->onDelete('cascade');

            // Composite index untuk query filtering berdasarkan status
            // Index ini bisa cover query: WHERE surat_cuti_id = ? AND disetujui_oleh = ? AND status = ?
            $table->index(
                ['surat_cuti_id', 'disetujui_oleh', 'status'],
                'idx_approval_status'
            );
        });

        Schema::table('surat_cuti', function (Blueprint $table) {
            $table->dropColumn('acc');
            $table->dropColumn('status');
        });

        Schema::table('surat_cuti', function (Blueprint $table) {
            $table->enum('status', ['waiting', 'pending', 'approved', 'rejected', 'manual'])->default('waiting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_cuti_approval');

        Schema::table('surat_cuti', function (Blueprint $table) {
            $table->text('acc')->nullable();
            $table->enum('status', ['proses', 'disetujui', 'ditolak'])->nullable();
        });
    }
};
