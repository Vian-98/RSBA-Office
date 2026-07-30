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
        Schema::create('jm_pasien', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pasien', 100);
            $table->string('no_rekmedis', 100);
            $table->date('tgl_checkin');
            $table->date('tgl_checkout');
            $table->string('dpjp', 50);
            $table->enum('layanan', ['rajal', 'ranap']);
            $table->enum('cabar', ['bpjs', 'jkmd', 'tunai']);
            $table->string('sep', 100)->nullable()->unique();
            $table->bigInteger('klaim')->default(0);
            $table->bigInteger('tarif_rs')->default(0);
            $table->integer('kelas_rawat')->nullable();
            $table->string('diaglist', 50)->nullable();
            $table->string('proclist', 50)->nullable();
            $table->string('deskripsi_inacbg', 255)->nullable();
            $table->bigInteger('disetujui')->default(0);
            $table->integer('batch')->default(0);
            $table->enum('kelompok', ['ri_no', 'ri_op', 'ri_partus', 'ri_sc', 'ri_curet', 'ri_hd', 'ri_mata', 'rj_sp', 'rj_um', 'rj_mata', 'rj_hd'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jm_pasien');
    }
};
