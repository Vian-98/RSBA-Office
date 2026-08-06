<?php

namespace App\Livewire\Kepegawaian\JadwalKerja;

use App\Enums\StatusJadwalKerja;
use App\Models\Sdm\JadwalApprovalLog;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Services\AturanJadwalService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Title;
use Throwable;
use TallStackUi\Traits\Interactions;

#[Title('Kelola Jadwal Kerja')]
class Kelola extends Component
{
    use Interactions;

    public JadwalKerja $jadwalKerja;
    public $state = []; // state[detail_id] = shift_id
    public $karyawans = [];
    public $dates = [];
    public $shiftOptions = [];
    public $isReadOnly = false;
    public $cutiDates = [];
    public $catatanRevisiInput = '';
    public $showRevisiModal = false;

    public function mount($id, AturanJadwalService $service)
    {
        $this->jadwalKerja = JadwalKerja::with([
            'ruangan',
            'details.karyawan',
            'details.shift',
            'diketahuiOleh',
            'disetujuiOleh'
        ])->findOrFail($id);

        $karyawanIds = $this->jadwalKerja->details->pluck('karyawan_id')->unique()->toArray();
        $approvedCutis = \App\Models\Surat\SuratCuti::whereIn('karyawan_id', $karyawanIds)
            ->where('status', 'approved')
            ->get();

        foreach ($approvedCutis as $sc) {
            $dates = json_decode($sc->tgl_cuti, true);
            if (is_array($dates)) {
                foreach ($dates as $d) {
                    $this->cutiDates["{$sc->karyawan_id}-{$d}"] = $sc->no_surat;
                }
            }
        }

        $user = Auth::user();
        $canView = false;
        $canManage = false;

        if ($user) {
<<<<<<< HEAD
            $isRestrictedGuest = $user->hasRole('Guest') && !$user->isKoordinator();

            if ($isRestrictedGuest) {
                $canView = in_array(
                    (int) $this->jadwalKerja->ruangan_id,
                    $user->getOwnRuanganIds(),
                    true
                ) && in_array($this->jadwalKerja->status, [
                    StatusJadwalKerja::PUBLISHED,
                    StatusJadwalKerja::LOCKED,
                ], true)
                && $this->jadwalKerja->tipe === ($user->isDokter() ? 'dokter' : 'karyawan');
            }

            $isGlobalApprover = $user->hasRole([
                'Super-Admin', 'Staff-SDM', 'Wakil-Direktur',
                'Wadir-Medis-Keperawatan', 'Wadir-SDM-Umum', 'Wadir-Keuangan', 'Direktur'
            ]) || $user->can('approve-jadwal-wadir');

            if (!$isRestrictedGuest && $isGlobalApprover) {
=======
            $isApprover = $user->hasRole([
                'Super-Admin', 'Staff-SDM', 'Wakil-Direktur', 'Kepala-Bidang',
                'Wadir-Medis-Keperawatan', 'Wadir-SDM-Umum', 'Wadir-Keuangan', 'Direktur'
            ]) || $user->can('approve-jadwal-kabid') || $user->can('approve-jadwal-wadir') || $user->can('view-kepegawaian-jadwal-kerja');

            if ($isApprover) {
>>>>>>> origin/kepegawaian/penggajian
                $canView = true;
                if ($user->hasRole(['Super-Admin', 'Staff-SDM'])) {
                    $canManage = true;
                }
            } elseif (!$isRestrictedGuest && ($user->hasRole('Kepala-Bidang') || $user->can('approve-jadwal-kabid'))) {
                $bagianIds = $user->getActiveBagianIds();
                $legacyBagianRuanganIds = $user->getBagianScopedRuanganIds() ?? [];
                $koorIds = $user->getRuanganKoordinatorIds() ?? [];
                $jadwalBagianId = $this->jadwalKerja->bagian_id
                    ?? $this->jadwalKerja->ruangan?->bagian_id;
                $hasDepartmentAccess = $jadwalBagianId
                    && in_array((int) $jadwalBagianId, $bagianIds, true);
                $hasLegacyRoomAccess = !$this->jadwalKerja->bagian_id
                    && in_array($this->jadwalKerja->ruangan_id, $legacyBagianRuanganIds);
                $hasCoordinatorRoomAccess = in_array($this->jadwalKerja->ruangan_id, (array) $koorIds);

                if ($hasDepartmentAccess || $hasLegacyRoomAccess || $hasCoordinatorRoomAccess) {
                    $canView = true;
                    $canManage = true;
                }
            }

            $ownRuanganId = $user->karyawan?->ruangan_id;
            $koorIds = $user->isKoordinator() ? $user->getRuanganKoordinatorIds() : [];

            if ($koorIds === null) {
                // Access all rooms for Super-Admin / SDM / Wadir
                if ($user->hasRole(['Super-Admin', 'Staff-SDM'])) {
                    $canView = true;
                    $canManage = true;
                }
            } else if (in_array($this->jadwalKerja->ruangan_id, $koorIds)) {
                $canView = true;
                $canManage = true;
            }

            if (!$isRestrictedGuest && $ownRuanganId && $this->jadwalKerja->ruangan_id === $ownRuanganId) {
                $canView = true;
                if ($user->isKoordinator() || $user->hasRole('Kepala-Bidang')) {
                    $canManage = true;
                }
            }

            // Validasi tipe jadwal: Koor Dokter hanya boleh buka tipe=dokter, Koor Karyawan hanya tipe=karyawan
            if ($user->isKoordinatorDokter() && $this->jadwalKerja->tipe !== 'dokter') {
                abort(403, 'Anda adalah Koordinator Dokter, jadwal ini adalah Jadwal Karyawan.');
            }
            if ($user->isKoordinatorKaryawan() && $this->jadwalKerja->tipe === 'dokter') {
                abort(403, 'Jadwal Dokter tidak dapat dikelola oleh Koordinator Karyawan.');
            }

            $ownRuanganId = $user->karyawan?->ruangan_id;
            $ruanganIds = $user->isKoordinator() ? ($user->getRuanganKoordinatorIds() ?? []) : [];

            if ($this->jadwalKerja->ruangan_id === $ownRuanganId || in_array($this->jadwalKerja->ruangan_id, $ruanganIds)) {
                $canView = true;
            }

            if (in_array($this->jadwalKerja->ruangan_id, $ruanganIds)) {
                $canManage = true;
            }

            // Validasi tipe jadwal: Koor Dokter hanya boleh buka tipe=dokter, Koor Karyawan hanya tipe=karyawan
            if ($user->isKoordinatorDokter() && $this->jadwalKerja->tipe !== 'dokter') {
                abort(403, 'Anda adalah Koordinator Dokter, jadwal ini adalah Jadwal Karyawan.');
            }
            if ($user->isKoordinatorKaryawan() && $this->jadwalKerja->tipe === 'dokter') {
                abort(403, 'Jadwal Dokter tidak dapat dikelola oleh Koordinator Karyawan.');
            }
        }

        abort_unless($canView, 403, 'Anda tidak memiliki akses ke jadwal ruangan ini.');

        // Populate valid shifts using service (includes jam override)
        $validShifts = $service->shiftValidUntukRuangan(
            $this->jadwalKerja->ruangan_id,
            $this->jadwalKerja->bagian_id
        );
        $this->shiftOptions = $validShifts->map(function ($rs) {
            $shift = $rs->shift;
            return [
                'id' => $shift->id,
                'kode' => $shift->kode,
                'nama' => $shift->nama,
                'warna' => $shift->warna ?? '#e2e8f0',
                'jam_masuk' => $rs->jam_masuk_efektif,
                'jam_keluar' => $rs->jam_keluar_efektif,
            ];
        })->toArray();

        // Read-only mode is active if status is in approval process (menunggu_kabid / menunggu_wadir) or locked, unless user is Super-Admin
        $isEditableStatus = in_array($this->jadwalKerja->status, [
            StatusJadwalKerja::DRAFT,
            StatusJadwalKerja::DITOLAK,
            StatusJadwalKerja::PUBLISHED,
        ]);
        $this->isReadOnly = (!$isEditableStatus && !($user && $user->hasRole('Super-Admin'))) || !$canManage;

        // Populate dates for header
        $daysInMonth = Carbon::create($this->jadwalKerja->tahun, $this->jadwalKerja->bulan, 1)->daysInMonth;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $this->dates[] = Carbon::create($this->jadwalKerja->tahun, $this->jadwalKerja->bulan, $d);
        }

        $user = Auth::user();
        $isKoorDokter = $user?->isKoordinatorDokter() ?? false;
        $isKoorKaryawan = $user?->isKoordinatorKaryawan() ?? false;

<<<<<<< HEAD
        // Sinkronisasi detail adalah operasi tulis. User Guest/read-only tidak
        // boleh mengubah atau menghapus detail hanya karena membuka halaman.
        if ($canManage) {
            $this->syncDetails($daysInMonth, $isKoorDokter, $isKoorKaryawan);
        }
=======
        $this->syncDetails($daysInMonth, $isKoorDokter, $isKoorKaryawan);
>>>>>>> origin/kepegawaian/penggajian

        // Group details by Karyawan
        $grouped = $this->jadwalKerja->details->groupBy('karyawan_id');

        foreach ($grouped as $karyawanId => $details) {
            $karyawan = $details->first()->karyawan;
            if (!$karyawan) continue;

            $isDokter = $karyawan->dokterRecord()->exists();

            if ($isKoorDokter && !$isDokter) {
                continue; // Koordinator Dokter hanya melihat Dokter
            }
            if ($isKoorKaryawan && $isDokter) {
                continue; // Koordinator Karyawan (Karu) hanya melihat Non-Dokter
            }

            $row = [
                'id' => $karyawan->id,
                'nama' => $karyawan->full_nama,
                'is_dokter' => $isDokter,
                'kategori' => $karyawan->kategori_kerja->nama(),
                'details' => []
            ];

            foreach ($details as $detail) {
                $day = $detail->tanggal->day;
                $row['details'][$day] = $detail;

                // Init state
                $this->state[$detail->id] = $detail->shift_id;
            }

            $this->karyawans[] = $row;
        }
    }

