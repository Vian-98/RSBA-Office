<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sdm\Karyawan;
use App\Services\PayrollCalculator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Support\Facades\Schema;

class DummyPayrollSlipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $karyawans = Karyawan::with(['jabatan.bagian'])->take(15)->get();

        if ($karyawans->isEmpty()) {
            return;
        }

        // Hapus data lama agar tidak duplikat saat di-seed ulang
        Schema::disableForeignKeyConstraints();
        DB::table('sdm_payroll_slips')->truncate();
        Schema::enableForeignKeyConstraints();

        $months = [];
        $currentDate = Carbon::parse('2026-07-01');
        for ($i = 5; $i >= 0; $i--) {
            $months[] = $currentDate->copy()->subMonths($i)->format('Y-m');
        }

        foreach ($karyawans as $karyawan) {
            $base = PayrollCalculator::calculate($karyawan);
            
            foreach ($months as $m) {
                // Randomize some inputs
                $shift = rand(5, 15) * 15000;
                $radiologi = $karyawan->jabatan->first() && str_contains(strtolower($karyawan->jabatan->first()->nama), 'radiologi') ? 250000 : 0;
                $lembur = rand(0, 5) * 75000;
                $thr = ($m === '2026-04') ? (double)$base['gaji_pokok'] : 0; // THR in April
                
                $potAbsensi = rand(0, 3) * 50000;
                $potCashBon = rand(0, 1) * rand(50000, 150000);
                $potObat = rand(0, 1) * rand(20000, 80000);
                $potLain = rand(0, 1) * 25000;
                $potBank = rand(0, 1) * 150000;
                
                $bpjsKeluargaTambahan = rand(0, 2);
                
                // Total Earnings
                $totalEarnings = (double)$base['gaji_pokok'] +
                    (double)$base['tunjangan_tetap'] +
                    (double)$base['tunjangan_absensi'] +
                    (double)$base['tunjangan_jabatan'] +
                    (double)$shift +
                    (double)$radiologi +
                    (double)$lembur +
                    (double)$thr;
                    
                // BPJS & PPh21 calculations
                $deductions = PayrollCalculator::calculateDeductions(
                    $base['gaji_pokok'],
                    $base['tunjangan_tetap'],
                    $totalEarnings,
                    $bpjsKeluargaTambahan
                );
                
                $bpjsKes = $deductions['potongan_bpjs_kes'];
                $bpjsTk = $deductions['potongan_bpjs_tk'];
                $pph21 = $deductions['potongan_pph21'];
                
                // Total Potongan
                $totalPotongan = (double)$potAbsensi +
                    (double)$potCashBon +
                    (double)$potObat +
                    (double)$potLain +
                    (double)$bpjsKes +
                    (double)$bpjsTk;
                    
                // Net Salary
                $gajiBersih = $totalEarnings - $totalPotongan - $pph21 - $potBank;
                
                DB::table('sdm_payroll_slips')->insert([
                    'karyawan_id' => $karyawan->id,
                    'periode' => $m,
                    'gaji_pokok' => $base['gaji_pokok'],
                    'tunjangan_tetap' => $base['tunjangan_tetap'],
                    'tunjangan_absensi' => $base['tunjangan_absensi'],
                    'tunjangan_jabatan' => $base['tunjangan_jabatan'],
                    'tunjangan_shift' => $shift,
                    'tunjangan_radiologi' => $radiologi,
                    'tunjangan_lain' => 0,
                    'uang_lembur' => $lembur,
                    'tunjangan_hari_raya' => $thr,
                    'potongan_absensi' => $potAbsensi,
                    'potongan_cash_bon' => $potCashBon,
                    'potongan_obat' => $potObat,
                    'potongan_bpjs_kes' => $bpjsKes,
                    'potongan_bpjs_tk' => $bpjsTk,
                    'potongan_lain' => $potLain,
                    'potongan_pph21' => $pph21,
                    'potongan_bank' => $potBank,
                    'bpjs_keluarga_tambahan' => $bpjsKeluargaTambahan,
                    'total_gaji' => $totalEarnings,
                    'total_potongan' => $totalPotongan,
                    'gaji_bersih' => $gajiBersih,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
