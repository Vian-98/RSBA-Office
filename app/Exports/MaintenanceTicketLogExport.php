<?php

namespace App\Exports;

use App\Models\Maintenance\Request as MaintenanceRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MaintenanceTicketLogExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function collection()
    {
        if ($this->query) {
            return $this->query->get();
        }

        return MaintenanceRequest::with([
            'asset.barang',
            'asset.ruangan',
            'ruangan',
            'user_req.karyawan',
            'user_verif.karyawan',
            'jadwal.teknisi.user.karyawan',
            'jadwal.work.selesai_oleh.karyawan',
        ])->latest('created_at')->get();
    }

    public function headings(): array
    {
        return [
            'No. Tiket',
            'Waktu Pengajuan',
            'Kategori / Bidang',
            'Item / Perihal Kerusakan',
            'Kode Aset',
            'Lokasi Ruangan',
            'Diajukan Oleh',
            'Kontak Pelapor',
            'Prioritas',
            'Ket. Prioritas',
            'Status Permintaan',
            'Status Siklus Tiket',
            'Diverifikasi / Ditinjau Oleh',
            'Alasan Penolakan (Jika Ditolak)',
            'Jadwal Penanganan',
            'Teknisi Penanggung Jawab',
            'Waktu Mulai Pengerjaan',
            'Waktu Selesai Pengerjaan',
            'Catatan Teknisi',
            'Total Biaya (Rp)',
        ];
    }

    public function map($ticket): array
    {
        $jadwal = $ticket->jadwal;
        $work = $jadwal?->work;

        // Teknisi list
        $teknisiList = '-';
        if ($jadwal && $jadwal->teknisi->isNotEmpty()) {
            $teknisiList = $jadwal->teknisi
                ->map(fn($t) => $t->user?->karyawan?->nama ?? $t->user?->name ?? 'Teknisi')
                ->implode(', ');
        }

        // Priority Label
        $priorityLabel = match ($ticket->priority) {
            'penting' => 'Urgent',
            'darurat' => 'Emergency',
            default   => 'Normal',
        };

        // Ticket Status Lifecycle
        $lifecycleStatus = match ($ticket->ticket_status) {
            'open'        => 'Open / Menunggu Otorisasi',
            'rejected'    => 'Ditolak (Rejected)',
            'assigned'    => 'Disetujui & Dijadwalkan',
            'in_progress' => 'Sedang Dikerjakan (In Progress)',
            'resolved'    => 'Selesai (Resolved)',
            default       => ucfirst((string) $ticket->status),
        };

        return [
            $ticket->nomor_tiket ?? ('#REQ-' . $ticket->id),
            $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i:s') : '-',
            strtoupper($ticket->jenis ?? 'Umum'),
            $ticket->item_nama ?? ($ticket->asset?->barang?->nama ?? $ticket->note ?? '-'),
            $ticket->asset?->kode ?? 'Non-Aset',
            $ticket->lokasi_nama ?? ($ticket->asset?->ruangan?->nama ?? $ticket->ruangan?->nama ?? '-'),
            $ticket->user_request ?? $ticket->pelapor_nama ?? '-',
            $ticket->pelapor_kontak ?? '-',
            $priorityLabel,
            $ticket->ket_priority ?? '-',
            ucfirst((string) $ticket->status),
            $lifecycleStatus,
            $ticket->user_verif?->karyawan?->nama ?? $ticket->user_verif?->name ?? '-',
            $ticket->ket_reject ?? '-',
            $jadwal?->tanggal ? date('d/m/Y', strtotime($jadwal->tanggal)) : '-',
            $teknisiList,
            $work?->mulai ? date('d/m/Y H:i', strtotime($work->mulai)) : '-',
            $work?->selesai ? date('d/m/Y H:i', strtotime($work->selesai)) : '-',
            $work?->catatan ?? $jadwal?->note ?? '-',
            $work?->total_biaya ? number_format((float) $work->total_biaya, 0, ',', '.') : '0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4338CA'], // Indigo 700
                ],
            ],
        ];
    }
}
