<?php

namespace App\Livewire\Master\Barang\Kategori;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use App\Models\Master\BarangKategori;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TableKategori extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    #[Locked]
    public $selectedId;

    public static function table(Table $table): Table
    {

        return $table
            ->query(BarangKategori::query())
            ->columns([
                TextColumn::make('nama')
                    ->label('Kategori')
                    ->searchable(),

                TextColumn::make('deskripsi')
                    ->label('Deskripsi'),

                TextColumn::make('prefix')
                    ->label('Preffix Kode')
            ])
            ->recordActions([
                Action::make('edit')
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->action(
                        fn($record, $livewire) => $livewire->modalForm(
                            id: $record->getKey()
                        )
                    ),

                Action::make('delete')
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->color('danger')

            ])
            ->recordClasses(function () {
                return 'hover:bg-gray-200/25'; // Customize hover color here
            });
    }

    function modalForm($id)
    {
        $this->selectedId = $id;
        $this->dispatch('open-modal', id: 'modal-edit-kategori-barang');
    }

    public function render()
    {
        return view('livewire.master.barang.kategori.table-kategori');
    }
}
