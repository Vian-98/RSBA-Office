<?php

namespace App\Livewire\Master\Jabatan;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Throwable;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\Sdm\Jabatan;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class JabatanTable extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use Interactions;
    use InteractsWithTable, InteractsWithForms;

    public ?Jabatan $jabatan;

    public static function table(Table $table): Table
    {
        return $table
            ->query(Jabatan::query()->orderByDesc('tunjangan_jabatan')->orderBy('id'))
            ->columns([
                TextColumn::make('nama')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kode_surat')
                    ->label('Kode Surat'),
                TextColumn::make('parent_id')
                    ->label('Atasan')
                    ->getStateUsing(function (Jabatan $record) {
                        return $record->atasan->nama ?? '-';
                    }),
                TextColumn::make('bagian.nama')
                    ->label('Bagian')
            ])

            ->filters([
                SelectFilter::make('parent_id')
                    ->label('Atasan')
                    ->options(
                        fn(): array => Jabatan::orderByDesc('tunjangan_jabatan')->pluck('nama', 'id')->toArray()
                    )
            ])

            ->recordActions([
                Action::make('edit')
                    ->iconButton()
                    ->tooltip('Edit')
                    ->icon('tabler-edit')
                    ->action(
                        fn(Jabatan $jabatan, $livewire) => $livewire->edit(modal: 'edit-jabatan', id: $jabatan->getKey())
                    ),
                Action::make('delete')
                    ->iconButton()
                    ->tooltip('Hapus')
                    ->icon('tabler-trash')
                    ->color('danger')
                    ->action(
                        fn(Jabatan $jabatan, $livewire) => $livewire->delete(id: $jabatan->getKey())
                    ),
            ]);
    }

    public function edit($modal, $id)
    {
        $this->jabatan = Jabatan::findOrFail($id);
        $this->dispatch('open-modal', id: $modal);
    }


    function delete($id)
    {
        $this->jabatan = Jabatan::findOrFail($id);
        $this->dialog()
            ->question('Warning!', "Yakin hapus <b>" . $this->jabatan->nama . "</b> ?")
            ->confirm('Hapus', 'confirmedDelete', $id)
            ->cancel('Batal', 'cancelledDelete')
            ->send();
    }

    // confirmed delete
    function confirmedDelete($id)
    {
        $this->jabatan = Jabatan::findOrFail($id);
        DB::beginTransaction();
        try {
            $this->jabatan->delete();

            DB::commit();
            $this->toast()
                ->success('Berhasil', "<b>" . $this->jabatan->nama . "</b>  berhasil dihapus.")
                ->send();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast()
                ->error('Failed', "Error : " . $th->getMessage())
                ->send();
        }
    }

    // canceled delete
    function cancelledDelete()
    {
        $this->toast()
            ->info('Dibatalkan', 'Hapus data karyawan dibatalkan.')
            ->send();
    }

    public function render()
    {
        return view('livewire.master.jabatan.jabatan-table');
    }
}
