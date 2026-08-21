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
            if ($user->can('approve-kepegawaian-cuti')) {
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
            if ($user->can('view-umum-maintenance')) {
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

            $ruanganIds = $user->getRuanganKoordinatorIds();
            if ($ruanganIds !== null) {
                // Jika koordinator ruangan biasa, hanya ambil ruangan yang dikoordinasikan
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
                        'icon' => 'tabler.alert-triangle',
                        'title' => 'Peringatan Jadwal: ' . ($sched->ruangan->nama ?? 'Ruangan'),
                        'message' => "Ditemukan {$totalViolations} potensi pelanggaran aturan jadwal. Contoh: \"{$sample}\"",
                        'time' => $sched->updated_at->diffForHumans(),
                        'route' => 'kepegawaian.jadwal-kerja.kelola',
                        'route_params' => ['id' => $sched->id],
                    ];
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        // 5. Digital Signature Notifications (Pengajuan Ditolak & Menunggu Approval)
        try {
            // A. Dokumen milik user yang Ditolak
            $rejectedDocs = \App\Models\DigitalSignatureDocument::with(['approvals.user'])
                ->where('user_id', $user->id)
                ->where('status', 'rejected')
                ->latest()
                ->take(5)
                ->get();

            foreach ($rejectedDocs as $doc) {
                $rejectedAppr = $doc->approvals->where('status', 'rejected')->first();
                $penolakName = $rejectedAppr?->user?->name ?? 'Penandatangan';
                $reason = $rejectedAppr?->rejection_reason ?? 'Tidak ada catatan feedback';

                $items[] = [
                    'id' => 'ds-rejected-' . $doc->id,
                    'type' => 'danger',
                    'icon' => 'tabler.file-x',
                    'title' => 'Pengajuan TTD Ditolak: ' . $doc->title,
                    'message' => "Ditolak oleh {$penolakName}. Catatan: \"{$reason}\"",
                    'time' => $doc->updated_at->diffForHumans(),
                    'route' => 'kepegawaian.digital-signature.index',
                ];
            }

            // B. Dokumen bertingkat yang Menunggu Persetujuan User Ini
            $pendingApprovals = \App\Models\DigitalSignatureApproval::with(['document.user'])
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get();

            foreach ($pendingApprovals as $appr) {
                $doc = $appr->document;
                if ($doc && $doc->isPendingForUser($user->id)) {
                    $senderName = $doc->user?->name ?? 'Pengirim';
                    $items[] = [
                        'id' => 'ds-pending-' . $doc->id,
                        'type' => 'warning',
                        'icon' => 'tabler.file-pencil',
                        'title' => 'Permohonan TTD Bertingkat: ' . $doc->title,
                        'message' => "Diajukan oleh {$senderName}. Membutuhkan persetujuan & TTD Anda (Tier {$appr->step_order}).",
                        'time' => $appr->created_at->diffForHumans(),
                        'route' => 'kepegawaian.digital-signature.index',
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

        // 6. Golongan Matrix & Tunjangan Changes (For SDM & Management)
        try {
            if ($user->can('view-kepegawaian-gaji')) {
                $logs = \Illuminate\Support\Facades\DB::table('sdm_payroll_golongan_logs')
                    ->join('users', 'sdm_payroll_golongan_logs.user_id', '=', 'users.id')
                    ->join('sdm_karyawan', 'users.karyawan_id', '=', 'sdm_karyawan.id')
                    ->select('sdm_payroll_golongan_logs.*', 'sdm_karyawan.nama as nama_user')
                    ->latest('sdm_payroll_golongan_logs.created_at')
                    ->take(5)
                    ->get();

                foreach ($logs as $log) {
                    $items[] = [
                        'id' => 'golongan-change-' . $log->id,
                        'type' => 'danger',
                        'icon' => 'tabler.alert-triangle',
                        'title' => 'Perubahan Golongan/Matrix',
                        'message' => "Golongan/Matrix diubah oleh {$log->nama_user}. [{$log->tipe}] {$log->kunci}: {$log->nilai_lama} -> {$log->nilai_baru}",
                        'time' => \Carbon\Carbon::parse($log->created_at)->diffForHumans(),
                        'route' => 'kepegawaian.master.tunjangan-golongan.index',
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
