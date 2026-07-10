<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Sdm\Karyawan;

class AbsensiImport implements ToCollection
{
    public $parsedData = [];
    public $periodeAwal = null;
    public $periodeAkhir = null;
    public $anomaliCount = 0;
    public $totalBaris = 0;

    /**
    * @param Collection $rows
    */
    public function collection(Collection $rows)
    {
        // Skip first 6 rows (header & metadata)
        $dataRows = $rows->slice(6);
        $karyawanList = Karyawan::pluck('id', 'nip')->toArray(); // Buat mapping nip => id

        foreach ($dataRows as $row) {
            // Cek apakah baris kosong (kolom employee ID = 0 / kosong)
            if (!isset($row[0]) || empty(trim($row[0]))) {
                continue;
            }

            $employeeId = trim($row[0]);
            $nama = trim($row[1] ?? '');
            $dateStr = trim($row[2] ?? '');
            
            if (empty($dateStr)) {
                continue;
            }

            try {
                $tanggal = Carbon::createFromFormat('d-m-Y', $dateStr)->format('Y-m-d');
            } catch (\Exception $e) {
                // Gagal parse tanggal
                $this->anomaliCount++;
                continue; 
            }

            // Normalisasi jam (; menjadi :)
            $checkIn = $this->normalizeTime($row[3] ?? '');
            $checkOut = $this->normalizeTime($row[4] ?? '');
            $clockIn = $this->normalizeTime($row[5] ?? '');
            $clockOut = $this->normalizeTime($row[6] ?? '');
            $catatan = trim($row[7] ?? '');

            // Deteksi min/max tanggal
            if (is_null($this->periodeAwal) || $tanggal < $this->periodeAwal) {
                $this->periodeAwal = $tanggal;
            }
            if (is_null($this->periodeAkhir) || $tanggal > $this->periodeAkhir) {
                $this->periodeAkhir = $tanggal;
            }

            // Validasi format time jika ada isinya
            $isAnomali = false;
            if ($clockIn && !preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $clockIn)) { $isAnomali = true; }
            if ($clockOut && !preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $clockOut)) { $isAnomali = true; }

            if ($isAnomali) {
                $this->anomaliCount++;
            }

            // Matching karyawan
            $statusMatching = 'unmatched';
            $karyawanId = null;

            if (isset($karyawanList[$employeeId])) {
                $statusMatching = 'matched';
                $karyawanId = $karyawanList[$employeeId];
            } elseif (in_array($employeeId, ['000', '0000', '00000']) || strlen($employeeId) > 8) {
                $statusMatching = 'ambiguous';
            }

            $this->parsedData[] = [
                'employee_id_mentah' => $employeeId,
                'nama_mentah' => $nama,
                'tanggal' => $tanggal,
                'check_in_jadwal' => $checkIn ?: null,
                'check_out_jadwal' => $checkOut ?: null,
                'clock_in_aktual' => $clockIn ?: null,
                'clock_out_aktual' => $clockOut ?: null,
                'catatan_mesin' => $catatan ?: null,
                'karyawan_id' => $karyawanId,
                'status_matching' => $statusMatching,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $this->totalBaris++;
        }
    }

    private function normalizeTime($timeStr)
    {
        $timeStr = trim($timeStr);
        if (empty($timeStr)) return null;
        return str_replace(';', ':', $timeStr);
    }
}
