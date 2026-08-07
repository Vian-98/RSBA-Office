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
        Schema::create('sdm_workflow_approval', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ruangan_id')->nullable(); // null = berlaku global
            $table->string('tipe_jadwal', 20)->nullable(); // null = semua, 'karyawan', 'dokter'
            $table->integer('step_number'); // 1 = Diketahui (Kabid/Kepala Dept), 2 = Disetujui (Wadir)
            $table->unsignedBigInteger('tingkat_jabatan_id');
            $table->timestamps();

            $table->foreign('ruangan_id')->references('id')->on('ruangan')->onDelete('cascade');
            $table->foreign('tingkat_jabatan_id')->references('id')->on('sdm_jabatan_tingkat')->onDelete('cascade');
        });

        // Seed default global workflow rules
        $now = now();
        DB::table('sdm_workflow_approval')->insert([
            // Rule default untuk Karyawan: Step 1 = Kepala Dept (Level 3), Step 2 = Wadir (Level 2)
            ['ruangan_id' => null, 'tipe_jadwal' => 'karyawan', 'step_number' => 1, 'tingkat_jabatan_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['ruangan_id' => null, 'tipe_jadwal' => 'karyawan', 'step_number' => 2, 'tingkat_jabatan_id' => 2, 'created_at' => $now, 'updated_at' => $now],

            // Rule default untuk Dokter (Bypass Step 1): Step 2 = Wadir (Level 2)
            ['ruangan_id' => null, 'tipe_jadwal' => 'dokter', 'step_number' => 2, 'tingkat_jabatan_id' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_workflow_approval');
    }
};
