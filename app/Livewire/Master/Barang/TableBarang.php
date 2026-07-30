<?php

namespace App\Livewire\Master\Barang;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use App\Models\Master\Barang;
use App\Models\Master\BarangKategori;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TableBarang extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    #[Locked]
    public $selectedId;

    public static function table(Table $table)
    {
        return $table
            ->query(Barang::query())
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                TextColumn::make('nama')
                    ->label('Nama Barang')
                    ->searchable(),

                TextColumn::make('satuan.nama')
                    ->label('Satuan'),

                TextColumn::make('kategori.nama')
                    ->label('Kategori'),


                TextColumn::make('tipe')
                    ->label('Tipe'),

                TextColumn::make('min_stok')
                    ->label('Minimal Stok')
                    ->default(0),

                TextColumn::make('bhp')
                    ->label('BHP')
                    ->getStateUsing(function ($record) {
                        return ($record->bhp === 1) ? 'BHP' : '';
                    })

            ])
            ->filters([
                // filter kategori
                SelectFilter::make('kategori_id')
                    ->label('Kategori')
                    ->options(
                        fn() => BarangKategori::pluck('nama', 'id')->toArray()
                    ),

                SelectFilter::make('bhp')
                    ->label('BHP')
                    ->options([
                        '1' => 'BHP',
                        '0' => 'Bukan',
                    ])


            ])

            ->recordActions([
                Action::make('print')
                    ->iconButton()
                    ->icon('tabler-tags')
                    ->action(
                        fn($record, $livewire) => $livewire->modalForm(
                            modal: 'modal-print-label',
                            id: $record->getKey()
                        )
                    ),

                Action::make('edit')
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->action(
                        fn($record, $livewire) => $livewire->modalForm(
                            modal: 'modal-edit-barang',
                            id: $record->getKey()
                        )
                    ),


                Action::make('delete')
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->color('danger')
            ]);
    }

    function modalForm($modal, $id)
    {
        $this->selectedId = $id;
        $this->dispatch('open-modal', id: $modal);
    }

    public function render()
    {
        return view('livewire.master.barang.table-barang');
    }
}
