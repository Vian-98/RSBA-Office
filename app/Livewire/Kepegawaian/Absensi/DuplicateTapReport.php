<?php

namespace App\Livewire\Kepegawaian\Absensi;

use App\Models\Sdm\AbsensiImportLog;
use App\Models\Sdm\AbsensiRawPunch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

class DuplicateTapReport extends Component
{
    use WithPagination, Interactions;

    public $logId;
    public $search = '';
    public $importLog;

    public function mount($logId = null)
    {
        abort_unless(
            auth()->user()?->can('view-kepegawaian-absensi'),
            403,
            'Anda tidak memiliki izin (view-kepegawaian-absensi) untuk mengakses Laporan Tap Ganda.'
        );
        $this->logId = $logId;
        if ($this->logId) {
            $this->importLog = AbsensiImportLog::find($this->logId);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function exportCsv()
    {
        $query = AbsensiRawPunch::query()
            ->from('sdm_absensi_raw_punch as discarded')
            ->leftJoin('sdm_absensi_raw_punch as ref', 'ref.id', '=', 'discarded.duplicate_reference_id')
            ->where('discarded.is_discarded', true);

        if ($this->logId) {
            $query->where('discarded.import_log_id', $this->logId);
        }

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('discarded.employee_id', 'like', "%{$search}%")
                  ->orWhere('discarded.nama_mentah', 'like', "%{$search}%");
            });
        }

        $records = $query->select([
            'discarded.employee_id',
            'discarded.nama_mentah',
            'discarded.tanggal',
            'ref.jam as jam_anchor',
            'discarded.jam as jam_dibuang',
            'discarded.discard_reason',
            'ref.punch_datetime as anchor_datetime',
            'discarded.punch_datetime as discarded_datetime',
        ])
        ->orderBy('discarded.employee_id')
        ->orderBy('discarded.tanggal')
        ->get();

        $filename = "Laporan_Tap_Duplikat_" . ($this->importLog->nama_file ?? 'all') . "_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Employee ID', 'Nama Employee', 'Tanggal', 'Jam Anchor (Referensi)', 'Jam Dibuang', 'Selisih Menit', 'Alasan']);

            foreach ($records as $row) {
                $selisih = '-';
                if ($row->anchor_datetime && $row->discarded_datetime) {
                    $diffSec = abs(strtotime($row->discarded_datetime) - strtotime($row->anchor_datetime));
                    $mins = floor($diffSec / 60);
                    $secs = $diffSec % 60;
                    $selisih = "{$mins} mnt {$secs} dtk";
                }

                fputcsv($file, [
                    $row->employee_id,
                    $row->nama_mentah,
                    $row->tanggal ? Carbon::parse($row->tanggal)->format('d-m-Y') : '-',
                    $row->jam_anchor ?? '-',
                    $row->jam_dibuang ?? '-',
                    $selisih,
                    $row->discard_reason ?? 'Duplicate tap (<=10 menit)',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $query = AbsensiRawPunch::query()
            ->from('sdm_absensi_raw_punch as discarded')
            ->leftJoin('sdm_absensi_raw_punch as ref', 'ref.id', '=', 'discarded.duplicate_reference_id')
            ->where('discarded.is_discarded', true);

        if ($this->logId) {
            $query->where('discarded.import_log_id', $this->logId);
        }

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('discarded.employee_id', 'like', "%{$search}%")
                  ->orWhere('discarded.nama_mentah', 'like', "%{$search}%");
            });
        }

        $records = $query->select([
            'discarded.id',
            'discarded.employee_id',
            'discarded.nama_mentah',
            'discarded.tanggal',
            'ref.jam as jam_anchor',
            'discarded.jam as jam_dibuang',
            'discarded.discard_reason',
            'ref.punch_datetime as anchor_datetime',
            'discarded.punch_datetime as discarded_datetime',
        ])
        ->orderBy('discarded.employee_id')
        ->orderBy('discarded.tanggal')
        ->paginate(15);

        $allImportLogs = AbsensiImportLog::latest()->take(20)->get();

        return view('livewire.kepegawaian.absensi.duplicate-tap-report', [
            'records'       => $records,
            'allImportLogs' => $allImportLogs,
        ]);
    }
}
