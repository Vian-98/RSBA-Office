<?php

namespace App\Livewire\Kepegawaian\Absensi;

use App\Imports\AbsensiImport;
use App\Imports\AbsensiPunchImport;
use App\Models\Sdm\AbsensiImportLog;
use App\Models\Sdm\AbsensiStaging;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;
use Livewire\WithFileUploads;
use TallStackUi\Traits\Interactions;

class Import extends Component
{
    use WithFileUploads, Interactions;

    public $file;
    public $filePath = null;
    public $formatFile = 'punch_csv'; // 'punch_csv' or 'excel'
    public $previewData = null;
    public $isProcessing = false;

    public function mount()
    {
        abort_unless(
            auth()->user()?->can('view-kepegawaian-absensi'),
            403,
            'Anda tidak memiliki izin (view-kepegawaian-absensi) untuk melakukan Import Absensi.'
        );
    }

    public function updatedFile()
    {
        if ($this->formatFile === 'punch_csv') {
            $this->validate([
                'file' => 'required|mimetypes:text/plain,text/csv,text/comma-separated-values,application/csv,application/vnd.ms-excel|max:10240',
            ], [
                'file.mimetypes' => 'File harus berformat CSV.',
            ]);
        } else {
            $this->validate([
                'file' => 'required|mimes:xlsx,xls|max:10240',
            ]);
        }

        $this->isProcessing = true;

        try {
            $this->filePath = $this->file->store('absensi-temp');
            $fullPath = Storage::path($this->filePath);

            if ($this->formatFile === 'punch_csv') {
                // Pre-create import log
                $log = AbsensiImportLog::create([
                    'nama_file'     => $this->file->getClientOriginalName(),
                    'periode_awal'  => now()->toDateString(),
                    'periode_akhir' => now()->toDateString(),
                    'total_baris'   => 0,
                    'status'        => 'diunggah',
                    'diunggah_oleh' => auth()->id() ?? 1,
                ]);

                $importer = new AbsensiPunchImport();
                $result = $importer->import($fullPath, $log->id);

                if ($result['totalRawRows'] === 0) {
                    $log->delete();
                    Storage::delete($this->filePath);
                    $this->toast()->error('Gagal', 'File CSV kosong atau format tidak sesuai.')->send();
                    $this->previewData = null;
                    $this->isProcessing = false;
                    return;
                }

                $log->refresh();

                $this->previewData = [
                    'format'          => 'punch_csv',
                    'import_log_id'   => $log->id,
                    'periode_awal'    => $log->periode_awal,
                    'periode_akhir'   => $log->periode_akhir,
                    'total_tap'       => $result['totalRawRows'],
                    'total_hari'      => $result['totalPaired'],
                    'total_duplikat'  => $result['duplicateCount'],
                    'anomali'         => $result['anomalyCount'],
                    'anomalies'       => array_slice($result['anomalies'], 0, 10), // max 10 for preview
                ];
            } else {
                // Legacy Excel import
                $import = new AbsensiImport();
                Excel::import($import, $fullPath);

                if ($import->totalBaris === 0) {
                    Storage::delete($this->filePath);
                    $this->toast()->error('Gagal', 'File kosong atau format tidak sesuai.')->send();
                    $this->previewData = null;
                    $this->isProcessing = false;
                    return;
                }

                $this->previewData = [
                    'format'        => 'excel',
                    'periode_awal'  => $import->periodeAwal,
                    'periode_akhir' => $import->periodeAkhir,
                    'total_baris'   => $import->totalBaris,
                    'anomali'       => $import->anomaliCount,
                ];
            }
        } catch (\Exception $e) {
            $this->toast()->error('Gagal Parsing', 'Gagal memproses file: ' . $e->getMessage())->send();
            $this->previewData = null;
        }

        $this->isProcessing = false;
    }

    public function prosesImport()
    {
        if (!$this->previewData) return;

        if ($this->previewData['format'] === 'punch_csv') {
            $logId = $this->previewData['import_log_id'];

            if ($this->filePath) {
                Storage::delete($this->filePath);
            }

            $this->toast()->success('Sukses', 'Data CSV berhasil di-clearing dan diunggah ke staging.')->send();
            return redirect()->route('kepegawaian.absensi.rekonsiliasi', ['batchId' => $logId]);
        }

        // Legacy Excel import execution
        if (!$this->filePath) return;

        DB::beginTransaction();
        try {
            $import = new AbsensiImport();
            Excel::import($import, Storage::path($this->filePath));
            $parsedData = $import->parsedData;

            $log = AbsensiImportLog::create([
                'nama_file'     => $this->file->getClientOriginalName(),
                'periode_awal'  => $this->previewData['periode_awal'],
                'periode_akhir' => $this->previewData['periode_akhir'],
                'total_baris'   => $this->previewData['total_baris'],
                'baris_anomali' => $this->previewData['anomali'],
                'status'        => 'diunggah',
                'diunggah_oleh' => auth()->id() ?? 1,
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
                'baris_matched'   => $matchedCount,
                'baris_unmatched' => $unmatchedCount,
            ]);

            foreach (array_chunk($parsedData, 200) as $chunk) {
                AbsensiStaging::insert($chunk);
            }

            Storage::delete($this->filePath);
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
