<?php

namespace App\Models\Sdm;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;
use App\Enums\StatusJadwalKerja;

class JadwalKerja extends Model
{
    use Blameable;
    
    protected $table = 'sdm_jadwal_kerja';
    protected $guarded = [];
    protected $casts = [
        'status' => StatusJadwalKerja::class,
        'diketahui_at' => 'datetime',
        'disetujui_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(JadwalKerjaDetail::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(Karyawan::class, 'dibuat_oleh');
    }

    public function diketahuiOleh()
    {
        return $this->belongsTo(Karyawan::class, 'diketahui_oleh');
    }

    public function disetujuiOleh()
    {
        return $this->belongsTo(Karyawan::class, 'disetujui_oleh');
    }

    public function ruangan()
    {
        return $this->belongsTo(\App\Models\Ruangan::class, 'ruangan_id');
    }

    public function bagian()
    {
        return $this->belongsTo(Bagian::class, 'bagian_id');
    }

    /**
     * Resolve one department for a room-based schedule.
     * A schedule is intentionally limited to one department in the first rollout.
     */
    public static function resolveBagianIdForKaryawanIds(iterable $karyawanIds, ?int $ruanganId = null): ?int
    {
        $ids = collect($karyawanIds)->filter()->unique()->values();
        $bagianIds = Karyawan::with('jabatan')
            ->whereIn('id', $ids)
            ->get()
            ->map(fn (Karyawan $karyawan) => $karyawan->active_bagian_id)
            ->filter()
            ->values();

        if ($bagianIds->isNotEmpty() && $bagianIds->unique()->count() === 1) {
            return (int) $bagianIds->first();
        }

        if ($ruanganId) {
            $ruanganBagianId = \App\Models\Ruangan::whereKey($ruanganId)->value('bagian_id');
            if ($ruanganBagianId) {
                return (int) $ruanganBagianId;
            }
        }

        return $bagianIds->isNotEmpty() ? (int) $bagianIds->first() : null;
    }

    public function approvalLogs()
    {
        return $this->hasMany(JadwalApprovalLog::class, 'jadwal_kerja_id')->latest();
    }

    /**
     * Catat audit trail persetujuan jadwal kerja.
     *
     * @param string      $aksi           Salah satu konstanta JadwalApprovalLog::AKSI_*
     * @param string|null $statusSebelum  Status jadwal sebelum aksi
     * @param string|null $statusSesudah  Status jadwal setelah aksi
     * @param string|null $catatan        Catatan revisi / keterangan
     */
    public function logApproval(string $aksi, ?string $statusSebelum = null, ?string $statusSesudah = null, ?string $catatan = null): JadwalApprovalLog
    {
        $user     = auth()->user();
        $karyawan = $user?->karyawan;

        return $this->approvalLogs()->create([
            'aksi'             => $aksi,
            'user_id'          => $user?->id,
            'karyawan_id'      => $karyawan?->id,
            'status_sebelumnya' => $statusSebelum,
            'status_sesudah'   => $statusSesudah,
        ]);
    }

    public function isDokterSchedule(): bool
    {
        if (!empty($this->tipe)) {
            return $this->tipe === 'dokter';
        }
        $detailsQuery = $this->details();
        if ($detailsQuery->exists()) {
            $karyawanIds = $detailsQuery->pluck('karyawan_id')->unique()->filter();
            if ($karyawanIds->isNotEmpty()) {
                return Dokter::whereIn('karyawan_id', $karyawanIds)->exists();
            }
        }

        return false;
    }

