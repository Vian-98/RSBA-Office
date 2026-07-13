<?php

namespace App\Livewire\Kepegawaian\Absensi;

use Livewire\Component;

use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AbsensiImport;
use App\Models\Sdm\AbsensiImportLog;
use App\Models\Sdm\AbsensiStaging;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

class Import extends Component
{
    use WithFileUploads, Interactions;

    public $file;
    public $filePath = null;
    public $previewData = null;
    public $isProcessing = false;

    public function updatedFile()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240', // Maksimal 10MB
        ]);

        $this->isProcessing = true;

        try {
            // Simpan file secara lokal
            $this->filePath = $this->file->store('absensi-temp');

            $import = new AbsensiImport();
            Excel::import($import, \Illuminate\Support\Facades\Storage::path($this->filePath));
            
            if ($import->totalBaris === 0) {
                $this->toast()->error('Gagal', 'File kosong atau format tidak sesuai.')->send();
                $this->previewData = null;
                $this->isProcessing = false;
                return;
            }

            $this->previewData = [
                'periode_awal' => $import->periodeAwal,
                'periode_akhir' => $import->periodeAkhir,
                'total_baris' => $import->totalBaris,
                'anomali' => $import->anomaliCount,
            ];

        } catch (\Exception $e) {
            $this->toast()->error('Gagal Parsing', 'Gagal memproses file: ' . $e->getMessage())->send();
            $this->previewData = null;
        }

        $this->isProcessing = false;
    }

    public function prosesImport()
    {
        if (!$this->previewData || !$this->filePath) return;

        DB::beginTransaction();
        try {
            // Baca ulang file dari path penyimpanan
            $import = new AbsensiImport();
            Excel::import($import, \Illuminate\Support\Facades\Storage::path($this->filePath));
            $parsedData = $import->parsedData;

            $log = AbsensiImportLog::create([
                'nama_file' => $this->file->getClientOriginalName(),
                'periode_awal' => $this->previewData['periode_awal'],
                'periode_akhir' => $this->previewData['periode_akhir'],
                'total_baris' => $this->previewData['total_baris'],
                'baris_anomali' => $this->previewData['anomali'],
                'status' => 'diunggah',
                'diunggah_oleh' => auth()->id(),
            ]);

            $matchedCount = 0;
            $unmatchedCount = 0;

            foreach ($parsedData as &$row) {
                $row['import_batch_id'] = $log->id;
                if ($row['status_matching'] === 'matched') {
                    $matchedCount++;
                } else {
                    $unmatchedCount++;
                }
            }

            $log->update([
                'baris_matched' => $matchedCount,
                'baris_unmatched' => $unmatchedCount,
            ]);

            foreach (array_chunk($parsedData, 200) as $chunk) {
                AbsensiStaging::insert($chunk);
            }

            // Hapus file temp setelah berhasil di-import
            \Illuminate\Support\Facades\Storage::delete($this->filePath);

            DB::commit();

            $this->toast()->success('Sukses', 'Data berhasil diunggah ke staging.')->send();
            return redirect()->route('kepegawaian.absensi.rekonsiliasi', ['batchId' => $log->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->toast()->error('Error', 'Gagal menyimpan data: ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.kepegawaian.absensi.import');
    }
}
