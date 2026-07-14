<?php

namespace App\Livewire\Profile;

use Livewire\Attributes\Isolate;
use Livewire\Attributes\Lazy;
use Livewire\Component;

use App\Models\Surat\SuratCuti;
use App\Models\Maintenance\Jadwal;
use App\Models\Surat\SuratSp3;
use App\Enums\StatusApproval;
use Illuminate\Support\Facades\Auth;

#[Lazy]
#[Isolate]
class Notif extends Component
{
    public array $notifications = [];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $items = [];

        // 1. General Welcome
        $items[] = [
            'id' => 'welcome',
            'type' => 'info',
            'icon' => 'tabler.info-circle',
            'title' => 'Selamat datang di RSBA!',
            'message' => 'Halo ' . (optional($user->karyawan)->nama ?? $user->email) . ', selamat bekerja dan berkontribusi hari ini.',
            'time' => '10 menit yang lalu',
            'route' => 'dashboard',
        ];

        // 2. Cuti (Leave Requests) Notifications
        try {
            if ($user->hasRole('Super-Admin') || $user->hasRole('Staff-SDM')) {
                // Pending cuti needing approval
                $pendingCutis = SuratCuti::with('karyawan')
                    ->whereIn('status', [StatusApproval::PENDING, StatusApproval::WAITING])
                    ->latest()
                    ->take(5)
                    ->get();

                foreach ($pendingCutis as $cuti) {
                    $items[] = [
                        'id' => 'cuti-pending-' . $cuti->id,
                        'type' => 'warning',
                        'icon' => 'tabler.calendar-time',
                        'title' => 'Persetujuan Cuti Baru',
                        'message' => 'Pengajuan cuti dari ' . ($cuti->karyawan->nama ?? 'Karyawan') . ' menunggu keputusan Anda.',
                        'time' => $cuti->created_at->diffForHumans(),
                        'route' => 'kepegawaian.surat.cuti',
                    ];
                }
            } else {
                // Own cuti notifications
                $myCutis = SuratCuti::where('karyawan_id', $user->karyawan_id)
                    ->latest()
                    ->take(5)
                    ->get();

                foreach ($myCutis as $cuti) {
                    $statusText = $cuti->status->nama();
                    $type = $cuti->status === StatusApproval::APPROVED ? 'success' : ($cuti->status === StatusApproval::REJECTED ? 'danger' : 'info');
                    $icon = $cuti->status === StatusApproval::APPROVED ? 'tabler.circle-check' : ($cuti->status === StatusApproval::REJECTED ? 'tabler.circle-x' : 'tabler.calendar-time');

                    $items[] = [
                        'id' => 'cuti-status-' . $cuti->id,
                        'type' => $type,
                        'icon' => $icon,
                        'title' => 'Status Pengajuan Cuti',
                        'message' => 'Pengajuan cuti Anda pada ' . \Carbon\Carbon::parse($cuti->tgl_mulai)->translatedFormat('d M Y') . ' telah ' . strtolower($statusText) . '.',
                        'time' => $cuti->updated_at->diffForHumans(),
                        'route' => 'kepegawaian.surat.cuti',
                    ];
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        // 3. Maintenance (Jadwal) Notifications
        try {
            if ($user->hasRole('Super-Admin') || $user->hasRole('Bagian-Umum')) {
                $maintenance = Jadwal::with('asset')
                    ->latest()
                    ->take(5)
                    ->get();

                foreach ($maintenance as $maint) {
                    $items[] = [
                        'id' => 'maint-' . $maint->id,
                        'type' => 'info',
                        'icon' => 'tabler.tool',
                        'title' => 'Jadwal Pemeliharaan',
                        'message' => 'Jadwal pemeliharaan baru untuk asset ' . ($maint->asset->nama ?? 'Asset') . ' pada ' . \Carbon\Carbon::parse($maint->tanggal)->translatedFormat('d M Y') . '.',
                        'time' => $maint->created_at->diffForHumans(),
                        'route' => 'umum.maintenance.index',
                    ];
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        // 4. Pelanggaran Aturan Jadwal Kerja (Untuk Koordinator Ruangan, Staff-SDM, & Super-Admin)
        try {
            $aturanService = app(\App\Services\AturanJadwalService::class);
            $query = \App\Models\Sdm\JadwalKerja::with(['ruangan']);

            if (!$user->hasRole(['Super-Admin', 'Staff-SDM'])) {
                // Jika koordinator ruangan biasa, hanya ambil ruangan yang dikoordinasikan
                $ruanganIds = $user->getRuanganKoordinatorIds();
                if (!empty($ruanganIds)) {
                    $query->whereIn('ruangan_id', $ruanganIds);
                } else {
                    $query->whereRaw('1=0');
                }
            }

            // Ambil jadwal yang aktif (tidak terkunci) dalam 60 hari terakhir
            $activeSchedules = $query->where('status', '!=', \App\Enums\StatusJadwalKerja::LOCKED)
                ->where('created_at', '>=', now()->subDays(60))
                ->get();

            foreach ($activeSchedules as $sched) {
                $violations = $aturanService->checkViolations($sched);
                if (!empty($violations)) {
                    $totalViolations = count($violations);
                    $sample = $violations[0]['message'];
                    
                    $items[] = [
                        'id' => 'jadwal-violation-' . $sched->id . '-' . $totalViolations,
                        'type' => 'danger',
                        'icon' => 'tabler.exclamation-circle',
                        'title' => 'Pelanggaran Aturan Jadwal: ' . ($sched->ruangan->nama ?? 'Ruangan'),
                        'message' => "Ada {$totalViolations} pelanggaran pada jadwal " . \Carbon\Carbon::create($sched->tahun, $sched->bulan, 1)->translatedFormat('F Y') . ". Contoh: {$sample}",
                        'time' => $sched->updated_at->diffForHumans(),
                        'route' => 'kepegawaian.jadwal-kerja.kelola',
                        'route_params' => ['id' => $sched->id]
                    ];
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $readNotifs = $user->read_notifications ?? [];
        foreach ($items as &$item) {
            $item['is_read'] = in_array($item['id'], $readNotifs);
        }

        $this->notifications = $items;
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        if (!$user) return;

        $readNotifs = $user->read_notifications ?? [];
        if (!in_array($id, $readNotifs)) {
            $readNotifs[] = $id;
            $user->read_notifications = $readNotifs;
            $user->save();
        }

        $this->loadNotifications();
        $this->dispatch('notification-updated');
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        if (!$user) return;

        $readNotifs = $user->read_notifications ?? [];
        foreach ($this->notifications as $notif) {
            if (!in_array($notif['id'], $readNotifs)) {
                $readNotifs[] = $notif['id'];
            }
        }

        $user->read_notifications = $readNotifs;
        $user->save();

        $this->loadNotifications();
        $this->dispatch('notification-updated');
    }

    public function render()
    {
        return view('livewire.profile.notif')
            ->title('Notifikasi');
    }
}