    /**
     * Resolves the target approver Karyawan model or name dynamically based on Workflow & Department
     */
    public function getTargetApproverName(int $stepNumber): string
    {
        $tipeJadwal = $this->isDokterSchedule() ? 'dokter' : 'karyawan';
        $targetTingkatId = WorkflowApproval::getTargetTingkatId($this->ruangan_id, $stepNumber, $tipeJadwal);

        if (!$targetTingkatId) {
            return $stepNumber === 1 ? 'Kepala Dept / Bidang' : 'Wakil Direktur';
        }

        // Legacy fallback is kept until all existing schedules are backfilled.
        $bagianId = $this->bagian_id ?? $this->ruangan?->bagian_id;

        if ($targetTingkatId === 3 && !$bagianId) {
            return 'Kepala Dept / Bidang (jadwal belum memiliki bagian)';
        }

        // Query karyawan yang memegang Jabatan AKTIF (tgl_berakhir IS NULL) dengan tingkat_id yang cocok
        $approver = Karyawan::whereHas('jabatan', function ($q) use ($targetTingkatId, $bagianId) {
            $q->where('tingkat_id', $targetTingkatId)
              ->whereNull('sdm_kary_jabatan.tgl_berakhir'); // hanya jabatan aktif saat ini
            if ($bagianId && $targetTingkatId === 3) {
                $q->where(function ($partQuery) use ($bagianId) {
                    $partQuery->where('sdm_kary_jabatan.bagian_id', $bagianId)
                        ->orWhere(function ($legacyQuery) use ($bagianId) {
                            $legacyQuery->whereNull('sdm_kary_jabatan.bagian_id')
                                ->where('sdm_jabatan.bagian_id', $bagianId);
                        });
                });
            }
        })->orderBy('id')->first();

        if ($approver) {
            return $approver->full_nama;
        }

        // Jika belum ada karyawan yang menjabat Kabid di bagian ini
        if ($targetTingkatId === 3 && $bagianId) {
            $jabatanKabidBagian = Jabatan::where('bagian_id', $bagianId)
                ->where('tingkat_id', 3)
                ->first();

            if ($jabatanKabidBagian) {
                return $jabatanKabidBagian->nama . ' (Belum ada yang menjabat)';
            }

            $namaBagian = Bagian::find($bagianId)?->nama;
            return 'Kepala Bidang ' . ($namaBagian ?? '') . ' (Belum ada yang menjabat)';
        }

        if ($targetTingkatId === 2) {
            try {
                $wadirUser = \App\Models\User::permission('approve-jadwal-wadir')->first()
                    ?? \App\Models\User::role(['Wakil-Direktur', 'Wadir-Medis-Keperawatan', 'Wadir-SDM-Umum'])->first();
                return $wadirUser?->karyawan?->full_nama ?? $wadirUser?->name ?? 'Wakil Direktur';
            } catch (\Throwable $e) {
                return 'Wakil Direktur';
            }
        }

        try {
            $kabidUser = \App\Models\User::permission('approve-jadwal-kabid')->first()
                ?? \App\Models\User::role('Kepala-Bidang')->first();
            return $kabidUser?->karyawan?->full_nama ?? $kabidUser?->name ?? 'Kepala Dept';
        } catch (\Throwable $e) {
            return 'Kepala Dept';
        }
    }

    public static function ensureEmployeeDetailsExist($karyawanId, $bulan, $tahun)
    {
        $karyawan = \App\Models\Sdm\Karyawan::find($karyawanId);
        if (!$karyawan) {
            return;
        }

        $isDokter = $karyawan->dokterRecord()->exists();
        $tipe = $isDokter ? 'dokter' : 'karyawan';

        $ruanganId = $karyawan->ruangan_id;
        if (!$ruanganId) {
            $defaultRuangan = \App\Models\Ruangan::firstOrCreate(
                ['nama' => 'Kantor Manajemen (SDM & Keuangan)'],
                ['is_active' => true]
            );
            $ruanganId = $defaultRuangan->id;
            $karyawan->update(['ruangan_id' => $ruanganId]);
        }

        $isReguler = $karyawan->kategori_kerja === \App\Enums\KategoriKerja::REGULER;

        $hasTipe = \Illuminate\Support\Facades\Schema::hasColumn('sdm_jadwal_kerja', 'tipe');

        $query = self::where('ruangan_id', $ruanganId)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun);
        if ($hasTipe) {
            $query->where('tipe', $tipe);
        }
        $jadwalKerja = $query->first();

