<?php

namespace App\Imports;

use Throwable;
use Illuminate\Database\Eloquent\Model;
use App\Models\JmDokter;
use App\Models\JmPasien;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithGroupedHeadingRow;

// FIXME : Import data dokter dari excel ke database dengan batch/chunck
// https://docs.laravel-excel.com/3.1/imports/batch-inserts.html

class VisiteImport implements ToCollection, WithGroupedHeadingRow
{
    /**
     * @param array $row
     *
     * @return Model|null
     */
    public function collection(Collection $rows)
    {
        // Batch
        $currentBatch = [
            'noRekmedis' => null,
            'tglCheckout' => null,
            'pasienId' => null,
            'spesialis' => [],
            'umum' => [],
            'operator' => [],
            'span' => [],
        ];

        // Track invalid row, dapat di skip jika sudah di ketahui invalid
        $invalidRow = [];

        foreach ($rows as $row) {
            $startBatchTime = microtime(true);
            $noRekmedis = $row['no_rekmedis'];
            $tglCheckout = $row['tgl_checkout'];

            // buat key sebagai indetifier row
            $recordKey = $this->makeRecordKey($noRekmedis, $tglCheckout);

            // Skip if telah diketahui kombinasi [no_rekmedis, tgl_checkouot] invalid
            if (isset($invalidRow[$recordKey])) {
                continue;
            }

            // Jika row saat ini sama dengan current batch, populate data saja
            if (
                $currentBatch['noRekmedis'] === $noRekmedis
                && $currentBatch['tglCheckout'] === $tglCheckout
            ) {
                $this->populateData($currentBatch, $row);
                continue;
            }

            // Jika row saat ini tidak sama dengan current batch, insert batch data sebelumnya
            // dan, get PasienId dari database

            $pasien = $this->getPasienId($noRekmedis, $tglCheckout);

            // Skip if no valid patient found
            if (!$pasien) {
                $invalidRow[$recordKey] = true;
                continue;
            }

            // If we have previous batch data, process it before starting new batch
            if ($currentBatch['noRekmedis'] !== null && $currentBatch['pasienId'] !== null) {

                // insert Batch
                $this->insertBatchData(
                    $currentBatch['noRekmedis'],
                    $currentBatch['pasienId'],
                    $currentBatch['spesialis'],
                    $currentBatch['umum'],
                    $currentBatch['operator'],
                    $currentBatch['span']
                );

                // make log info
                $endBatchTime  = microtime(true);
                Log::info("Row [MRN: " . $currentBatch['noRekmedis'] .
                    ", Tgl Checkout : " . $currentBatch['tglCheckout'] .
                    ", pasienId : " . $currentBatch['pasienId'] .
                    " inserted, time : " . number_format(($endBatchTime - $startBatchTime), 3, '.', '') . "s");

                // Reset batch, dan new batch untuk 'no rekmedis'
                $this->resetBatch($currentBatch);
            }


            // NEW BATCH POPULATION
            // Set new batch data
            $currentBatch['noRekmedis'] = $noRekmedis;
            $currentBatch['tglCheckout'] = $tglCheckout;
            $currentBatch['pasienId'] = $pasien->id;
            // Populate data
            $this->populateData($currentBatch, $row);
            //  END, NEW BATCH POPULATION
        }
        // end each row


        // Process the final batch
        if ($currentBatch['noRekmedis'] !== null) {
            $this->insertBatchData(
                $currentBatch['noRekmedis'],
                $currentBatch['pasienId'],
                $currentBatch['spesialis'],
                $currentBatch['umum'],
                $currentBatch['operator'],
                $currentBatch['span']
            );
            $this->resetBatch($currentBatch);

            $endFinal  = microtime(true);
            Log::info("Process final batch, time execution :" . number_format(($endFinal - $startBatchTime), 3, '.', '') . "s");
        }
    }

    private function makeRecordKey(string $noRekmedis, string $tglCheckout): string
    {
        return "{$noRekmedis}_{$tglCheckout}";
    }

    private function getPasienId($noRekmedis, $tglCheckout)
    {
        return JmPasien::where('no_rekmedis', $noRekmedis)
            ->where('tgl_checkout', $tglCheckout)
            ->where('layanan', 'ranap')
            ->first();
    }

