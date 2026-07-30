<?php

namespace App\Livewire\Pembelian;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Models\Gudang\PembelianDetail;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class TablePembelianBarang extends Component implements HasTable, HasForms, HasActions
{

    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    public function table(Table $table): Table
    {

        return $table
            ->query(PembelianDetail::with(['pembelian', 'barang', 'terimas']))
            ->columns([
                TextColumn::make('barang.nama')
                    ->label('Barang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pembelian.no')
                    ->label('No Tranksaksi')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('pembelian.tgl')
                    ->label('Tanggal'),

                TextColumn::make('jumlah')
                    ->label('Jumlah Beli'),

                TextColumn::make('terimas_sum_jumlah')
                    ->sum('terimas', 'jumlah')
                    ->default(0)
                    ->label('Jumlah Diterima'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(
                        function (PembelianDetail $pembelianDetail) {
                            $jumlahBeli = $pembelianDetail->jumlah ?? 0;
                            $jumlahDiterima = $pembelianDetail->terimas?->sum('jumlah') ?? 0;

                            if ($jumlahDiterima == $jumlahBeli && $jumlahBeli > 0) {
                                return 'Selesai';
                            } elseif ($jumlahDiterima > 0 && $jumlahDiterima < $jumlahBeli) {
                                return 'Sebagian';
                            } else {
                                return 'Belum Diterima';
                            }
                        }
                    )
                    ->color(
                        fn($state) => match ($state) {
                            'Selesai' => 'success',
                            'Sebagian' => 'warning',
                            'Belum Diterima' => 'danger',
                        }
                    )



            ]);
    }



    public function render()
    {
        return view('livewire.pembelian.table-pembelian-barang');
    }
}
