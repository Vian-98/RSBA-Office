<?php

namespace App\Livewire\Master\Supplier;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\Master\Supplier;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Attributes\Locked;

class TableSupplier extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms, InteractsWithTable;

    #[Locked]
    public int $selectedId;

    public static function table(Table $table): Table
    {
        return $table
            ->query(Supplier::query())
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('telp')
                    ->label('Telp'),
                TextColumn::make('email')
                    ->label('Email'),
                TextColumn::make('npwp')
                    ->label('NPWP'),
                TextColumn::make('bank')
                    ->label('Bank'),
                TextColumn::make('norek')
                    ->label('No. Rekening'),
                TextColumn::make('an')
                    ->label('PIC / Atas Nama'),
                TextColumn::make('alamat')
                    ->label('Alamat')
            ])
            ->recordActions([
                Action::make('edit')
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->action(fn(Supplier $supplier, $livewire) => $livewire->modal(
                        modal: 'modal-edit-supplier',
                        id: $supplier->getKey()
                    )),
            ]);
    }

    function modal($modal, $id)
    {
        $this->selectedId = $id;
        $this->dispatch('open-modal', id: $modal);
    }

    public function render()
    {
        return view('livewire.master.supplier.table-supplier');
    }
}
