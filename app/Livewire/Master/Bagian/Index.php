<?php

namespace App\Livewire\Master\Bagian;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Throwable;
use Livewire\Component;
use App\Models\Sdm\Bagian;
use App\Traits\AuthorizesFromRoute;
use Filament\Tables\Table;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use TallStackUi\Traits\Interactions;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

#[Lazy]
#[Title('Bagian')]
class Index extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use AuthorizesFromRoute;
    use InteractsWithForms, InteractsWithTable;
    use Interactions;

    function table(Table $table): Table
    {
        return $table
            ->query(Bagian::query())
            ->columns([
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('group')
                    ->label('Group')
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'manajemen' => 'Manajemen',
                        'medis' => 'Medis',
                        'penunjang' => 'Penunjang',
                        'non_medis' => 'Non Medis',
                        default => ucwords($state)
                    }),
                TextColumn::make('is_active')
                    ->label('Aktif')
                    ->formatStateUsing(fn($state) => match ($state) {
                        1 => 'Aktif',
                        0 => 'Tidak Aktif'
                    }),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->label('Group')
                    ->options([
                        'man' => 'Manajemen',
                        'medis' => 'Medis',
                        'penunjang' => 'Penunjang Medis',
                        'non_medis' => 'Non Medis'
                    ])
            ])
            ->recordActions([
                Action::make('delete')
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->color('danger')
                    ->action(
                        fn(Bagian $record, $livewire) => $livewire->delete($record->getKey())
                    )
            ]);
    }

    function delete($id)
    {

        $this->dialog()
            ->question('Warning !', "Yakin hapus data ?")
            ->confirm('Hapus', 'confirmhapus', $id)
            ->cancel('Batal', 'cancelhapus')
            ->send();
    }

    function confirmhapus($id)
    {
        $bagian = Bagian::findOrFail($id);

        try {
            $bagian->delete();

            $this->toast()
                ->success('Berhasil', 'Data berhasil dihapus.')
                ->send();
        } catch (Throwable $th) {
            $this->toast()
                ->success('Failed', 'Error : ' . $th->getMessage())
                ->send();
        }
    }


    function cancelhapus()
    {
        $this->toast()
            ->info('Dibatalkan', 'Hapus data dibatalkan.')
            ->send();
    }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.master.bagian.index');
    }
}
