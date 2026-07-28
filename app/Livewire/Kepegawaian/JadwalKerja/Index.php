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

    public function mount()
    {
        $this->autoGenerateRegulerSchedules();
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
                    $roomMembers = $grouped[$ruanganId];
                    foreach ($roomMembers as $karyawan) {
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $date = \Carbon\Carbon::create($year, $month, $d);
                            $dateStr = $date->format('Y-m-d');

                            $shiftId = null;
                            if ($date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 5) {
                                $shiftId = $shiftReguler->id;
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
            if ($user->hasRole(['Super-Admin', 'Staff-SDM'])) {
                // Super-Admin & Staff-SDM dapat melihat semua ruangan
            } elseif ($user->isKoordinator()) {
                // Koordinator: ruangan koordinasi + ruangan sendiri
                $ruanganIds = $user->getRuanganKoordinatorIds() ?? [];
                $ownRuanganId = $user->karyawan?->ruangan_id;
                if ($ownRuanganId && !in_array($ownRuanganId, $ruanganIds)) {
                    $ruanganIds[] = $ownRuanganId;
                }
                
                if (empty($ruanganIds)) {
                    $query->whereRaw('0 = 1');
                } else {
                    $query->whereIn('ruangan_id', $ruanganIds);
                }
            } else {
                // User biasa: hanya melihat ruangan tempat dia ditugaskan (teman seruangan)
                $ownRuanganId = $user->karyawan?->ruangan_id;
                if ($ownRuanganId) {
                    $query->where('ruangan_id', $ownRuanganId);
                } else {
                    $query->whereRaw('0 = 1');
                }
            }
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('ruangan.nama')->label('Ruangan (Tim)')->searchable()->sortable(),
                TextColumn::make('bulan')->label('Bulan')->formatStateUsing(fn ($state) => date('F', mktime(0, 0, 0, $state, 1)))->sortable(),
                TextColumn::make('tahun')->label('Tahun')->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state->color())
                    ->formatStateUsing(fn ($state) => $state->nama()),
                TextColumn::make('diketahuiOleh.nama')->label('Diketahui (Kabid)')->placeholder('—')->toggleable(),
                TextColumn::make('disetujuiOleh.nama')->label('Disetujui (Wadir)')->placeholder('—')->toggleable(),
                TextColumn::make('pembuat.nama')->label('Dibuat Oleh')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('kelola')
                    ->label(fn (JadwalKerja $record): string => 
                        Auth::user()?->hasRole(['Super-Admin', 'Staff-SDM']) || 
                        (Auth::user()?->isKoordinator() && in_array($record->ruangan_id, Auth::user()->getRuanganKoordinatorIds() ?? []))
                            ? 'Kelola' 
                            : 'Lihat'
                    )
                    ->iconButton()
                    ->icon(fn (JadwalKerja $record): string => 
                        Auth::user()?->hasRole(['Super-Admin', 'Staff-SDM']) || 
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
                        (Auth::user()?->hasRole(['Super-Admin', 'Staff-SDM']) || 
                         (Auth::user()?->isKoordinator() && in_array($record->ruangan_id, Auth::user()->getRuanganKoordinatorIds() ?? [])))
                    ),
            ]);
    }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.kepegawaian.jadwal-kerja.index');
    }
}
