<?php

namespace App\Livewire\Profile;

use Livewire\Component;

use App\Models\Surat\SuratCuti;
use App\Models\Maintenance\Jadwal;
use App\Models\Surat\SuratSp3;
use App\Enums\StatusApproval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

        $cacheKey = 'notif_user_' . $user->id;
        $this->notifications = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user) {
            return $this->buildNotifications($user);
        });

        // Apply read status (not cached, always fresh)
        $readNotifs = $user->read_notifications ?? [];
        foreach ($this->notifications as &$item) {
            $item['is_read'] = in_array($item['id'], $readNotifs);
        }
    }

    protected function buildNotifications($user): array
    {

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

            // Ambil 10 jadwal aktif (tidak terkunci) terupdate dalam 60 hari terakhir
            $activeSchedules = $query->where('status', '!=', \App\Enums\StatusJadwalKerja::LOCKED)
                ->where('created_at', '>=', now()->subDays(60))
                ->orderBy('updated_at', 'desc')
                ->take(10)
                ->get();

            foreach ($activeSchedules as $sched) {
                /** @var \App\Models\Sdm\JadwalKerja $sched */
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

        // 5. Jadwal Kerja Dipublikasikan / Terkunci (Untuk Karyawan)
        try {
            if ($user->karyawan_id) {
                // Ambil jadwal kerja yang dipublikasikan/terkunci dalam 60 hari terakhir yang diikuti karyawan ini
                $mySchedules = \App\Models\Sdm\JadwalKerja::whereIn('status', [\App\Enums\StatusJadwalKerja::PUBLISHED, \App\Enums\StatusJadwalKerja::LOCKED])
                    ->whereHas('details', function ($query) use ($user) {
                        $query->where('karyawan_id', $user->karyawan_id);
                    })
                    ->where('updated_at', '>=', now()->subDays(60))
                    ->with('ruangan')
                    ->latest('updated_at')
                    ->take(5)
                    ->get();

                foreach ($mySchedules as $sched) {
                    $statusText = $sched->status === \App\Enums\StatusJadwalKerja::PUBLISHED ? 'dipublikasikan' : 'dikunci';
                    $items[] = [
                        'id' => 'jadwal-publish-' . $sched->id . '-' . $sched->status->value,
                        'type' => 'success',
                        'icon' => 'tabler.calendar',
                        'title' => 'Jadwal Kerja ' . ($sched->status === \App\Enums\StatusJadwalKerja::PUBLISHED ? 'Dipublikasikan' : 'Terkunci'),
                        'message' => 'Jadwal kerja Anda untuk periode ' . \Carbon\Carbon::create($sched->tahun, $sched->bulan, 1)->translatedFormat('F Y') . ' di ruangan ' . ($sched->ruangan->nama ?? 'Ruangan') . ' telah ' . $statusText . '.',
                        'time' => $sched->updated_at->diffForHumans(),
                        'route' => 'kepegawaian.jadwal-kerja.index',
                    ];
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return $items;
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

        Cache::forget('notif_user_' . $user->id);
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

        Cache::forget('notif_user_' . $user->id);
        $this->loadNotifications();
        $this->dispatch('notification-updated');
    }

    public function render()
    {
        return view('livewire.profile.notif')
            ->title('Notifikasi');
    }
}
