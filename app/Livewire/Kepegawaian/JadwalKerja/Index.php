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
            $ruanganIds = $user->getRuanganKoordinatorIds();
            // null = Super-Admin/Staff-SDM, akses semua ruangan
            // [] kosong = tidak punya akses ruangan sama sekali
            if ($ruanganIds !== null) {
                if (empty($ruanganIds)) {
                    $query->whereRaw('0 = 1'); // tidak ada ruangan yg bisa diakses
                } else {
                    $query->whereIn('ruangan_id', $ruanganIds);
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
                    ->label('Kelola')
                    ->iconButton()
                    ->icon('tabler-list-details')
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
                    ->visible(fn (JadwalKerja $record): bool => $record->status === \App\Enums\StatusJadwalKerja::DRAFT),
            ]);
    }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.kepegawaian.jadwal-kerja.index');
    }
}
