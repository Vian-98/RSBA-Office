<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sdm_kary_ruangan')) {
            Schema::create('sdm_kary_ruangan', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('karyawan_id');
                $table->unsignedBigInteger('ruangan_id');
                $table->date('tgl_mulai');
                $table->date('tgl_berakhir')->nullable();
                $table->boolean('is_utama')->default(true);
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->foreign('karyawan_id')->references('id')->on('sdm_karyawan')->onDelete('cascade');
                $table->foreign('ruangan_id')->references('id')->on('ruangan')->onDelete('cascade');
            });
        }

        // Backfill data eksisting dari sdm_karyawan.ruangan_id
        if (Schema::hasColumn('sdm_karyawan', 'ruangan_id')) {
            $karyawans = DB::table('sdm_karyawan')
                ->whereNotNull('ruangan_id')
                ->select('id', 'ruangan_id', 'tgl_masuk', 'created_at')
                ->get();

        $now = now();
        $records = [];
        foreach ($karyawans as $k) {
            $tglMulai = $k->tgl_masuk ?? date('Y-m-d', strtotime($k->created_at ?? 'now'));
            $records[] = [
                'karyawan_id'  => $k->id,
                'ruangan_id'   => $k->ruangan_id,
                'tgl_mulai'    => $tglMulai,
                'tgl_berakhir' => null,
                'is_utama'     => true,
                'keterangan'   => 'Penugasan Ruangan Utama (Auto-migrasi)',
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        if (!empty($records)) {
            // Insert in chunks of 100
            foreach (array_chunk($records, 100) as $chunk) {
                DB::table('sdm_kary_ruangan')->insert($chunk);
            }
        }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_kary_ruangan');
    }
};
