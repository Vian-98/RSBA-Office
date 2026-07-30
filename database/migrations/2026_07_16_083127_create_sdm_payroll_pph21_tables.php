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
        // 1. Alter sdm_karyawan to add ptkp_status
        Schema::table('sdm_karyawan', function (Blueprint $table) {
            $table->enum('ptkp_status', ['TK0', 'TK1', 'TK2', 'TK3', 'K0', 'K1', 'K2', 'K3'])
                ->nullable()
                ->after('status_pernikahan')
                ->comment('Status PTKP Karyawan per 1 Januari');
        });

        // 2. Alter sdm_payroll_slips to add audit & calculation columns for PPh 21
        Schema::table('sdm_payroll_slips', function (Blueprint $table) {
            $table->decimal('pph21_bruto_bulan', 15, 2)->default(0)->after('potongan_pph21');
            $table->decimal('pph21_calculated', 15, 2)->default(0)->after('pph21_bruto_bulan');
            $table->boolean('pph21_is_overridden')->default(false)->after('pph21_calculated');
            $table->text('pph21_override_reason')->nullable()->after('pph21_is_overridden');
            $table->unsignedBigInteger('pph21_override_by')->nullable()->after('pph21_override_reason');
            $table->timestamp('pph21_override_at')->nullable()->after('pph21_override_by');

            $table->foreign('pph21_override_by')->references('id')->on('users')->onDelete('set null');
        });

        // 3. Create sdm_payroll_ptkp reference table
        Schema::create('sdm_payroll_ptkp', function (Blueprint $table) {
            $table->id();
            $table->string('status', 10); // TK0, TK1, etc.
            $table->decimal('nominal_setahun', 15, 2);
            $table->integer('berlaku_mulai_tahun')->default(2024);
            $table->timestamps();

            $table->unique(['status', 'berlaku_mulai_tahun']);
        });

        // 4. Create sdm_payroll_ter reference table
        Schema::create('sdm_payroll_ter', function (Blueprint $table) {
            $table->id();
            $table->char('kategori', 1); // A, B, C
            $table->decimal('bruto_bawah', 15, 2);
            $table->decimal('bruto_atas', 15, 2);
            $table->decimal('tarif_persen', 5, 2); // e.g. 5.50 %
            $table->integer('berlaku_mulai_tahun')->default(2024);
            $table->timestamps();
        });

        // 5. Create sdm_payroll_pasal17 reference table
        Schema::create('sdm_payroll_pasal17', function (Blueprint $table) {
            $table->id();
            $table->decimal('pkp_bawah', 15, 2);
            $table->decimal('pkp_atas', 15, 2)->nullable(); // null means unlimited
            $table->decimal('tarif_persen', 5, 2);
            $table->integer('berlaku_mulai_tahun')->default(2024);
            $table->timestamps();
        });

        // 6. Create sdm_payroll_pph21_override_logs audit table
        Schema::create('sdm_payroll_pph21_override_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_slip_id');
            $table->decimal('nilai_lama', 15, 2);
            $table->decimal('nilai_baru', 15, 2);
            $table->text('alasan');
            $table->unsignedBigInteger('diubah_oleh');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('payroll_slip_id', 'fk_override_logs_slip_id')
                ->references('id')->on('sdm_payroll_slips')->onDelete('cascade');
            $table->foreign('diubah_oleh', 'fk_override_logs_user_id')
                ->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_payroll_pph21_override_logs');
        Schema::dropIfExists('sdm_payroll_pasal17');
        Schema::dropIfExists('sdm_payroll_ter');
        Schema::dropIfExists('sdm_payroll_ptkp');

        Schema::table('sdm_payroll_slips', function (Blueprint $table) {
            $table->dropForeign(['pph21_override_by']);
            $table->dropColumn([
                'pph21_bruto_bulan',
                'pph21_calculated',
                'pph21_is_overridden',
                'pph21_override_reason',
                'pph21_override_by',
                'pph21_override_at'
            ]);
        });

        Schema::table('sdm_karyawan', function (Blueprint $table) {
            $table->dropColumn('ptkp_status');
        });
    }
};
