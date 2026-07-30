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
        Schema::create('sdm_jadwal_kerja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ruangan_id')->nullable();
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->enum('status', ['draft', 'published', 'locked'])->default('draft');
            $table->unsignedBigInteger('dibuat_oleh')->nullable(); // karyawan_id koordinator yang generate
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('ruangan_id')->references('id')->on('ruangan')->nullOnDelete();
            $table->foreign('dibuat_oleh')->references('id')->on('sdm_karyawan')->nullOnDelete();
            $table->unique(['ruangan_id', 'bulan', 'tahun'], 'uniq_jadwalkerja_ruangan_periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_jadwal_kerja');
    }
};
