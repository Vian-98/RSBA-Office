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
        Schema::create('sdm_absensi_staging', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('import_batch_id');
            $table->string('employee_id_mentah', 30);
            $table->string('nama_mentah', 100)->nullable();
            $table->date('tanggal');
            $table->string('check_in_jadwal', 50)->nullable();
            $table->string('check_out_jadwal', 50)->nullable();
            $table->string('clock_in_aktual', 50)->nullable();
            $table->string('clock_out_aktual', 50)->nullable();
            $table->text('catatan_mesin')->nullable();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->enum('status_matching', ['matched', 'unmatched', 'ambiguous', 'diabaikan'])->default('unmatched');
            $table->unsignedBigInteger('detail_terkirim_id')->nullable();
            $table->timestamps();

            $table->foreign('karyawan_id')->references('id')->on('sdm_karyawan')->nullOnDelete();
            $table->index(['employee_id_mentah', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_absensi_staging');
    }
};
