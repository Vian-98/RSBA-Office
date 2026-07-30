<?php

namespace App\Livewire\Distribusi;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Carbon\Carbon;
use Livewire\Component;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;
use App\Models\Gudang\Distribusi;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class TableDistribusi extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    #[Locked]
    public $distribusi;
    // public int $selectedId;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Distribusi::with(
                    ['ruangan']
                )
                    ->latest()
            )
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->sortable(),

                TextColumn::make('ruangan.nama', 'Ruangan'),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->wrap()
                    ->limit(72),

                TextColumn::make('dist_as')
                    ->label('Distribusi Sbg')
                    ->badge()
                    ->formatStateUsing(
                        fn($state) => match ($state) {
                            'keluar' => 'Pengeluaran',
                            'asset' => 'Sebagai Asset',
                            default => $state,
                        }
                    )
                    ->color(
                        fn($state) => match ($state) {
                            'keluar' => 'danger',
                            'asset' => 'success',
                            default => 'secondary',
                        }
                    ),

                TextColumn::make('pengirim_nama')
                    ->label('Pengirim'),

                TextColumn::make('penerima_nama')
                    ->label('Penerima'),
            ])
            ->filters([

                // filter tanggal (periode)
                Filter::make('tanggal')
                    ->label('Periode Distribusi')
                    ->schema([
                        DatePicker::make('tgl_mulai')
                            ->default(now()->startOfMonth())
                            ->label('Dari')
                            ->placeholder('Pilih Tanggal'),
                        DatePicker::make('tgl_selesai')
                            ->default(now())
                            ->label('Sampai')
                            ->placeholder('Pilih Tanggal')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tgl_mulai'],
                                fn($query, $tgl_mulai) => $query->where('tanggal', '>=', $tgl_mulai)
                            )
                            ->when(
                                $data['tgl_selesai'],
                                fn($query, $tgl_selesai) => $query->where('tanggal', '<=', $tgl_selesai)
                            );
                    })
                    // indicator filter
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['tgl_mulai'] ?? null) {
                            $indicators[] = Indicator::make('Tgl : ' . Carbon::parse($data['tgl_mulai'])->toFormattedDateString())
                                ->removeField('tgl_mulai');
                        }

                        if ($data['tgl_selesai'] ?? null) {
                            $indicators[] = Indicator::make('Sampai : ' . Carbon::parse($data['tgl_selesai'])->toFormattedDateString())
                                ->removeField('tgl_selesai');
                        }

                        return $indicators;
                    })

            ])
            ->recordActions([
                Action::make('detail')
                    ->label('Detail')
                    ->icon('tabler-file-symlink')
                    ->iconButton()
                    ->action(
                        fn($record, $livewire) => $livewire->modal(
                            modal: 'detail-distribusi',
                            id: $record->getKey()
                        )
                    ),
            ])
        ;
    }

    function modal($modal, $id)
    {
        $this->distribusi = Distribusi::findOrFail($id);
        $this->dispatch('open-modal', id: $modal);
    }

    #[On('new-distribusi-created')]
    public function render()
    {
        return view('livewire.distribusi.table-distribusi');
    }
}
