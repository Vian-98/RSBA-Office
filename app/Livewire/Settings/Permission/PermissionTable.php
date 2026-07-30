<?php

namespace App\Livewire\Settings\Permission;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Throwable;
use Livewire\Component;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Spatie\Permission\Models\Permission;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class PermissionTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms, InteractsWithTable;

    use Interactions;
    public $selectedPermission;

    function table(Table $table): Table
    {
        return $table
            ->query(Permission::query())
            ->deferLoading(false)
            ->striped()
            ->columns([
                TextColumn::make('id')
                    ->label('Id'),
                TextColumn::make('name')
                    ->label('Permission')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guard_name')
                    ->label('Guard'),
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->sortable()
            ])
            ->recordActions([
                Action::make('edit')
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->action(
                        fn(Permission $record, $livewire) => $livewire->edit(modal: 'modal-edit-permission', id: $record->getKey())
                    ),
                // ->url(fn(Karyawan $record): string => route('karyawan.edit', $record))
                Action::make('delete')
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->color('danger')
                    ->action(
                        fn(Permission $record, $livewire) => $livewire->delete(id: $record->getKey())
                    ),
            ]);;
    }

    private function edit($modal, $id)
    {
        $this->selectedPermission = $id;
        $this->dispatch('open-modal', id: $modal);
    }

    private function delete($id)
    {
        $this->dialog()
            ->question('Hapus Permission ?')
            ->confirm('Hapus', 'confirmDeletePermission', $id)
            ->cancel('Batal', 'cancelDeletePermission', $id)
            ->send();
    }

    function confirmDeletePermission($id)
    {
        $permission = Permission::findOrFail($id);

        DB::beginTransaction();
        try {
            $permission->delete();

            DB::commit();

            $this->dispatch('permission-deleted');
            $this->toast()
                ->success('Berhasil', 'Hapus permission berhasil.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Failed', $e->getMessage())
                ->send();
        }
    }

    function cancelDeletePermission($id)
    {
        $this->toast()
            ->info("Dibatalkan", 'Hapus data dibatalkan.')
            ->send();
    }

    public function render()
    {
        return view('livewire.settings.permission.permission-table');
    }
}
