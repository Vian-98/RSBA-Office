<?php

namespace App\Livewire\Master\Barang\Satuan;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Throwable;
use Livewire\Component;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use App\Models\Master\BarangSatuan;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Attributes\Locked;
use TallStackUi\Traits\Interactions;

class TableSatuan extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use Interactions;
    use InteractsWithForms, InteractsWithTable;

    #[Locked]
    public $selectedId;

    public static function table(Table $table): Table
    {
        return $table
            ->query(BarangSatuan::query())
            ->deferLoading(false)
            ->columns([
                TextColumn::make('nama')
                    ->label('Satuan Barang')
                    ->searchable(),
                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
            ])
            ->recordActions([
                Action::make('edit')
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->action(
                        fn($record, $livewire) => $livewire->editForm(
                            modal: 'modal-edit-satuan',
                            id: $record->getKey()
                        )
                    ),

                Action::make('hapus')
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(
                        fn($record, $livewire) => $livewire->delete(modal: 'modal-edit-satuan', id: $record->getKey())
                    )

            ]);
    }

    function delete($modal, $id)
    {
        DB::beginTransaction();
        try {
            $satuan = BarangSatuan::findOrFail($id);
            $satuan->delete();

            DB::commit();

            $this->dispatch('satuan-deleted');

            $this->toast()
                ->success('Berhasil', 'Data satuan dinonaktifkan.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Failed', 'Error' . $e->getMessage())
                ->send();
        }
    }


    function editForm($modal, $id)
    {
        $this->selectedId = $id;
        $this->dispatch('open-modal', id: $modal);
    }

    public function render()
    {
        return view('livewire.master.barang.satuan.table-satuan');
    }
}