    private function syncDetails($daysInMonth, bool $isKoorDokter = false, bool $isKoorKaryawan = false)
    {
        $karyawansQuery = \App\Models\Sdm\Karyawan::where('ruangan_id', $this->jadwalKerja->ruangan_id)
            ->whereNull('resign_at');
<<<<<<< HEAD
=======

        // Filter berdasarkan TIPE JADWAL (bukan role user) untuk memastikan pemisahan permanen
        if ($this->jadwalKerja->tipe === 'dokter') {
            $karyawansQuery->whereHas('dokterRecord');
        } else {
            $karyawansQuery->whereDoesntHave('dokterRecord');
        }

        $karyawansInRoom = $karyawansQuery->get();
>>>>>>> origin/kepegawaian/penggajian

        // Filter berdasarkan TIPE JADWAL (bukan role user) untuk memastikan pemisahan permanen
        if ($this->jadwalKerja->tipe === 'dokter') {
            $karyawansQuery->whereHas('dokterRecord');
        } else {
            $karyawansQuery->whereDoesntHave('dokterRecord');
        }

        $karyawansInRoom = $karyawansQuery->get();
        $validKaryawanIds = $karyawansInRoom->pluck('id')->toArray();

        // Hapus detail lama untuk karyawan yang sudah tidak berada di ruangan ini
        JadwalKerjaDetail::where('jadwal_kerja_id', $this->jadwalKerja->id)
            ->whereNotIn('karyawan_id', $validKaryawanIds)
            ->delete();

        $existingDetails = JadwalKerjaDetail::where('jadwal_kerja_id', $this->jadwalKerja->id)->get();

        $existingMap = [];
        foreach ($existingDetails as $detail) {
            $dateStr = $detail->tanggal->format('Y-m-d');
            $existingMap[$detail->karyawan_id][$dateStr] = true;
        }

        $startDate = $this->dates[0]->format('Y-m-d');
        $endDate = $this->dates[$daysInMonth - 1]->format('Y-m-d');

        $approvedCutis = \App\Models\Surat\SuratCuti::where('status', 'approved')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('tgl_mulai', [$startDate, $endDate])
                  ->orWhereBetween('tgl_akhir', [$startDate, $endDate])
                  ->orWhere(function($sub) use ($startDate, $endDate) {
                      $sub->where('tgl_mulai', '<=', $startDate)
                          ->where('tgl_akhir', '>=', $endDate);
                      });
            })
            ->get();

        $cutiMap = [];
        foreach ($approvedCutis as $sc) {
            $dates = json_decode($sc->tgl_cuti, true);
            if (is_array($dates)) {
                foreach ($dates as $d) {
                    $cutiMap[$sc->karyawan_id][$d] = [
                        'status' => (int)$sc->urgensi_id === 4 ? \App\Enums\StatusKehadiran::IZIN : \App\Enums\StatusKehadiran::CUTI,
                        'catatan' => $sc->jenis?->nama . ' resmi (' . $sc->no_surat . ')'
                    ];
                }
            }
        }

        $detailsToInsert = [];

        foreach ($karyawansInRoom as $karyawan) {
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = $this->dates[$d - 1]->format('Y-m-d');
                $exists = isset($existingMap[$karyawan->id][$dateStr]);

                if (!$exists) {
                    $statusKehadiran = 'belum_dicek';
                    $catatan = null;

                    if (isset($cutiMap[$karyawan->id][$dateStr])) {
                        $statusKehadiran = $cutiMap[$karyawan->id][$dateStr]['status']->value;
                        $catatan = $cutiMap[$karyawan->id][$dateStr]['catatan'];
                    }

                    $detailsToInsert[] = [
                        'jadwal_kerja_id' => $this->jadwalKerja->id,
                        'karyawan_id' => $karyawan->id,
                        'shift_id' => null,
                        'tanggal' => $dateStr,
                        'status_kehadiran' => $statusKehadiran,
                        'catatan' => $catatan,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (!empty($detailsToInsert)) {
            foreach (array_chunk($detailsToInsert, 500) as $chunk) {
                JadwalKerjaDetail::insert($chunk);
            }
            $this->jadwalKerja->load(['details.karyawan', 'details.shift']);
        }
    }

    public function save()
    {
        $this->authorize('kelola', $this->jadwalKerja);

        if ($this->isReadOnly) {
            $this->toast()->error('Gagal', 'Jadwal kerja ini dalam status terlindungi / read-only.')->send();
            return;
        }

        $allowedShiftIds = collect($this->shiftOptions)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($this->state as $shiftId) {
            if ($shiftId !== null && $shiftId !== '' && !in_array((int) $shiftId, $allowedShiftIds, true)) {
                $this->toast()->error(
                    'Gagal',
                    'Shift yang dipilih tidak berlaku untuk Bagian pada jadwal ini.'
                )->send();
                return;
            }
        }

        try {
            DB::beginTransaction();
            $logCount = 0;

            foreach ($this->state as $detailId => $shiftId) {
                $detail = JadwalKerjaDetail::find($detailId);

                $dateStr = $detail->tanggal->format('Y-m-d');
                $isCuti = isset($this->cutiDates["{$detail->karyawan_id}-{$dateStr}"]);
                if ($isCuti) {
                    $shiftId = null;
                } else {
                    $shiftId = empty($shiftId) ? null : (int) $shiftId;
                }

                if ($detail->shift_id !== $shiftId) {
                    \App\Models\Sdm\JadwalKerjaLog::create([
                        'jadwal_kerja_id' => $this->jadwalKerja->id,
                        'detail_id'       => $detail->id,
                        'karyawan_id'     => $detail->karyawan_id,
                        'shift_lama_id'   => $detail->shift_id,
                        'shift_baru_id'   => $shiftId,
                        'diubah_oleh'     => Auth::user()->karyawan_id ?? 1,
                    ]);

                    $detail->update([
                        'shift_id' => $shiftId
                    ]);

                    $logCount++;
                }
            }

            $wasPublished = ($this->jadwalKerja->status === StatusJadwalKerja::PUBLISHED);

            if ($wasPublished && $logCount > 0) {
                $statusSebelum = $this->jadwalKerja->status->value;
                $targetStatus = $this->jadwalKerja->isDokterSchedule() 
                    ? StatusJadwalKerja::MENUNGGU_WADIR 
                    : StatusJadwalKerja::MENUNGGU_KABID;

                $this->jadwalKerja->update([
                    'status' => $targetStatus,
                    'diketahui_oleh' => null,
                    'diketahui_at' => null,
                    'disetujui_oleh' => null,
                    'disetujui_at' => null,
                    'catatan_revisi' => null,
                ]);

                // Catat log audit edit pasca publish
                $this->jadwalKerja->logApproval(
                    JadwalApprovalLog::AKSI_EDIT_PASCA_PUBLISH,
                    $statusSebelum,
                    $targetStatus->value,
                    "$logCount perubahan shift dicatat pasca publish."
                );
            }

            DB::commit();

            if ($wasPublished && $logCount > 0) {
                $statusMsg = $this->jadwalKerja->isDokterSchedule() 
                    ? 'Menunggu Disetujui Wadir' 
                    : 'Menunggu Diketahui Kabid';
                $this->toast()->success('Berhasil', "Jadwal kerja berhasil diperbarui ($logCount perubahan). Status dikembalikan ke '$statusMsg' untuk dikonfirmasi ulang.")->send();
                return redirect()->route('kepegawaian.jadwal-kerja.index');
            } elseif ($logCount > 0) {
                $this->toast()->success('Berhasil', "Jadwal kerja berhasil disimpan. $logCount perubahan dicatat.")->send();
            } else {
                $this->toast()->info('Tidak Ada Perubahan', 'Jadwal kerja disimpan tanpa ada perubahan.')->send();
            }

        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast()->error('Gagal', 'Terjadi kesalahan: ' . $th->getMessage())->send();
        }
    }

    public function ajukanKeKabid(AturanJadwalService $service)
    {
        $this->authorize('ajukanKabid', $this->jadwalKerja);

        if (!$this->isReadOnly) {
            $this->save();
        }

        // Validasi Aturan Jadwal
        $violations = $service->validasiJadwal($this->jadwalKerja->fresh(['details.shift', 'details.karyawan']));
        if (!empty($violations)) {
            $pesanError = implode(" | ", array_slice($violations, 0, 3));
            if (count($violations) > 3) {
                $pesanError .= " (dan " . (count($violations) - 3) . " pelanggaran lainnya)";
            }
            $this->toast()->error('Pengajuan Ditolak - Melanggar Aturan Jadwal', $pesanError)->send();
            return;
        }

        $statusSebelum = $this->jadwalKerja->status->value;
        $this->jadwalKerja->update([
            'status' => StatusJadwalKerja::MENUNGGU_KABID,
            'catatan_revisi' => null,
        ]);

        // Catat log audit pengajuan ke Kabid
        $this->jadwalKerja->logApproval(
            JadwalApprovalLog::AKSI_AJUKAN_KABID,
            $statusSebelum,
            StatusJadwalKerja::MENUNGGU_KABID->value
        );

        $this->toast()->success('Berhasil', 'Jadwal kerja berhasil diajukan ke Kepala Bidang!')->send();
        return redirect()->route('kepegawaian.jadwal-kerja.index');
    }

    public function ajukanKeWadirLangsung(AturanJadwalService $service)
    {
        $this->authorize('ajukanWadirLangsung', $this->jadwalKerja);

        if (!$this->isReadOnly) {
            $this->save();
        }

        // Validasi Aturan Jadwal
        $violations = $service->validasiJadwal($this->jadwalKerja->fresh(['details.shift', 'details.karyawan']));
        if (!empty($violations)) {
            $pesanError = implode(" | ", array_slice($violations, 0, 3));
            if (count($violations) > 3) {
                $pesanError .= " (dan " . (count($violations) - 3) . " pelanggaran lainnya)";
            }
            $this->toast()->error('Pengajuan Ditolak - Melanggar Aturan Jadwal', $pesanError)->send();
            return;
        }

        $statusSebelum = $this->jadwalKerja->status->value;
        $this->jadwalKerja->update([
            'status' => StatusJadwalKerja::MENUNGGU_WADIR,
            'catatan_revisi' => null,
        ]);

        // Catat log audit pengajuan langsung ke Wadir
        $this->jadwalKerja->logApproval(
            JadwalApprovalLog::AKSI_AJUKAN_WADIR,
            $statusSebelum,
            StatusJadwalKerja::MENUNGGU_WADIR->value
        );

        $this->toast()->success('Berhasil', 'Jadwal Dokter berhasil diajukan langsung ke Wadir!')->send();
        return redirect()->route('kepegawaian.jadwal-kerja.index');
    }

    public function konfirmasiKabid()
    {
        $this->authorize('konfirmasiKabid', $this->jadwalKerja);

        $karyawanId = Auth::user()->karyawan_id ?? Auth::user()->karyawan?->id;

        $statusSebelum = $this->jadwalKerja->status->value;
        $this->jadwalKerja->update([
            'status' => StatusJadwalKerja::MENUNGGU_WADIR,
            'diketahui_oleh' => $karyawanId,
            'diketahui_at' => now(),
        ]);

        // Catat log audit konfirmasi Kabid
        $this->jadwalKerja->logApproval(
            JadwalApprovalLog::AKSI_DIKETAHUI_KABID,
            $statusSebelum,
            StatusJadwalKerja::MENUNGGU_WADIR->value
        );

        $this->toast()->success('Berhasil', 'Jadwal kerja dikonfirmasi Diketahui Kabid & diteruskan ke Wadir!')->send();
        return redirect()->route('kepegawaian.jadwal-kerja.index');
    }

    public function setujuiWadir()
    {
        $this->authorize('setujuiWadir', $this->jadwalKerja);

        $karyawanId = Auth::user()->karyawan_id ?? Auth::user()->karyawan?->id;

        $statusSebelum = $this->jadwalKerja->status->value;
        $this->jadwalKerja->update([
            'status' => StatusJadwalKerja::PUBLISHED,
            'disetujui_oleh' => $karyawanId,
            'disetujui_at' => now(),
            'published_at' => now(),
        ]);

        // Catat log audit persetujuan Wadir & publish
        $this->jadwalKerja->logApproval(
            JadwalApprovalLog::AKSI_DISETUJUI_WADIR,
            $statusSebelum,
            StatusJadwalKerja::PUBLISHED->value
        );

        $this->toast()->success('Berhasil', 'Jadwal kerja disetujui & resmi dipublikasikan!')->send();
        return redirect()->route('kepegawaian.jadwal-kerja.index');
    }

    public function openRevisiModal()
    {
        $this->authorize('kembalikanDraft', $this->jadwalKerja);
        $this->catatanRevisiInput = '';
        $this->showRevisiModal = true;
    }

    public function confirmKembalikanDraft()
    {
        $this->authorize('kembalikanDraft', $this->jadwalKerja);
        $this->validate([
            'catatanRevisiInput' => 'required|string|min:3|max:500'
        ], [
            'catatanRevisiInput.required' => 'Catatan revisi wajib diisi saat mengembalikan jadwal.',
            'catatanRevisiInput.min' => 'Catatan revisi minimal 3 karakter.'
        ]);

        $statusSebelum = $this->jadwalKerja->status->value;
        $this->jadwalKerja->update([
            'status' => StatusJadwalKerja::DITOLAK,
            'catatan_revisi' => $this->catatanRevisiInput,
            'diketahui_oleh' => null,
            'diketahui_at' => null,
            'disetujui_oleh' => null,
            'disetujui_at' => null,
        ]);

        // Catat log audit revisi/pengembalian ke draft
        $this->jadwalKerja->logApproval(
            JadwalApprovalLog::AKSI_REVISI_DRAFT,
            $statusSebelum,
            StatusJadwalKerja::DITOLAK->value,
            $this->catatanRevisiInput
        );

        $this->showRevisiModal = false;
        $this->toast()->warning('Dikembalikan', 'Jadwal kerja telah dikembalikan ke Draf dengan catatan revisi.')->send();
        return redirect()->route('kepegawaian.jadwal-kerja.index');
    }

    public function render()
    {
        return view('livewire.kepegawaian.jadwal-kerja.kelola');
    }
}