    // populate data
    private function populateData(array &$currentBatch, $row)
    {
        $this->spesialisData($currentBatch['spesialis'], $row);
        $this->umumData($currentBatch['umum'], $row);
        $this->operatorData($currentBatch['operator'], $row);
        $this->spanData($currentBatch['span'], $row);
    }

    // accumulate data 'umum'
    private function umumData(array &$batchUmum, $row): void
    {
        // Accumulate `umum` data
        if (!empty($row['umum'])) {
            foreach ($row['umum'] as $index => $dokter) {
                if (!empty($dokter)) {
                    $jumlahUmum = $row['jumlah_umum'][$index] ?? 0;
                    $batchUmum[$dokter] = ($batchUmum[$dokter] ?? 0) + $jumlahUmum;
                }
            }
        }
    }

    // accumulate data 'spesialis'
    private function spesialisData(array &$batchSpesialis, $row)
    {
        // Accumulate `spesialis` data
        if (!empty($row['spesialis'])) {
            foreach ($row['spesialis'] as $index => $dokter) {
                if (!empty($dokter)) {
                    $jumlahSpesialis = $row['jumlah_spesialis'][$index] ?? 0;
                    $batchSpesialis[$dokter] = ($batchSpesialis[$dokter] ?? 0) + $jumlahSpesialis;
                }
            }
        }
    }

    // accumulate data 'operator'
    private function operatorData(array &$batchOperator, $row): void
    {
        if (!empty($row['operator'])) {
            $operator = $row['operator'];
            $jumlahOperator = 1;
            $batchOperator[$operator] = ($batchOperator[$operator] ?? 0) + $jumlahOperator;
        }
    }

    // accumulate data 'span'
    private function spanData(array &$batchSpan, $row): void
    {
        if (!empty($row['span'])) {
            $span = $row['span'];
            $jumlahSpan = 1;
            $batchSpan[$span] = ($batchSpan[$span] ?? 0) + $jumlahSpan;
        }
    }

    // insert data dengan batch
    private function insertBatchData($noRekmedis, $pasienId, $batchSpesialis, $batchUmum, $batchOperator, $batchSpan)
    {
        // start Insert
        DB::beginTransaction();
        try {
            // clean up dokter database by 
            $noRekmedis;
            JmDokter::where('jm_pasien_id', $pasienId)->delete();

            if (!empty($batchSpesialis)) {
                foreach ($batchSpesialis as $dokter => $jumlah) {
                    JmDokter::create([
                        'jm_pasien_id' => $pasienId,
                        'dokter' => $dokter,
                        'jumlah' => $jumlah,
                        'status' => 'sp',
                    ]);
                }
            }

            if (!empty($batchUmum)) {
                foreach ($batchUmum as $dokter => $jumlah) {
                    JmDokter::create([
                        'jm_pasien_id' => $pasienId,
                        'dokter' => $dokter,
                        'jumlah' => $jumlah,
                        'status' => 'um',
                    ]);
                }
            }


            if (!empty($batchOperator)) {
                foreach ($batchOperator as $dokter => $jumlah) {
                    JmPasien::where('id', $pasienId)->update(['dpjp' => $dokter]);

                    JmDokter::create([
                        'jm_pasien_id' => $pasienId,
                        'dokter' => $dokter,
                        'jumlah' => $jumlah,
                        'status' => 'dpjp',
                    ]);
                }
            }

            if (!empty($batchSpan)) {
                foreach ($batchSpan as $dokter => $jumlah) {
                    JmDokter::create([
                        'jm_pasien_id' => $pasienId,
                        'dokter' => $dokter,
                        'jumlah' => $jumlah,
                        'status' => 'an',
                    ]);
                }
            }
            DB::commit();
            Log::info("Commmited for : [MRN: $noRekmedis, pasienId : $pasienId]");
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
        }
    }

    private function resetBatch(array &$batch): void
    {
        $batch['noRekmedis'] = null;
        $batch['tglCheckout'] = null;
        $batch['pasienId'] = null;
        $batch['spesialis'] = [];
        $batch['umum'] = [];
        $batch['operator'] = [];
        $batch['span'] = [];
    }
}
