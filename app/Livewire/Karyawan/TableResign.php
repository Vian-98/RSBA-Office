<?php

namespace App\Livewire\Karyawan;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\Sdm\Karyawan;
use App\Enums\StatusKaryawan;
use App\Models\Sdm\Jabatan;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;

class TableResign extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    #[Locked]
    public $karyawanId = null; //default

    public static function table(Table $table): Table
    {
        return $table
            ->query(Karyawan::query()->with('latestJabatan.jabatan')
                ->whereNotNull('resign'))
            ->deferLoading(false)
            ->striped()
            ->columns([
                TextColumn::make('nip')
                    ->label('NIP')
                    ->copyable()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn(StatusKaryawan $state) => $state->nama())
                    ->badge()
                    ->color(fn(StatusKaryawan $state) => $state->color()),

                TextColumn::make('latestJabatan.jabatan.nama')
                    ->label('Jabatan')
                    ->default('-')
                    ->action(function (Karyawan $record, $livewire): void {
                        // dispatch event to livewire
                        $livewire->modal(
                            modal: 'history-jabatan',
                            karyawan: $record->getKey()
                        );
                    })
                    ->tooltip('History Jabatan'),

                TextColumn::make('masakerja')
                    ->label('Masa Kerja'),

                TextColumn::make('resign')
                    ->badge()
                    ->formatStateUsing(
                        fn($state): string => match ($state) {
                            '1' => 'Resign / Mengundurkan Diri',
                            '2' => 'Diberhentikan',
                            '4' => 'Habis Kontrak',
                            default => 'Aktif'
                        }
                    )
                    ->color(
                        fn(Karyawan $record) => (!empty($record->resign)) ? 'danger' : 'primary'
                    ),

                TextColumn::make('resign_at')
                    ->label('Tanggal Resign')
            ])
            ->filters([
                // Filter status
                SelectFilter::make('status')
                    ->label('Status Karyawan')
                    ->options(
                        // options dari enum StatusKaryawan
                        fn(): array => collect(StatusKaryawan::options())
                            ->pluck('label', 'value')
                            ->toArray()
                    ),

                // Filter Jabatan
                // FIXME tidak dapat filter jabatan saat ini saja
                Filter::make('Jabatan')
                    ->schema([
                        Select::make('Jabatan')
                            ->options(
                                fn() => Jabatan::pluck('nama', 'id')->toArray()
                            )->searchable()
                    ])->modifyQueryUsing(function (Builder $query, $data) {
                        return $query
                            ->when(
                                $data['Jabatan'],
                                fn(Builder $query, $state): Builder =>  $query->whereHas('latestJabatan', function ($query) use ($state) {
                                    $query->where('jabatan_id', $state);
                                })
                            );
                    })
                    ->indicateUsing(function ($data): ?string {
                        if (! $data['Jabatan']) {
                            return null;
                        }

                        $jabatanNama = Jabatan::find($data['Jabatan'])->nama ?? null;

                        return $jabatanNama ? 'Jabatan : ' . $jabatanNama : null;
                    })
            ])
            ->recordActions([
                Action::make('view-profile')
                    ->iconButton()
                    ->icon('tabler-printer')
                    ->color('primary')
                    ->action(function (Karyawan $record, $livewire): void {
                        $livewire->modal(
                            modal: 'modal-print-cv',
                            karyawan: $record->getKey()
                        );
                    }),
            ]);
    }

    function modal($modal, $karyawan)
    {
        $this->karyawanId = $karyawan;
        $this->dispatch('open-modal', id: $modal);
    }

    // #[On('open-history-jabatan')]
    public function historyJabatan($id, $karyawan)
    {
        $this->karyawanId = $karyawan;
        $this->dispatch('open-modal', id: $id);
    }

    public function render()
    {
        return view('livewire.karyawan.table-resign');
    }
}
