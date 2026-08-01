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
        Schema::create('sdm_payroll_slips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->string('periode', 7); // YYYY-MM
            
            // Earnings
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('tunjangan_tetap', 15, 2)->default(0);
            $table->decimal('tunjangan_absensi', 15, 2)->default(0);
            $table->decimal('tunjangan_jabatan', 15, 2)->default(0);
            $table->decimal('tunjangan_shift', 15, 2)->default(0);
            $table->decimal('tunjangan_radiologi', 15, 2)->default(0);
            $table->decimal('tunjangan_lain', 15, 2)->default(0);
            $table->decimal('uang_lembur', 15, 2)->default(0);
            $table->decimal('tunjangan_hari_raya', 15, 2)->default(0);
            
            // Deductions
            $table->decimal('potongan_absensi', 15, 2)->default(0);
            $table->decimal('potongan_cash_bon', 15, 2)->default(0);
            $table->decimal('potongan_obat', 15, 2)->default(0);
            $table->decimal('potongan_bpjs_kes', 15, 2)->default(0);
            $table->decimal('potongan_bpjs_tk', 15, 2)->default(0);
            $table->decimal('potongan_lain', 15, 2)->default(0);
            
            // Taxes & Bank
            $table->decimal('potongan_pph21', 15, 2)->default(0);
            $table->decimal('potongan_bank', 15, 2)->default(0);
            
            // Metadata
            $table->integer('bpjs_keluarga_tambahan')->default(0);
            $table->decimal('total_gaji', 15, 2)->default(0);
            $table->decimal('total_potongan', 15, 2)->default(0);
            $table->decimal('gaji_bersih', 15, 2)->default(0);
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('karyawan_id')->references('id')->on('sdm_karyawan')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['karyawan_id', 'periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_payroll_slips');
    }
};
