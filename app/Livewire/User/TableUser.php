<?php

namespace App\Livewire\User;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use App\Models\User;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class TableUser extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;

    public ?User $user;

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query())
            ->striped()
            ->columns([
                TextColumn::make('karyawan.nama')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->copyable()
                    ->searchable(),
                TextColumn::make('roles')
                    ->label('Role')
                    ->formatStateUsing(
                        function ($record) {
                            $role = $record->roles?->pluck('name')->implode(', ');
                            $superAdmin = '<span class="py-0.5 px-1 text-xs bg-red-200/50 text-red-500 rounded-md border border-p-2 border-red-500">Super Admin</span>';
                            return ($role == 'Super-Admin') ? $superAdmin : $role;
                        }
                    )
                    ->html(),
                TextColumn::make('created_at')
                    ->label('Tgl Registrasi')
                    ->dateTime(format: 'd M Y H:i:s'),
            ])
            ->recordActions([
                Action::make('Role')
                    ->icon('tabler-circle-key')
                    ->color('primary')
                    ->action(
                        fn(User $user, $livewire) => $livewire->setRole(modal: 'set-user-role', id: $user->getKey())
                    )
                    ->visible(
                        fn() => auth()->user()->can('edit-user')
                    ),


                Action::make('Permission')
                    ->icon('tabler-key')
                    ->color('primary')
                    ->action(
                        fn(User $user, $livewire) => $livewire->permission(modal: 'edit-user-permission', id: $user->getKey())
                    )
                    ->visible(
                        fn() => auth()->user()->can('edit-user')
                    ),

                Action::make('Disable')
                    ->icon('tabler-user-cancel')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (User $user, $livewire): void {
                        $livewire->disableUser(id: $user->getKey());
                    })
                    ->visible(
                        fn() => auth()->user()->can('delete-user')
                    ),

                Action::make('Logs')
                    ->icon('tabler-history')
                    ->color('primary')
                    ->action(function (User $user, $livewire): void {
                        $livewire->logsUser($user->getKey());
                    }),
            ]);
    }

    function disableUser($id)
    {
        #Todo
    }

    function setRole($modal, $id)
    {
        $this->user = User::findOrFail($id);
        $this->dispatch('open-modal', id: $modal);
    }

    function permission($modal, $id)
    {
        $this->user = User::findOrFail($id);
        $this->dispatch('open-modal', id: $modal);
    }

    public function logsUser($id)
    {
        #Todo
    }

    public function render()
    {
        return view('livewire.user.table-user');
    }
}
