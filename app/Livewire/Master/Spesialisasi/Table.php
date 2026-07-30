<?php

namespace App\Livewire\Master\Spesialisasi;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use App\Models\Sdm\DokterSpesialisasi;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table as FilamentTable;
use Livewire\Component;

class Table extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    public ?DokterSpesialisasi $spesialisasi;

    public static function table(FilamentTable $table): FilamentTable
    {
        return $table
            ->query(DokterSpesialisasi::query())
            ->deferLoading(false)
            ->striped()
            ->columns([
                TextColumn::make('id')
                    ->label('Id'),
                TextColumn::make('nama')
                    ->label('Spesialis')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('singkatan')
                    ->label('Singkatan / Gelar')
            ])
            ->recordActions([
                Action::make('edit')
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->action(
                        fn(DokterSpesialisasi $sp, $livewire) => $livewire->editSp(modal: 'modal-edit-spesialisasi', id: $sp->getKey())
                    )
            ]);
    }


    function editSp($modal, $id)
    {
        $this->spesialisasi = DokterSpesialisasi::findOrFail($id);
        $this->dispatch('open-modal', id: $modal);
    }

    public function render()
    {
        return view('livewire.master.spesialisasi.table');
    }
}
