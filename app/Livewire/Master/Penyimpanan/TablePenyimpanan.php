<?php

namespace App\Livewire\Master\Penyimpanan;

use Throwable;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use App\Models\Master\BarangPenyimpanan;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class TablePenyimpanan extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;
    use Interactions;

    #[Locked]
    public $selectedId;

    public static function table(Table $table): Table
    {
        return $table
            ->query(BarangPenyimpanan::query())
            ->columns([
                TextColumn::make('nama')
                    ->label('Tempat Penyimpanan')
                    ->searchable(),

                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
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
                    ->requiresConfirmation()
                    ->action(
                        fn($record, $livewire) => $livewire->deletePenyimpanan(
                            id: $record->getKey()
                        )
                    )
            ])
            ->recordClasses(function () {
                return 'hover:bg-gray-200/25';
            });
    }

    function modalForm($id)
    {
        $this->selectedId = $id;
        $this->dispatch('open-modal', id: 'modal-edit-penyimpanan');
    }

    function deletePenyimpanan($id)
    {
        DB::beginTransaction();
        try {
            BarangPenyimpanan::findOrFail($id)->delete();
            DB::commit();

            $this->toast()
                ->success('Berhasil', 'Tempat penyimpanan berhasil dihapus.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Gagal', "Tempat penyimpanan gagal dihapus. Error: {$e->getMessage()}")
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.penyimpanan.table-penyimpanan');
    }
}
