<?php

namespace App\Livewire\Maintenance\Ticket;

use App\Models\Maintenance\Request as MaintenanceRequest;
use App\Models\Maintenance\TicketComment;
use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Title;
use Livewire\Component;
use TallStackUi\Traits\Interactions;
use Throwable;

#[Title('Detail Tiket')]
class Detail extends Component
{
    use Interactions;
    use AuthorizesFromRoute;

    public MaintenanceRequest $request;

    public function mount(int $id): void
    {
        $this->request = MaintenanceRequest::with([
            'asset.barang',
            'asset.ruangan',
            'user_req.karyawan',
            'user_verif.karyawan',
            'jadwal.teknisi.user.karyawan',
            'jadwal.work',
            'comments.user.karyawan',
        ])->findOrFail($id);
    }

    /** Timeline status untuk tampilan di view */
    public function getTimeline(): array
    {
        $jadwal = $this->request->jadwal;
        $work   = $jadwal?->work;

        $steps = [
            [
                'label'  => 'Tiket Dibuat',
                'sub'    => 'Oleh ' . ($this->request->user_request ?? '-'),
                'time'   => $this->request->created_at?->format('d M Y H:i'),
                'done'   => true,
                'active' => false,
                'icon'   => 'tabler.ticket',
                'color'  => 'green',
            ],
            [
                'label'  => 'Disetujui & Dijadwalkan',
                'sub'    => $jadwal ? 'Jadwal: ' . \Carbon\Carbon::parse($jadwal->tanggal)->format('d M Y') : 'Menunggu persetujuan',
                'time'   => $jadwal?->created_at?->format('d M Y H:i'),
                'done'   => (bool) $jadwal,
                'active' => !$jadwal,
                'icon'   => 'tabler.calendar-check',
                'color'  => $jadwal ? 'green' : 'gray',
            ],
            [
                'label'  => 'In Progress',
                'sub'    => $work ? 'Mulai: ' . \Carbon\Carbon::parse($work->mulai)->format('d M Y H:i') : 'Belum dimulai',
                'time'   => $work?->mulai ? \Carbon\Carbon::parse($work->mulai)->format('d M Y H:i') : null,
                'done'   => $work && in_array($work->status, ['in_progress', 'done']),
                'active' => $jadwal && !$work,
                'icon'   => 'tabler.tool',
                'color'  => ($work && in_array($work->status, ['in_progress', 'done'])) ? 'green' : 'gray',
            ],
            [
                'label'  => 'Selesai',
                'sub'    => ($work?->status === 'done') ? 'Perbaikan selesai' : 'Menunggu penyelesaian',
                'time'   => $work?->selesai ? \Carbon\Carbon::parse($work->selesai)->format('d M Y H:i') : null,
                'done'   => $work?->status === 'done',
                'active' => $work?->status === 'in_progress',
                'icon'   => 'tabler.circle-check',
                'color'  => ($work?->status === 'done') ? 'green' : 'gray',
            ],
        ];

        return $steps;
    }

    public function render()
    {
        return view('livewire.maintenance.ticket.detail', [
            'timeline' => $this->getTimeline(),
        ]);
    }
}
