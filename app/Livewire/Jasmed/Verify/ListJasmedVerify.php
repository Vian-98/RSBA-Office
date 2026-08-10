<?php

namespace App\Livewire\Jasmed\Verify;

use App\Models\JmPasien;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ListJasmedVerify extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public $selectedId;

    public $periode, $cabar, $layanan, $kelompok, $batch;

    public function mount($periode, $cabar, $layanan, $kelompok, $batch)
    {
        $this->periode = $periode;
        $this->cabar = $cabar;
        $this->layanan = $layanan;
        $this->kelompok = $kelompok;
        $this->batch = $batch;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn(): Builder => JmPasien::with('prosentase')
                    ->where('tgl_checkout', 'like', "$this->periode%")
                    ->where('layanan', $this->layanan)
                    ->where('cabar', $this->cabar)
                    ->when(
                        $this->kelompok,
                        fn($q) => $q->where('kelompok', $this->kelompok)
                    )
                    ->when(
                        $this->batch,
                        fn($q) => $q->where('batch', $this->batch)
                    )
            )
            ->columns([
                TextColumn::make('no_rekmedis')->searchable(),
                TextColumn::make('nama_pasien')->searchable(),
                TextColumn::make('sep')->searchable()->sortable(),
                TextColumn::make('disetujui')
                    ->label('Klaim')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    ),
                TextColumn::make('prosentase.total_billing')
                    ->label('Total Billing')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    ),
                TextColumn::make('prosentase.chosaring')
                    ->label('Chosaring')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    ),
                TextColumn::make('prosentase.jasa_p')
                    ->label('Jasa Pelayanan')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    ),
                TextColumn::make('prosentase.klaim_min_rincian')
                    ->label('Klaim Min Rincian')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    )
                    ->color(fn($state) => $state < 0 ? 'danger' : ''),
                TextColumn::make('prosentase.jasa_pelayanan')
                    ->label('Jasa Pelayanan Dibagi')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    )
                    ->color(fn($state) => $state < 0 ? 'danger' : '')
                    ->sortable(),
                TextColumn::make('prosentase.jasa_rs')
                    ->label('RS')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    ),
                TextColumn::make('prosentase.jasa_medis')
                    ->label('Medis')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    ),
                TextColumn::make('prosentase.jasa_operator')
                    ->label('Operator')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    )
                    ->sortable(),
                TextColumn::make('prosentase.jasa_anastesi')
                    ->label('Anastesi')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    ),
                TextColumn::make('prosentase.jasa_penata')
                    ->label('Penata')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    ),
                TextColumn::make('prosentase.jasa_resus')
                    ->label('Resus')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    ),
                TextColumn::make('prosentase.jasa_pekerja')
                    ->label('Pekerja')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    ),
                TextColumn::make('prosentase.jasa_sppdkgh')
                    ->label('SPPDKGH')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    ),
                TextColumn::make('prosentase.jasa_um_sertifikat')
                    ->label('Umum Sertifikat')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    ),
                TextColumn::make('prosentase.jasa_dpjp_hd')
                    ->label('DPJP HD')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.'
                    ),
            ])
            ->filters([
                Filter::make('minus')
                    ->label('Negatif')
                    ->schema([
                        TextInput::make('nilai_minus')
                            ->type('number')
                            ->label('Jasa Pelayanan Dibagi')
                            ->placeholder('Nilai')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['nilai_minus']),
                            fn(Builder $query, $minus): Builder => $query->whereHas(
                                'prosentase',
                                fn($q) => $q->where('jasa_pelayanan', '<=', $minus)
                            )
                        );
                    })
            ])
            ->recordActions([
                Action::make('recalculate')
                    ->iconButton()
                    ->icon('tabler-refresh')
                    ->color('danger')
                    ->action(fn($record) => $this->openModal(
                        id: $record->getKey(),
                        modal: 'modal-edit-rician-jasmed'
                    )),

                Action::make('edit-dokter')
                    ->iconButton()
                    ->icon('tabler-user-edit')
                    ->action(fn($record) => $this->openModal(
                        id: $record->getKey(),
                        modal: 'modal-edit-dokter'
                    )),
            ], position: RecordActionsPosition::BeforeColumns);
    }

    public function openModal($id, $modal): void
    {
        $this->selectedId = $id;
        $this->dispatch('open-modal', id: $modal);
    }

    public function render(): View
    {
        return view('livewire.jasmed.verify.list-jasmed-verify');
    }
}
