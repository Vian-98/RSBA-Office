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
        Schema::create('jm_prosentase', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jm_pasien_id')->unique();
            $table->double('total_billing', 15, 2)->default(0);
            $table->double('chosaring', 15, 2)->default(0);
            $table->double('jasa_p', 15, 2)->default(0);
            $table->double('klaim_min_rincian', 15, 2)->default(0);
            $table->double('jasa_pelayanan', 15, 2)->default(0);
            $table->double('jasa_rs', 15, 2)->default(0);
            $table->double('jasa_medis', 15, 2)->default(0);
            $table->double('jasa_operator', 15, 2)->default(0);
            $table->double('jasa_anastesi', 15, 2)->default(0);
            $table->double('jasa_penata', 15, 2)->default(0);
            $table->double('jasa_resus', 15, 2)->default(0);
            $table->double('jasa_pekerja', 15, 2)->default(0);
            $table->double('jasa_sppdkgh', 15, 2)->default(0);
            $table->double('jasa_um_sertifikat', 15, 2)->default(0);
            $table->double('jasa_dpjp_hd', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('jm_pasien_id')->references('id')->on('jm_pasien')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jm_prosentase');
    }
};