        if (!$jadwalKerja) {
            try {
                $payload = [
                    'ruangan_id'  => $ruanganId,
                    'bagian_id'   => self::resolveBagianIdForKaryawanIds([$karyawanId], $ruanganId),
                    'bulan'       => $bulan,
                    'tahun'       => $tahun,
                    'tipe'        => $tipe,
                    'status'      => $isReguler ? StatusJadwalKerja::PUBLISHED : StatusJadwalKerja::DRAFT,
                    'dibuat_oleh' => 1,
                ];
                if ($hasTipe) {
                    $payload['tipe'] = $tipe;
                }
                $jadwalKerja = self::create($payload);
            } catch (\Throwable $e) {
                $fallbackQuery = self::where('ruangan_id', $ruanganId)
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun);
                if ($hasTipe) {
                    $fallbackQuery->where('tipe', $tipe);
                }
                $jadwalKerja = $fallbackQuery->first();
                if (!$jadwalKerja) {
                    return;
                }
            }
        } else {
            $updates = [];
            if (!$jadwalKerja->bagian_id) {
                $resolvedBagianId = self::resolveBagianIdForKaryawanIds([$karyawanId], $ruanganId);
                if ($resolvedBagianId) {
                    $updates['bagian_id'] = $resolvedBagianId;
                }
            }
            if ($isReguler && $jadwalKerja->status === StatusJadwalKerja::DRAFT) {
                $updates['status'] = StatusJadwalKerja::PUBLISHED;
            }
            if ($updates) {
                $jadwalKerja->update($updates);
            }
        }

        $hasDetails = \App\Models\Sdm\JadwalKerjaDetail::where('jadwal_kerja_id', $jadwalKerja->id)
            ->where('karyawan_id', $karyawanId)
            ->exists();

        if ($hasDetails) {
            $shiftReguler = app(\App\Services\AturanJadwalService::class)
                ->shiftValidUntukRuangan($ruanganId, $jadwalKerja->bagian_id)
                ->first(fn ($ruanganShift) => $ruanganShift->shift?->kode === 'REGULER')
                ?->shift
                ?? \App\Models\Sdm\JadwalShift::where('kode', 'REGULER')->first();

            if ($isReguler && $shiftReguler) {
                $details = \App\Models\Sdm\JadwalKerjaDetail::where('jadwal_kerja_id', $jadwalKerja->id)
                    ->where('karyawan_id', $karyawanId)
                    ->where('status_kehadiran', 'belum_dicek')
                    ->get();

                foreach ($details as $detail) {
                    $date = \Carbon\Carbon::parse($detail->tanggal);
                    if ($date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 5) {
                        $detail->update(['shift_id' => $shiftReguler->id]);
                    } else {
                        $detail->update(['shift_id' => null]);
                    }
                }
            }
            return;
        }

        $shiftReguler = app(\App\Services\AturanJadwalService::class)
            ->shiftValidUntukRuangan($ruanganId, $jadwalKerja->bagian_id)
            ->first(fn ($ruanganShift) => $ruanganShift->shift?->kode === 'REGULER')
            ?->shift;
        $daysInMonth = \Carbon\Carbon::create($tahun, $bulan, 1)->daysInMonth;
        
        $startDate = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $endDate = \Carbon\Carbon::create($tahun, $bulan, $daysInMonth)->format('Y-m-d');

        $approvedCutis = \App\Models\Surat\SuratCuti::where('karyawan_id', $karyawanId)
            ->where('status', 'approved')
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
                    $cutiMap[$d] = [
                        'status' => (int)$sc->urgensi_id === 4 ? \App\Enums\StatusKehadiran::IZIN : \App\Enums\StatusKehadiran::CUTI,
                        'catatan' => $sc->jenis?->nama . ' resmi (' . $sc->no_surat . ')'
                    ];
                }
            }
        }

        $newDetails = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = \Carbon\Carbon::create($tahun, $bulan, $d);
            $dateStr = $date->format('Y-m-d');

            $shiftId = null;
            if ($karyawan->kategori_kerja === \App\Enums\KategoriKerja::REGULER && $shiftReguler) {
                if ($date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 5) {
                    $shiftId = $shiftReguler->id;
                }
            }

            $statusKehadiran = 'belum_dicek';
            $catatan = null;
            $actualShiftId = $shiftId;

            if (isset($cutiMap[$dateStr])) {
                $statusKehadiran = $cutiMap[$dateStr]['status']->value;
                $catatan = $cutiMap[$dateStr]['catatan'];
                $actualShiftId = null;
            }

            $newDetails[] = [
                'jadwal_kerja_id' => $jadwalKerja->id,
                'karyawan_id' => $karyawanId,
                'shift_id' => $actualShiftId,
                'tanggal' => $dateStr,
                'status_kehadiran' => $statusKehadiran,
                'catatan' => $catatan,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        try {
            \App\Models\Sdm\JadwalKerjaDetail::insert($newDetails);
        } catch (\Throwable $e) {
            // Safe to ignore duplicate records on concurrent request
        }
    }

    public static function syncKaryawanRegulerSchedule(int $karyawanId, ?int $bulan = null, ?int $tahun = null): void
    {
        $karyawan = Karyawan::find($karyawanId);
        if (!$karyawan) {
            return;
        }

        $kategori = $karyawan->kategori_kerja instanceof \App\Enums\KategoriKerja
            ? $karyawan->kategori_kerja
            : \App\Enums\KategoriKerja::tryFrom($karyawan->kategori_kerja);

        if ($kategori !== \App\Enums\KategoriKerja::REGULER) {
            return;
        }

        $bulan = $bulan ?? (int) now()->format('n');
        $tahun = $tahun ?? (int) now()->format('Y');

        // Ensure schedule & details exist for current month
        self::ensureEmployeeDetailsExist($karyawanId, $bulan, $tahun);

        // Find REGULER shift
        $ruanganId = $karyawan->ruangan_id;
        $shiftReguler = null;
        if ($ruanganId) {
            $shiftReguler = app(\App\Services\AturanJadwalService::class)
                ->shiftValidUntukRuangan($ruanganId, null)
                ->first(fn ($ruanganShift) => $ruanganShift->shift?->kode === 'REGULER')
                ?->shift;
        }
        if (!$shiftReguler) {
            $shiftReguler = JadwalShift::where('kode', 'REGULER')->where('aktif', true)->first();
        }
        if (!$shiftReguler) {
            $shiftReguler = JadwalShift::where('kode', 'REGULER')->first();
        }

        if (!$shiftReguler) {
            return;
        }

        // Get details for current month onwards that are not yet checked
        $details = JadwalKerjaDetail::where('karyawan_id', $karyawanId)
            ->where(function($q) use ($tahun, $bulan) {
                $q->whereYear('tanggal', '>', $tahun)
                  ->orWhere(function($sub) use ($tahun, $bulan) {
                      $sub->whereYear('tanggal', '=', $tahun)
                          ->whereMonth('tanggal', '>=', $bulan);
                  });
            })
            ->where('status_kehadiran', 'belum_dicek')
            ->get();

        foreach ($details as $detail) {
            $date = \Carbon\Carbon::parse($detail->tanggal);
            if ($date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 5) {
                $detail->update(['shift_id' => $shiftReguler->id]);
            } else {
                $detail->update(['shift_id' => null]);
            }
        }
    }
}
