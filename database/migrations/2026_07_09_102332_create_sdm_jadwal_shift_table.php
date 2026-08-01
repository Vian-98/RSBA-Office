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
        Schema::create('sdm_jadwal_shift', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();        // 'PAGI', 'SIANG', 'MALAM', 'REGULER'
            $table->string('nama', 30);
            $table->time('jam_masuk');
            $table->time('jam_keluar');
            $table->unsignedSmallInteger('toleransi_telat_menit')->default(15); // dipakai fase absensi nanti
            $table->string('warna', 10)->nullable();      // hex warna tampilan kalender
            $table->boolean('lintas_hari')->default(false); // true kalau jam_keluar < jam_masuk (shift malam)
            $table->boolean('aktif')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_jadwal_shift');
    }
};
