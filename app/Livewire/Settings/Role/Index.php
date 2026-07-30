<?php

namespace App\Livewire\Settings\Role;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Throwable;
use App\Traits\AuthorizesFromRoute;
use Livewire\Component;
use Filament\Tables\Table;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Spatie\Permission\Models\Role;
use TallStackUi\Traits\Interactions;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;


#[Title('Role')]
#[Lazy]
class Index extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use AuthorizesFromRoute;
    use InteractsWithTable, InteractsWithForms;
    use Interactions;

    public ?Role $role;

    function table(Table $table): Table
    {
        return $table
            ->query(Role::query())
            ->columns([
                TextColumn::make('id')
                    ->label('Id'),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guard_name')
                    ->label('Guard')
            ])
            ->recordActions([
                Action::make('Permission')
                    ->icon('tabler-circle-key')
                    ->action(
                        fn(Role $role, $livewire) => $livewire->setPermission(modal: 'set-permission', id: $role->getKey())
                    ),
                Action::make('Hapus')
                    ->icon('tabler-trash')
                    ->color('danger')
                    ->action(
                        fn(Role $role, $livewire) => $livewire->delete(id: $role->getKey())
                    )
            ]);
    }

    function setPermission($modal, $id)
    {
        $this->role = Role::findOrFail($id);
        $this->dispatch('open-modal', id: $modal);
    }


    function delete($id)
    {
        $role = Role::findOrFail($id);

        $this->dialog()
            ->question('Warning!', "Yakin hapus <b>$role->name</b> ?")
            ->confirm('Hapus', 'confirmed', $id)
            ->cancel('Batal', 'cancelled')
            ->send();
    }

    function confirmed($id)
    {
        $role = Role::findOrFail($id);
        try {
            $role->delete();

            $this->toast()
                ->success('Berhasil', "<b>$role->nama</b>  berhasil dihapus.")
                ->send();
        } catch (Throwable $th) {
            $this->toast()
                ->error('Failed', "Error : " . $th->getMessage())
                ->send();
        }
    }

    function cancelled()
    {
        $this->toast()
            ->info('Dibatalkan', 'Hapus role dibatalkan.')
            ->send();
    }

    public function render()
    {
        // $this->authorize('view-roles');
        $this->authorizeFromRoute();
        return view('livewire.settings.role.index');
    }
}
