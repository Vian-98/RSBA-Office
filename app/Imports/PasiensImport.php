<?php

namespace App\Imports;

use Illuminate\Database\Eloquent\Model;
use App\Models\JmPasien;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PasiensImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    /**
     *  To handle importing data in batches of 50 rows, 
     *  Maatwebsite\Excel\Concerns\WithChunkReading and Maatwebsite\Excel\Concerns\ShouldQueue traits
     * 
     */
    protected $batchData = [];
    // protected $batchSize = 50;
    /**
     * @param array $row
     *
     * @return Model|null
     */
    public function model(array $row)
    {
        // collecting row
        $this->batchData[] = $this->prepareRowData($row);

        return null;

        //batch size reached, proses batch
        // if (count($this->batchData) >= $this->batchSize) {
        //     $this->insertBatch();
        // }
    }

    /**
     * Prepare data to array
     */
    protected function prepareRowData(array $row)
    {
        return [

            // data untuk kondisi
            'where' =>
            $row['sep'] ?  //if sep !empty
                [
                    'sep' => $row['sep'] //kondisi menggunakan sep
                ] : //else menggunakan beberapa berikut ini:
                [
                    'no_rekmedis' => $row['no_rekmedis'],
                    'tgl_checkout' => $row['tgl_checkout'],
                    'layanan' => $row['layanan'],
                    'cabar' => $row['cabar'],
                ],

            // data untuk insert or update
            'data' =>
            [
                'nama_pasien' => $row['nama_pasien'],
                'no_rekmedis' => $row['no_rekmedis'],
                'sep' => $row['sep'] ?? null,
                'dpjp' => $row['dpjp'],
                'tgl_checkin' => $row['tgl_checkin'],
                'tgl_checkout' => $row['tgl_checkout'],
                'layanan' => $row['layanan'],
                'cabar' => $row['cabar'],
                'klaim' => $row['klaim'],
                'tarif_rs' => $row['tarif_rs'],
                'kelas_rawat' => $row['kelas_rawat'] ?? null,
                'diaglist' => $row['diaglist'] ?? null,
                'proclist' => $row['proclist'] ?? null,
                'deskripsi_inacbg' => $row['deskripsi_inacbg'] ?? null
            ]

        ];
    }

    // Insert batch into database
    protected function insertBatch()
    {
        if (empty($this->batchData)) {
            return;
        }

        DB::transaction(
            function () {
                // each data $batchData
                foreach ($this->batchData as $row) {
                    JmPasien::updateOrCreate(
                        $row['where'], //kondisi
                        $row['data'] //data to insert or update
                    );
                }
            }
        );

        // Clear batch data
        $this->batchData = [];
    }

    /**
     * Called when chunk reading is completed
     */
    public function onChunk($chunk)
    {
        // Process the collected batch data for this chunk
        $this->insertBatch();
    }


    /**
     * Specify chunk size for reading.
     */
    public function chunkSize(): int
    {
        // return $this->batchSize;
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }


    /**
     * Handle end-of-file batch processing.
     */
    function __destruct()
    {
        // Process remaining rows in batch
        if (!empty($this->batchData)) {
            $this->insertBatch();
        }
    }
}
