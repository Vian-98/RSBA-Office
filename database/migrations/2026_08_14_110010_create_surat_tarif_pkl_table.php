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
        Schema::create('surat_tarif_pkl', function (Blueprint $table) {
            $table->id();
            $table->double('biaya_praktik_per_bulan')->default(0);
            $table->double('biaya_orientasi_per_orang')->default(0);
            $table->string('nomor_sk', 100);
            $table->date('tgl_berlaku');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        // Insert initial standard tariff from SK Direktur
        DB::table('surat_tarif_pkl')->insert([
            'biaya_praktik_per_bulan'   => 150000,
            'biaya_orientasi_per_orang' => 50000,
            'nomor_sk'                  => '023/Kpts-S4/PBA-A10/10.01.22',
            'tgl_berlaku'               => '2022-01-10',
            'keterangan'                => 'Tarif standar berdasarkan SK Direktur 023/Kpts-S4/PBA-A10/10.01.22',
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_tarif_pkl');
    }
};
