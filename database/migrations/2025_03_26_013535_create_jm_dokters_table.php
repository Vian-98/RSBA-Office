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
        Schema::create('jm_dokter', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jm_pasien_id');
            $table->string('dokter');
            $table->integer('jumlah');
            $table->enum('status', ['sp', 'um', 'an', 'dpjp', 'um_s', 'sppdkgh', 'dpjp_hd']);
            $table->timestamps();

            $table->foreign('jm_pasien_id')->references('id')->on('jm_pasien')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jm_dokter');
    }
};
