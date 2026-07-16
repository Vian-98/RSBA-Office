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

    public function table(Table $table): Table
    {
        $query = JadwalKerja::query()
            ->with(['ruangan', 'pembuat']);

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
                TextColumn::make('pembuat.nama')->label('Dibuat Oleh'),
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
                        $record->status === \App\Enums\StatusJadwalKerja::DRAFT && 
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
