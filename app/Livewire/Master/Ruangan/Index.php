<?php

namespace App\Livewire\Master\Ruangan;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Throwable;
use App\Models\Ruangan;
use App\Traits\AuthorizesFromRoute;
use Livewire\Component;
use Filament\Tables\Table;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use TallStackUi\Traits\Interactions;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

#[Lazy]
#[Title('Data Ruangan')]
class Index extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use AuthorizesFromRoute;
    use InteractsWithTable, InteractsWithForms;
    use Interactions;

    public int $selectedId;

    public static function table(Table $table): Table
    {
        return $table
            ->query(Ruangan::query())
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('is_active')
                    ->label('Aktif')
                    ->formatStateUsing(fn($state) => match ($state) {
                        1 => 'Aktif',
                        0 => 'Tidak Aktif'
                    })
                // ->formatState(fn()=>)
            ])
            ->recordActions([
                Action::make('edit')
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->color('primary')
                    ->action(
                        fn(Ruangan $record, $livewire) => $livewire->openModal(
                            modal: 'modal-edit-ruangan',
                            id: $record->getKey()
                        )
                    ),

                Action::make('delete')
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->color('danger')
                    ->action(
                        fn(Ruangan $record, $livewire) => $livewire->delete($record->getKey())
                    )
            ]);
    }

    function openModal($modal, $id)
    {
        $this->selectedId = $id;
        $this->dispatch('open-modal', id: $modal);
    }

    function delete($id)
    {
        try {
            $ruangan = Ruangan::findOrFail($id);
            $ruangan->delete();

            $this->toast()
                ->success('Berhasil', 'Hapus data berhasil')
                ->send();
        } catch (Throwable $e) {
            $this->toast()
                ->error('Failed', 'Error : ' . $e->getMessage())
                ->send();
        }
    }
    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.master.ruangan.index');
    }
}
