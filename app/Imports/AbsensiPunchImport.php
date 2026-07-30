<?php

namespace App\Imports;

use App\Models\Sdm\AbsensiImportLog;
use App\Models\Sdm\AbsensiRawPunch;
use App\Models\Sdm\AbsensiStaging;
use App\Models\Sdm\Karyawan;
use App\Services\AbsensiClearingService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class AbsensiPunchImport
{
    protected AbsensiClearingService $clearingService;

    public function __construct(?AbsensiClearingService $clearingService = null)
    {
        $this->clearingService = $clearingService ?? app(AbsensiClearingService::class);
    }

    /**
     * Process raw CSV punch file upload.
     *
     * 1. Read CSV, handle UTF-8 BOM, skip header.
     * 2. Dynamically map columns based on header names.
     * 3. Insert raw rows to `sdm_absensi_raw_punch`.
     * 4. Run `AbsensiClearingService::clear($importLogId)`.
     * 5. Perform karyawan matching and save paired records to `sdm_absensi_staging`.
     * 6. Update `sdm_absensi_import_log` summary.
     */
    public function import(UploadedFile|string $file, int $importLogId): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (!file_exists($path)) {
            throw new \InvalidArgumentException("File tidak ditemukan: {$path}");
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \RuntimeException("Gagal membuka file CSV.");
        }

        // Remove UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Detect delimiter (comma vs semicolon) from first line
        $firstLine = fgets($handle);
        $delimiter = (str_contains($firstLine, ';') && !str_contains($firstLine, ',')) ? ';' : ',';
        rewind($handle);

        // Skip UTF-8 BOM again if rewind reset it
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Read header row
        $header = fgetcsv($handle, 0, $delimiter);

        // Dynamic Header Index Mapping
        $colNo         = null;
        $colEmployeeId = null;
        $colFirstName  = null;
        $colLastName   = null;
        $colDept       = null;
        $colDate       = null;
        $colTime       = null;
        $colPunchState = null;
        $colDataSource = null;

        if (is_array($header)) {
            foreach ($header as $idx => $name) {
                $clean = strtolower(trim(str_replace(["\xEF\xBB\xBF", '"', "'"], '', $name)));
                if (in_array($clean, ['no', 'no.', 'no urut'])) {
                    $colNo = $idx;
                } elseif (str_contains($clean, 'employee id') || str_contains($clean, 'emp id') || $clean === 'pin') {
                    $colEmployeeId = $idx;
                } elseif ($clean === 'first name' || str_contains($clean, 'nama') || $clean === 'name') {
                    $colFirstName = $idx;
                } elseif ($clean === 'last name') {
                    $colLastName = $idx;
                } elseif (str_contains($clean, 'department') || str_contains($clean, 'departemen') || $clean === 'dept') {
                    $colDept = $idx;
                } elseif ($clean === 'date' || str_contains($clean, 'tanggal') || $clean === 'tgl') {
                    $colDate = $idx;
                } elseif ($clean === 'time' || str_contains($clean, 'jam') || $clean === 'waktu') {
                    $colTime = $idx;
                } elseif (str_contains($clean, 'punch state') || str_contains($clean, 'status')) {
                    $colPunchState = $idx;
                } elseif (str_contains($clean, 'data source') || str_contains($clean, 'device')) {
                    $colDataSource = $idx;
                }
            }
        }

        // Fallbacks if header mapping didn't find specific columns
        $colNo         = $colNo ?? 0;
        $colEmployeeId = $colEmployeeId ?? 1;
        $colFirstName  = $colFirstName ?? 2;
        $colDept       = $colDept ?? 3;
        $colDate       = $colDate ?? 4;
        $colTime       = $colTime ?? 5;
        $colPunchState = $colPunchState ?? 6;
        $colDataSource = $colDataSource ?? 7;

        $rawInsertBatch = [];
        $totalRawRows = 0;
        $periodeAwal = null;
        $periodeAkhir = null;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $empIdVal = isset($colEmployeeId) && isset($row[$colEmployeeId]) ? trim($row[$colEmployeeId]) : '';
            if (empty($row) || $empIdVal === '') {
                continue;
            }

            $noUrut       = isset($row[$colNo]) && is_numeric(trim($row[$colNo])) ? (int) trim($row[$colNo]) : null;
            $employeeId   = $empIdVal;
            $firstName    = isset($row[$colFirstName]) ? trim($row[$colFirstName]) : '';
            $lastName     = ($colLastName !== null && isset($row[$colLastName])) ? trim($row[$colLastName]) : '';
            $namaMentah   = trim($firstName . ' ' . $lastName);
            $departemen   = isset($row[$colDept]) ? trim($row[$colDept]) : '';
            $dateStr      = isset($row[$colDate]) ? trim($row[$colDate]) : '';
            $timeStr      = isset($row[$colTime]) ? trim($row[$colTime]) : '';
            $punchState   = isset($row[$colPunchState]) ? trim($row[$colPunchState]) : '';
            $dataSource   = ($colDataSource !== null && isset($row[$colDataSource])) ? trim($row[$colDataSource]) : '';

            if (empty($dateStr) || empty($timeStr)) {
                continue;
            }

            // Safe date parsing
            try {
                if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $dateStr)) {
                    $tanggal = Carbon::createFromFormat('d-m-Y', $dateStr)->format('Y-m-d');
                } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
                    $tanggal = $dateStr;
                } else {
                    $tanggal = Carbon::parse($dateStr)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }

            // Safe time parsing & normalization (HH:mm or HH:mm:ss)
            $timeStr = str_replace(';', ':', $timeStr);
            if (!preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $timeStr)) {
                continue; // skip row if timeStr is not valid time (e.g. text like "Check In")
            }

            $timeParts = explode(':', $timeStr);
            $hours   = str_pad($timeParts[0], 2, '0', STR_PAD_LEFT);
            $minutes = str_pad($timeParts[1], 2, '0', STR_PAD_LEFT);
            $seconds = isset($timeParts[2]) ? str_pad($timeParts[2], 2, '0', STR_PAD_LEFT) : '00';
            $jam     = "{$hours}:{$minutes}:{$seconds}";

            try {
                $punchDatetime = Carbon::parse("{$tanggal} {$jam}")->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                continue;
            }

            if (is_null($periodeAwal) || $tanggal < $periodeAwal) {
                $periodeAwal = $tanggal;
            }
            if (is_null($periodeAkhir) || $tanggal > $periodeAkhir) {
                $periodeAkhir = $tanggal;
            }

            $rawInsertBatch[] = [
                'import_log_id'  => $importLogId,
                'no_urut'        => $noUrut,
                'employee_id'    => $employeeId,
                'nama_mentah'    => $namaMentah,
                'departemen'     => $departemen,
                'tanggal'        => $tanggal,
                'jam'            => $jam,
                'punch_datetime' => $punchDatetime,
                'punch_state'    => $punchState,
                'data_source'    => $dataSource,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            $totalRawRows++;

            if (count($rawInsertBatch) >= 500) {
                AbsensiRawPunch::insert($rawInsertBatch);
                $rawInsertBatch = [];
            }
        }

        if (!empty($rawInsertBatch)) {
            AbsensiRawPunch::insert($rawInsertBatch);
        }

        fclose($handle);

        // 2. Trigger clearing service
        $clearResult = $this->clearingService->clear($importLogId);

        // 3. Match karyawan and map to sdm_absensi_staging
        $karyawanList = Karyawan::whereNotNull('pin_absen')
            ->pluck('id', 'pin_absen')
            ->toArray();

        $stagingBatch = [];
        $matchedCount = 0;
        $unmatchedCount = 0;

        foreach ($clearResult->paired as $row) {
            $employeeId = $row['employee_id_mentah'];
            $statusMatching = 'unmatched';
            $karyawanId = null;

            if (isset($karyawanList[$employeeId])) {
                $statusMatching = 'matched';
                $karyawanId = $karyawanList[$employeeId];
                $matchedCount++;
            } elseif (in_array($employeeId, ['000', '0000', '00000']) || strlen($employeeId) > 8) {
                $statusMatching = 'ambiguous';
                $unmatchedCount++;
            } else {
                $unmatchedCount++;
            }

            // Extract TIME (H:i) from DATETIME string for clock_in_aktual / clock_out_aktual
            $clockInTime  = $row['clock_in_aktual'] ? Carbon::parse($row['clock_in_aktual'])->format('H:i') : null;
            $clockOutTime = $row['clock_out_aktual'] ? Carbon::parse($row['clock_out_aktual'])->format('H:i') : null;

            $stagingBatch[] = [
                'import_batch_id'    => $importLogId,
                'employee_id_mentah' => $employeeId,
                'nama_mentah'        => $row['nama_mentah'],
                'tanggal'            => $row['tanggal'],
                'check_in_jadwal'    => null,
                'check_out_jadwal'   => null,
                'clock_in_aktual'    => $clockInTime,
                'clock_out_aktual'   => $clockOutTime,
                'catatan_mesin'      => $row['catatan_mesin'],
                'karyawan_id'        => $karyawanId,
                'status_matching'    => $statusMatching,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];

            if (count($stagingBatch) >= 500) {
                AbsensiStaging::insert($stagingBatch);
                $stagingBatch = [];
            }
        }

        if (!empty($stagingBatch)) {
            AbsensiStaging::insert($stagingBatch);
        }

        // 4. Update import log summary
        $importLog = AbsensiImportLog::find($importLogId);
        if ($importLog) {
            $importLog->update([
                'periode_awal'    => $periodeAwal ?? $importLog->periode_awal,
                'periode_akhir'   => $periodeAkhir ?? $importLog->periode_akhir,
                'total_baris'     => count($clearResult->paired),
                'baris_matched'   => $matchedCount,
                'baris_unmatched' => $unmatchedCount,
                'baris_anomali'   => $clearResult->anomalyCount,
            ]);
        }

        return [
            'totalRawRows'   => $totalRawRows,
            'totalPaired'    => count($clearResult->paired),
            'duplicateCount' => $clearResult->duplicateCount,
            'anomalyCount'   => $clearResult->anomalyCount,
            'anomalies'      => $clearResult->anomalies,
        ];
    }
}
