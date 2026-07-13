<?php

namespace App\Livewire\Gudang;

use App\Models\Master\Barang;
use App\Models\Master\BarangKategori;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TableGudang extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    #[Locked]
    public ?Barang $barang;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Barang::with(['stoks', 'stoks.penyimpanan', 'stoks.penerimaanDet.penerimaan'])
                    ->withSum('stoks', 'stok')
            )
            ->deferLoading(false)
            ->striped()
            ->headerActions([
                Action::make('download')
                    ->label('Stoks')
                    ->color('gray')
                    ->icon('tabler-file-excel')
                    ->tooltip('Stok Tersedia')
                    ->action(
                        fn() => $this->downloadStok()
                    ),
            ])
            ->columns([
                TextColumn::make('nama')
                    ->label('Barang')
                    ->searchable(),

                TextColumn::make('kategori.nama')
                    ->label('Kategori')
                    ->sortable(),

                TextColumn::make('stoks_sum_stok') // Menggunakan stok hasil agregasi
                    ->label('Stok Tersedia')
                    ->default(0)
                    ->sortable()
                    ->action(
                        fn($record) => $this->modalForm('modal-stok-aktif', $record->getKey())
                    )
                    ->icon('tabler-file-symlink')
                    ->iconPosition('after'),


                TextColumn::make('satuan.nama')
                    ->label('Satuan'),

                TextColumn::make('tgl_terakhir_masuk')
                    ->label('Terakhir Masuk')
                    ->getStateUsing(
                        fn($record) => $record->stoks->max('penerimaanDet.penerimaan.created_at')
                            ? Carbon::parse($record->stoks->max('penerimaanDet.penerimaan.created_at'))
                                ->timezone('Asia/Jakarta')
                                ->format('H:i d/m/Y')
                            : 'Belum pernah beli barang ini.'
                    ),


                IconColumn::make('stok_indicator') // Kolom untuk indikator stok rendah
                    ->label('')
                    ->getStateUsing(
                        fn($record) => $record->stoks_sum_stok < $record->min_stok ? 'tabler-trending-down' : null
                    )
                    ->icon(fn(string $state) => $state)
                    ->color('danger')
                    ->tooltip('Stok Dibawah 10')
                    ->width('w-10'),

            ])
            ->filters([
                SelectFilter::make('has_stok')
                    ->label('Stok')
                    ->options(
                        fn(): array => [
                            '1' => 'Tersedia',
                            '0' => 'Tidak Tersedia',
                        ]
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (!isset($data['value']) || $data['value'] === '' || $data['value'] === null) {
                            return $query;
                        }

                        if ($data['value'] === '1') {
                            return $query->whereHas('stoks', function (Builder $q) {
                                $q->where('stok', '>', 0);
                            });
                        }

                        if ($data['value'] === '0') {
                            return $query->where(function (Builder $q) {
                                $q->whereDoesntHave('stoks')
                                    ->orWhereHas('stoks', function (Builder $q2) {
                                        $q2->havingRaw('SUM(stok) <= 0');
                                    });
                            });
                        }

                        return $query;
                    }),

                SelectFilter::make('kategori_id')
                    ->label('Kategori')
                    ->options(
                        fn(): array => BarangKategori::pluck('nama', 'id')->toArray()
                    )
                    ->searchable(),


            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('detil')
                        ->label('Stocks')
                        ->icon('tabler-file-symlink')
                        ->tooltip('Detil Stok')
                        ->action(
                            fn(Barang $barang, $livewire) => $livewire->modalForm(modal: 'modal-detil-stok', id: $barang->getKey())
                        ),

                    Action::make('print-kartu-stok-by-trans')
                        ->label('Kartu Stok Transaksi')
                        ->icon('tabler-printer')
                        ->tooltip('Kartu Stok')
                        ->action(
                            fn(Barang $barang) => $this->printKartuStok(
                                barang: $barang,
                                printId: 'print-kartu-stok-transaksi'
                            )
                        ),

                    Action::make('print-kartu-stok')
                        ->label('Kartu Stok')
                        ->icon('tabler-printer')
                        ->tooltip('Kartu Stok')
                        ->action(
                            fn(Barang $barang) => $this->printKartuStok(
                                barang: $barang,
                                printId: 'print-kartu-stok'
                            )
                        ),
                ])->tooltip('Actions')
            ])
        ;
    }

    public function downloadStok()
    {
        // TODO
    }

    public function modalForm($modal, $id)
    {
        $this->barang = Barang::findOrFail($id);
        $this->dispatch('open-modal', id: $modal);
    }

    function printKartuStok($barang, $printId)
    {
        $this->barang = $barang;
        $this->dispatch($printId);
        // $this->js("printArea('print-kartu-stok')");
    }

    public function render()
    {
        return view('livewire.gudang.table-gudang');
    }
}
