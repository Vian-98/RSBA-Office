<?php

namespace App\Livewire\Kepegawaian\JadwalKerja;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Throwable;
use Livewire\Component;
use App\Models\Sdm\JadwalKerja;
use App\Traits\AuthorizesFromRoute;
use Filament\Tables\Table;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use TallStackUi\Traits\Interactions;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Support\Facades\Auth;

#[Lazy]
#[Title('Jadwal Kerja Pegawai')]
class Index extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use AuthorizesFromRoute;
    use InteractsWithForms, InteractsWithTable;
    use Interactions;

    protected $listeners = ['jadwal-kerja-generated' => '$refresh'];

    public function placeholder()
    {
        return <<<'HTML'
        <div class="animate-pulse space-y-6">
            <div class="flex justify-between items-center bg-white p-4 rounded-xl border border-slate-200">
                <div class="h-8 bg-slate-200 rounded w-1/3"></div>
                <div class="h-10 bg-slate-200 rounded w-1/6"></div>
            </div>
            <div class="h-96 bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                <div class="h-10 bg-slate-100 rounded"></div>
                <div class="h-12 bg-slate-50 rounded"></div>
                <div class="h-12 bg-slate-50 rounded"></div>
                <div class="h-12 bg-slate-50 rounded"></div>
                <div class="h-12 bg-slate-50 rounded"></div>
            </div>
        </div>
        HTML;
    }

    public function mount()
    {
        // Membuka halaman jadwal tidak boleh membuat data baru untuk user
        // read-only. Auto-generate hanya dijalankan oleh pihak yang memang
        // memiliki kemampuan generate jadwal.
        if (Auth::user()?->can('generate', JadwalKerja::class)) {
            $this->autoGenerateRegulerSchedules();
        }
    }

    private function isRestrictedGuest($user): bool
    {
        if (!$user) return false;
        if ($user->can('view-kepegawaian-jadwal-kerja') || $user->isKoordinator() || $user->isKepalaDept() || $user->isWadir()) {
            return false;
        }
        return true;
    }

    public function autoGenerateRegulerSchedules()
    {
        $shiftReguler = \App\Models\Sdm\JadwalShift::where('kode', 'REGULER')->where('aktif', true)->first();
        if (!$shiftReguler) {
            return;
        }

        $now = \Carbon\Carbon::now();
        $targetMonths = [
            $now,
            $now->copy()->addMonth()
        ];

        $karyawans = \App\Models\Sdm\Karyawan::whereNull('resign_at')->get();
        $grouped = $karyawans->groupBy('ruangan_id');

        $regulerOnlyRuanganIds = [];
        foreach ($grouped as $ruanganId => $members) {
            if (!$ruanganId) continue;
            
            $allReguler = $members->every(function ($k) {
                return $k->kategori_kerja === \App\Enums\KategoriKerja::REGULER;
            });

            if ($allReguler && !$members->isEmpty()) {
                $regulerOnlyRuanganIds[] = $ruanganId;
            }
        }

        if (empty($regulerOnlyRuanganIds)) {
            return;
        }

        foreach ($targetMonths as $target) {
            $month = $target->month;
            $year = $target->year;

            foreach ($regulerOnlyRuanganIds as $ruanganId) {
                $roomMembers = $grouped[$ruanganId];
                $bagianId = JadwalKerja::resolveBagianIdForKaryawanIds(
                    $roomMembers->pluck('id'),
                    (int) $ruanganId
                );

                // Do not silently create a schedule with an ambiguous department.
                // The user can resolve it through manual Generate Jadwal.
                if (!$bagianId) {
                    continue;
                }

                $shiftRegulerUntukBagian = app(\App\Services\AturanJadwalService::class)
                    ->shiftValidUntukRuangan((int) $ruanganId, (int) $bagianId)
                    ->first(fn ($ruanganShift) => $ruanganShift->shift?->kode === 'REGULER')
                    ?->shift;

                if (!$shiftRegulerUntukBagian) {
                    continue;
                }

                $exists = \App\Models\Sdm\JadwalKerja::where('ruangan_id', $ruanganId)
                    ->where('bulan', $month)
                    ->where('tahun', $year)
                    ->exists();

                if ($exists) {
                    continue;
                }

                \Illuminate\Support\Facades\DB::beginTransaction();
                try {
                    $jadwalKerja = \App\Models\Sdm\JadwalKerja::create([
                        'ruangan_id' => $ruanganId,
                        'bagian_id' => $bagianId,
                        'bulan' => $month,
                        'tahun' => $year,
                        'status' => \App\Enums\StatusJadwalKerja::PUBLISHED,
                        'dibuat_oleh' => auth()->id() ?? 1,
                    ]);

                    $daysInMonth = $target->daysInMonth;
                    $startDate = \Carbon\Carbon::create($year, $month, 1)->format('Y-m-d');
                    $endDate = \Carbon\Carbon::create($year, $month, $daysInMonth)->format('Y-m-d');

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

                    $details = [];
                    foreach ($roomMembers as $karyawan) {
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $date = \Carbon\Carbon::create($year, $month, $d);
                            $dateStr = $date->format('Y-m-d');

                            $shiftId = null;
                            if ($date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 5) {
                                $shiftId = $shiftRegulerUntukBagian->id;
                            }

                            $statusKehadiran = 'belum_dicek';
                            $catatan = null;
                            $actualShiftId = $shiftId;

                            if (isset($cutiMap[$karyawan->id][$dateStr])) {
                                $statusKehadiran = $cutiMap[$karyawan->id][$dateStr]['status']->value;
                                $catatan = $cutiMap[$karyawan->id][$dateStr]['catatan'];
                                $actualShiftId = null;
                            }

                            $details[] = [
                                'jadwal_kerja_id' => $jadwalKerja->id,
                                'karyawan_id' => $karyawan->id,
                                'shift_id' => $actualShiftId,
                                'tanggal' => $dateStr,
                                'status_kehadiran' => $statusKehadiran,
                                'catatan' => $catatan,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    \App\Models\Sdm\JadwalKerjaDetail::insert($details);
                    \Illuminate\Support\Facades\DB::commit();
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    report($e);
                }
            }
        }
    }

    public function table(Table $table): Table
    {
        $query = JadwalKerja::query()
            ->with(['ruangan', 'pembuat', 'diketahuiOleh', 'disetujuiOleh'])
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->orderBy('id', 'desc');

        $user = Auth::user();
        if ($user) {
            $isGlobalApprover = $user->can('edit-kepegawaian-jadwal-kerja') || $user->can('approve-jadwal-wadir') || $user->can('view-kepegawaian-laporan');

            if ($this->isRestrictedGuest($user)) {
                $ownRuanganIds = $user->getOwnRuanganIds();

                if (empty($ownRuanganIds)) {
                    $query->whereRaw('0 = 1');
                } else {
                    $query->whereIn('ruangan_id', $ownRuanganIds)
                        ->where('tipe', $user->isDokter() ? 'dokter' : 'karyawan')
                        ->whereIn('status', ['published', 'locked']);
                }
            } elseif ($isGlobalApprover) {
                // Super-Admin, SDM, Wadir, dan Direktur dapat melihat seluruh daftar jadwal ruangan
            } elseif ($user->can('approve-jadwal-kabid') || $user->isKepalaDept()) {
                $bagianIds = $user->getActiveBagianIds();
                $legacyBagianRuanganIds = $user->getBagianScopedRuanganIds() ?? [];
                $koorIds = $user->getRuanganKoordinatorIds() ?? [];
                if (empty($bagianIds) && empty($legacyBagianRuanganIds) && empty($koorIds)) {
                    $query->whereRaw('0 = 1');
                } else {
                    $query->where(function ($scope) use ($bagianIds, $legacyBagianRuanganIds, $koorIds) {
                        if (!empty($bagianIds)) {
                            $scope->whereIn('bagian_id', $bagianIds);
                        }

                        if (!empty($legacyBagianRuanganIds)) {
                            $scope->orWhere(function ($legacy) use ($legacyBagianRuanganIds) {
                                $legacy->whereNull('bagian_id')
                                    ->whereIn('ruangan_id', $legacyBagianRuanganIds);
                            });
                        }

                        if (!empty($koorIds)) {
                            $scope->orWhereIn('ruangan_id', $koorIds);
                        }
                    });
                }
            } elseif ($user->isKoordinatorDokter()) {
                $ruanganIds = $user->getRuanganKoordinatorIds() ?? [];
                if (empty($ruanganIds)) {
                    $query->whereRaw('0 = 1');
                } else {
                    $query->whereIn('ruangan_id', $ruanganIds)->where('tipe', 'dokter');
                }
            } elseif ($user->isKoordinatorKaryawan()) {
                $ruanganIds = $user->getRuanganKoordinatorIds() ?? [];
                $ownRuanganId = $user->karyawan?->ruangan_id;
                if ($ownRuanganId && !in_array($ownRuanganId, $ruanganIds)) {
                    $ruanganIds[] = $ownRuanganId;
                }
                
                if (empty($ruanganIds)) {
                    $query->whereRaw('0 = 1');
                } else {
                    $query->whereIn('ruangan_id', $ruanganIds)->where('tipe', 'karyawan');
                }
            } else {
                // User biasa: hanya melihat ruangan tempat dia ditugaskan (teman seruangan)
                $ownRuanganId = $user->karyawan?->ruangan_id;
                $isDokter = $user->isDokter();
                if ($ownRuanganId) {
                    $query->where('ruangan_id', $ownRuanganId)
                        ->where('tipe', $isDokter ? 'dokter' : 'karyawan');
                } else {
                    $query->whereRaw('0 = 1');
                }
            }
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('ruangan.nama')->label('Ruangan (Tim)')->searchable()->sortable(),
                TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->color(fn ($state) => $state === 'dokter' ? 'info' : 'success'),
                TextColumn::make('bulan')->label('Bulan')->formatStateUsing(fn ($state) => date('F', mktime(0, 0, 0, $state, 1)))->sortable(),
                TextColumn::make('tahun')->label('Tahun')->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state->color())
                    ->formatStateUsing(fn ($state) => $state->nama()),
                TextColumn::make('diketahuiOleh.nama')
                    ->label('Diketahui (Kabid)')
                    ->placeholder(fn (JadwalKerja $record): string => $record->status === \App\Enums\StatusJadwalKerja::MENUNGGU_KABID ? 'Target: ' . $record->getTargetApproverName(1) : '—')
                    ->description(fn (JadwalKerja $record): ?string => $record->diketahui_at?->format('d/m/Y H:i'))
                    ->toggleable(),
                TextColumn::make('disetujuiOleh.nama')
                    ->label('Disetujui (Wadir)')
                    ->placeholder(fn (JadwalKerja $record): string => $record->status === \App\Enums\StatusJadwalKerja::MENUNGGU_WADIR ? 'Target: ' . $record->getTargetApproverName(2) : '—')
                    ->description(fn (JadwalKerja $record): ?string => $record->disetujui_at?->format('d/m/Y H:i'))
                    ->toggleable(),
                TextColumn::make('pembuat.nama')->label('Dibuat Oleh')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('kelola')
                    ->label(fn (JadwalKerja $record): string => 
                        Auth::user()?->can('edit-kepegawaian-jadwal-kerja') || 
                        Auth::user()?->can('approve-jadwal-wadir') || 
                        Auth::user()?->can('approve-jadwal-kabid') || 
                        (Auth::user()?->isKoordinator() && in_array($record->ruangan_id, Auth::user()->getRuanganKoordinatorIds() ?? []))
                            ? 'Kelola' 
                            : 'Lihat'
                    )
                    ->iconButton()
                    ->icon(fn (JadwalKerja $record): string => 
                        Auth::user()?->can('edit-kepegawaian-jadwal-kerja') || 
                        Auth::user()?->can('approve-jadwal-wadir') || 
                        Auth::user()?->can('approve-jadwal-kabid') || 
                        (Auth::user()?->isKoordinator() && in_array($record->ruangan_id, Auth::user()->getRuanganKoordinatorIds() ?? []))
                            ? 'tabler-list-details' 
                            : 'tabler-eye'
                    )
                    ->color('primary')
                    ->url(fn (JadwalKerja $record): string => route('kepegawaian.jadwal-kerja.kelola', ['id' => $record->id])),
                Action::make('delete')
                    ->label('Hapus')
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (JadwalKerja $record) => $record->delete())
                    ->successNotificationTitle('Jadwal berhasil dihapus')
                    ->visible(fn (JadwalKerja $record): bool => 
                        in_array($record->status, [\App\Enums\StatusJadwalKerja::DRAFT, \App\Enums\StatusJadwalKerja::DITOLAK]) && 
                        (Auth::user()?->can('delete-kepegawaian-jadwal-kerja') || 
                         Auth::user()?->can('edit-kepegawaian-jadwal-kerja') || 
                         (Auth::user()?->isKoordinator() && in_array($record->ruangan_id, Auth::user()->getRuanganKoordinatorIds() ?? [])))
                    ),
            ]);
    }

    public function render()
    {
        $user = Auth::user();
        $canAccessJadwal = $user && $this->isRestrictedGuest($user)
            ? !empty($user->getOwnRuanganIds())
            : ($user && (
                $user->can('view-kepegawaian-jadwal-kerja')
                || $user->isDokter()
                || $user->isKoordinator()
                || $user->isKepalaDept()
                || $user->isWadir()
                || !empty($user->karyawan?->ruangan_id)
            ));

        abort_unless($canAccessJadwal, 403, 'Anda tidak memiliki akses ke Jadwal Kerja.');

        return view('livewire.kepegawaian.jadwal-kerja.index');
    }
}
