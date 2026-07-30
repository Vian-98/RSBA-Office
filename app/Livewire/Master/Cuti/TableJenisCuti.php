<?php

namespace App\Livewire\Master\Cuti;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use App\Models\Surat\CutiJenis;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TableJenisCuti extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;


    #[Locked]
    public CutiJenis $cuti_jenis;

    public static function table(Table $table): Table
    {
        return $table->query(
            CutiJenis::query()
        )
            ->columns([
                TextColumn::make('nama')
                    ->label('Jenis Cuti'),

                TextColumn::make('lama')
                    ->label('Lama Cuti')
            ])
            ->recordActions([
                Action::make('edit')
                    ->action(
                        fn($record, $livewire) => $livewire->modalTrigger(
                            modal: 'modal-edit-jenis-cuti',
                            id: $record->getKey()
                        )
                    )

            ]);
    }

    public function modalTrigger($modal, $id)
    {
        $this->cuti_jenis = CutiJenis::find($id);
        $this->dispatch('open-modal', id: $modal);
    }

    public function render()
    {
        return view('livewire.master.cuti.table-jenis-cuti');
    }
}
