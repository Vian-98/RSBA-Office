<?php

namespace App\Imports;

use App\Models\Sdm\Karyawan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PayrollImport implements ToCollection, WithHeadingRow
{
    protected string $periode;
    protected int $importedCount = 0;

    public function __construct(string $periode)
    {
        $this->periode = $periode;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $nip = trim((string) ($row['nip'] ?? ''));
            $nip = ltrim($nip, "'"); // Remove leading single quote if present
            if (empty($nip)) {
                continue;
            }

            $karyawan = Karyawan::where('nip', $nip)->first();
            if (!$karyawan) {
                continue;
            }

            // Update bank details if provided in Excel
            $namaBank = trim((string) ($row['nama_bank'] ?? ''));
            $noRekening = trim((string) ($row['no_rekening'] ?? ''));
            $noRekening = ltrim($noRekening, "'");

            $updateKaryawan = [];
            if (!empty($namaBank)) {
                $updateKaryawan['nama_bank'] = $namaBank;
            }
            if (!empty($noRekening)) {
                $updateKaryawan['no_rekening'] = $noRekening;
            }
            if (!empty($updateKaryawan)) {
                $karyawan->update($updateKaryawan);
            }

            // Extract salary numbers (cleaning formatted numbers if any)
            $gajiPokok = $this->cleanNumber($row['gaji_pokok'] ?? 0);
            $tunjanganTetap = $this->cleanNumber($row['tunjangan_tetap'] ?? 0);
            $tunjanganAbsensi = $this->cleanNumber($row['tunjangan_absensi'] ?? 0);
            $tunjanganJabatan = $this->cleanNumber($row['tunjangan_jabatan'] ?? 0);
            $tunjanganShift = $this->cleanNumber($row['tunjangan_shift'] ?? 0);
            $tunjanganRadiologi = $this->cleanNumber($row['tunjangan_radiologi'] ?? 0);
            $tunjanganLain = $this->cleanNumber($row['tunjangan_lain'] ?? 0);
            $uangLembur = $this->cleanNumber($row['uang_lembur'] ?? 0);
            $thr = $this->cleanNumber($row['tunjangan_thr'] ?? 0);

            $potAbsensi = $this->cleanNumber($row['potongan_absensi'] ?? 0);
            $potCashBon = $this->cleanNumber($row['potongan_cash_bon'] ?? 0);
            $potObat = $this->cleanNumber($row['potongan_obat'] ?? 0);
            $potLain = $this->cleanNumber($row['potongan_lain'] ?? 0);
            $potBank = $this->cleanNumber($row['potongan_bank'] ?? 0);
            $bpjsKes = $this->cleanNumber($row['bpjs_kesehatan'] ?? 0);
            $bpjsTk = $this->cleanNumber($row['bpjs_ketenagakerjaan'] ?? 0);
            $pph21 = $this->cleanNumber($row['pph_21'] ?? 0);

            $totalGaji = $gajiPokok + $tunjanganTetap + $tunjanganAbsensi + $tunjanganJabatan + $tunjanganShift + $tunjanganRadiologi + $tunjanganLain + $uangLembur + $thr;
            $totalPotongan = $potAbsensi + $potCashBon + $potObat + $potLain + $potBank + $bpjsKes + $bpjsTk + $pph21;
            $gajiBersih = $totalGaji - $totalPotongan;

            DB::table('sdm_payroll_slips')->updateOrInsert(
                [
                    'karyawan_id' => $karyawan->id,
                    'periode' => $this->periode,
                ],
                [
                    'gaji_pokok' => $gajiPokok,
                    'tunjangan_tetap' => $tunjanganTetap,
                    'tunjangan_absensi' => $tunjanganAbsensi,
                    'tunjangan_jabatan' => $tunjanganJabatan,
                    'tunjangan_shift' => $tunjanganShift,
                    'tunjangan_radiologi' => $tunjanganRadiologi,
                    'tunjangan_lain' => $tunjanganLain,
                    'uang_lembur' => $uangLembur,
                    'tunjangan_hari_raya' => $thr,
                    'potongan_absensi' => $potAbsensi,
                    'potongan_cash_bon' => $potCashBon,
                    'potongan_obat' => $potObat,
                    'potongan_lain' => $potLain,
                    'potongan_bank' => $potBank,
                    'potongan_bpjs_kes' => $bpjsKes,
                    'potongan_bpjs_tk' => $bpjsTk,
                    'potongan_pph21' => $pph21,
                    'total_gaji' => $totalGaji,
                    'total_potongan' => $totalPotongan,
                    'gaji_bersih' => $gajiBersih,
                    'created_by' => auth()->id(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $this->importedCount++;
        }
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    private function cleanNumber($value): float
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            // Remove 'Rp', whitespace, and quotes
            $str = trim(str_replace(['Rp', 'rp', 'RP', "'", '"', ' '], '', $value));
            if (empty($str)) {
                return 0.0;
            }

            // If string has thousand separator dots like "2.250.000"
            if (str_contains($str, '.') && !str_contains($str, ',')) {
                // If it's something like 2.250.000 (thousands separators)
                if (substr_count($str, '.') > 1 || strlen(substr(strrchr($str, '.'), 1)) === 3) {
                    $str = str_replace('.', '', $str);
                }
            } elseif (str_contains($str, '.') && str_contains($str, ',')) {
                // Indonesian format: 2.250.000,00 -> 2250000.00
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } elseif (str_contains($str, ',')) {
                // Format: 2250000,00 -> 2250000.00
                $str = str_replace(',', '.', $str);
            }

            return is_numeric($str) ? (float) $str : 0.0;
        }

        return 0.0;
    }
}
