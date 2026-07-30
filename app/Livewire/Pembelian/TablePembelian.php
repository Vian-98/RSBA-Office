<?php

namespace App\Livewire\Pembelian;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Carbon\Carbon;
use Livewire\Component;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use App\Models\Master\Supplier;
use Livewire\Attributes\Locked;
use App\Models\Gudang\Pembelian;
use App\Models\Sdm\Karyawan;
use App\Models\Surat\SuratSp3;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class TablePembelian extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    #[Locked]
    public ?int $selectedId;

    #[Locked]
    public $mengetahui = null;
    public $menyetujui = null;
    public $verifikator = null;

    #[Locked]
    public ?SuratSp3 $suratSp3;

    public static function table(Table $table): Table
    {
        return $table
            ->query(Pembelian::with('supplier')->latest())
            ->columns([
                TextColumn::make('no')
                    ->label('Nomor Transaksi')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('tgl')
                    ->label('Tanggal')
                    ->sortable(),

                TextColumn::make('supplier.nama')
                    ->label('Supplier')
                    ->searchable(),

                TextColumn::make('jenis')
                    ->label('Pembelian Dengan'),

                TextColumn::make('status_pembayaran')
                    ->label('Pembayaran')
                    ->badge()
                    ->getStateUsing(function (Pembelian $pembelian) {
                        return $pembelian->status_pembayaran ? ucfirst($pembelian->status_pembayaran) : 'Belum Dibayar';
                    })
                    ->color(
                        fn(Pembelian $pembelian) => match ($pembelian->status_pembayaran) {
                            'lunas' => 'success',
                            'tempo' => 'warning',
                            default => 'danger'
                        }
                    ),

                TextColumn::make('tgl_pembayaran')
                    ->label('Tgl Bayar'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            'Waiting' => 'warning',
                            'Selesai' => 'success',
                            'Dibatalkan' => 'danger',
                            default => 'secondary'
                        }
                    )
                    ->getStateUsing(
                        function ($record) {
                            return $record->status ? ucfirst($record->status) : 'Waiting';
                        }
                    ),

                TextColumn::make('total')
                    ->label('Total Pembelian (Rp)')
                    ->money('IDR', locale: 'id')

            ])
            ->filters([
                // Filter PO atau Langsungs
                SelectFilter::make('jenis')
                    ->options(
                        fn() => [
                            'langsung' => 'Langsung',
                            'pre_order' => 'Pre Order'
                        ]
                    )->searchable(),


                // filter supplier
                SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->options(
                        fn() => Supplier::pluck('nama', 'id')->toArray()
                    )->searchable(),

                // filter tanggal (periode)
                Filter::make('tgl')
                    ->label('Periode Pembelian')
                    ->schema([
                        DatePicker::make('tgl_mulai')
                            ->default(now()->startOfMonth())
                            ->label('Dari')
                            ->placeholder('Pilih Tanggal'),
                        DatePicker::make('tgl_selesai')
                            ->default(now()->endOfMonth())
                            ->label('Sampai')
                            ->placeholder('Pilih Tanggal')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tgl_mulai'],
                                fn($query, $tgl_mulai) => $query->where('tgl', '>=', $tgl_mulai)
                            )
                            ->when(
                                $data['tgl_selesai'],
                                fn($query, $tgl_selesai) => $query->where('tgl', '<=', $tgl_selesai)
                            );
                    })
                    // indicator filter
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['tgl_mulai'] ?? null) {
                            $indicators[] = Indicator::make('Dari ' . Carbon::parse($data['tgl_mulai'])->toFormattedDateString())
                                ->removeField('tgl_mulai');
                        }

                        if ($data['tgl_selesai'] ?? null) {
                            $indicators[] = Indicator::make('Sampai ' . Carbon::parse($data['tgl_selesai'])->toFormattedDateString())
                                ->removeField('tgl_selesai');
                        }

                        return $indicators;
                    })

            ])
            ->recordActions([
                Action::make('mark_lunas')
                    ->label('Bayar Lunas')
                    ->icon('tabler-cash')
                    ->iconButton()
                    ->color('success')
                    ->tooltip('Tandai Lunas')
                    ->visible(fn(Pembelian $record) => $record->status_pembayaran !== 'lunas')
                    ->requiresConfirmation()
                    ->action(function (Pembelian $record) {
                        $record->update([
                            'status_pembayaran' => 'lunas',
                            'tgl_pembayaran' => now()->format('Y-m-d'),
                        ]);
                    }),
                Action::make('detail')
                    ->icon('tabler-file-symlink')
                    ->iconButton()
                    ->action(
                        fn($record, $livewire) => $livewire->modalForm(
                            modal: 'modal-detail-pembelian',
                            id: $record->getKey()
                        )
                    ),
                Action::make('sp3-create')
                    ->icon('tabler-file-dollar')
                    ->iconButton()
                    ->color(
                        fn($record) => $record->sp3_id === null ? 'gray' : 'success'
                    )
                    ->action(
                        function ($record, $livewire) {
                            if ($record->sp3_id) {
                                $livewire->sp3Print(
                                    modal: 'modal-view-sp3',
                                    id: $record->sp3_id
                                );
                            } else {
                                $livewire->modalForm(
                                    modal: 'modal-create-sp3',
                                    id: $record->getKey()
                                );
                            }
                        }

                    ),

                Action::make('print-po')
                    ->iconButton()
                    ->icon('tabler-printer')
                    ->schema([

                        Select::make('mengetahui')
                            ->label('Mengetahui')
                            ->options(
                                Karyawan::orderBy('nama')
                                    ->pluck('nama', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload(),

                        Select::make('menyetujui')
                            ->label('Menyetujui')
                            ->options(
                                Karyawan::orderBy('nama')
                                    ->pluck('nama', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload(),


                        Select::make('verifikator')
                            ->label('Verifikator')
                            ->options(
                                Karyawan::orderBy('nama')
                                    ->pluck('nama', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function ($record, array $data, Component $livewire) {
                        $ids = [
                            $data['mengetahui'],
                            $data['menyetujui'],
                            $data['verifikator'],
                        ];

                        // $map = Karyawan::whereIn('id', $ids)
                        //     ->pluck('nama', 'id');
                        $map = Karyawan::whereIn('id', $ids)
                            ->get(['id', 'gelar_depan', 'nama', 'gelar_belakang'])
                            ->keyBy('id');

                        $livewire->mengetahui  = $map[$data['mengetahui']]?->full_nama ?? null;
                        $livewire->menyetujui  = $map[$data['menyetujui']]?->full_nama ?? null;
                        $livewire->verifikator = $map[$data['verifikator']]?->full_nama ?? null;

                        $livewire->selectedId = $record->getKey();
                        $livewire->dispatch('trigger-print');
                    })
                    ->modalFooterActions(fn() => [
                        Action::make('submit')
                            ->label('Print')
                            ->submit('submit'),
                        Action::make('cancel')
                            ->label('Tutup')
                            ->close()
                            ->color('gray'),
                    ])
                    ->modalFooterActionsAlignment('right')

                    ->visible(
                        fn($record) => $record->jenis === "Pre Order"
                    )
            ]);
    }

    function modalForm($modal, $id)
    {
        $this->selectedId = $id;
        $this->dispatch('open-modal', id: $modal);
    }

    public function sp3Print($modal, $id)
    {
        $this->suratSp3 = SuratSp3::findOrFail($id);
        $this->dispatch('open-modal', id: $modal);
    }

    #[On('new-transaksi-langsung-created')]
    #[On('new-pesanan-created')]
    #[On('penerimaan-beli-saved')]
    public function refreshTable()
    {
        $this->resetTable();
    }

    public function render()
    {
        return view('livewire.pembelian.table-pembelian');
    }
}
