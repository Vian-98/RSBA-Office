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
        Schema::create('surat_tarif_penelitian', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_penelitian', 100);
            $table->double('jasa_sarana')->default(0);
            $table->double('jasa_pelayanan')->default(0);
            $table->string('nomor_sk', 100)->nullable();
            $table->date('tgl_berlaku');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        // Insert initial template data
        DB::table('surat_tarif_penelitian')->insert([
            [
                'jenis_penelitian' => 'Presurvey / Studi Pendahuluan',
                'jasa_sarana'      => 50000,
                'jasa_pelayanan'   => 50000,
                'nomor_sk'         => '024/Kpts-S4/PBA-A10/10.01.22',
                'tgl_berlaku'      => '2022-01-10',
                'keterangan'       => 'Tarif standar presurvey',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'jenis_penelitian' => 'Penelitian Skripsi / Tugas Akhir',
                'jasa_sarana'      => 100000,
                'jasa_pelayanan'   => 150000,
                'nomor_sk'         => '024/Kpts-S4/PBA-A10/10.01.22',
                'tgl_berlaku'      => '2022-01-10',
                'keterangan'       => 'Tarif standar penelitian skripsi',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_tarif_penelitian');
    }
};
