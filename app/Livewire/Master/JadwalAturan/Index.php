<?php

namespace App\Livewire\Master\JadwalAturan;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Throwable;
use Livewire\Component;
use App\Models\Sdm\JadwalAturan;
use App\Traits\AuthorizesFromRoute;
use Filament\Tables\Table;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use TallStackUi\Traits\Interactions;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Enums\KodeAturanJadwal;

#[Lazy]
#[Title('Master Aturan Jadwal')]
class Index extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use AuthorizesFromRoute;
    use InteractsWithForms, InteractsWithTable;
    use Interactions;

    public ?int $editingId = null;

    protected $listeners = ['jadwal-aturan-updated' => '$refresh', 'new-jadwal-aturan-created' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->query(JadwalAturan::query()->with('bagian'))
            ->columns([
                TextColumn::make('bagian.nama')->label('Bagian')->searchable()->sortable(),
                TextColumn::make('kode')->label('Aturan')->formatStateUsing(fn (string $state) => KodeAturanJadwal::tryFrom($state)?->nama() ?? $state)->searchable()->sortable(),
                TextColumn::make('nilai')->label('Nilai')->searchable(),
                IconColumn::make('aktif')->boolean(),
            ])
            ->recordActions([
                Action::make('edit')
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->color('warning')
                    ->action(function (JadwalAturan $record, $livewire) {
                        $livewire->editingId = $record->id;
                        $livewire->dispatch('open-modal', id: 'edit-jadwal-aturan');
                    }),
                Action::make('delete')
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->color('danger')
                    ->action(
                        fn(JadwalAturan $record, $livewire) => $livewire->delete($record->getKey())
                    )
            ]);
    }

    public function delete($id)
    {
        $this->dialog()
            ->question('Warning !', "Yakin hapus data ?")
            ->confirm('Hapus', 'confirmhapus', $id)
            ->cancel('Batal', 'cancelhapus')
            ->send();
    }

    public function confirmhapus($id)
    {
        $record = JadwalAturan::findOrFail($id);

        try {
            $record->delete();
            $this->toast()->success('Berhasil', 'Data berhasil dihapus.')->send();
        } catch (Throwable $th) {
            $this->toast()->error('Failed', 'Error : ' . $th->getMessage())->send();
        }
    }

    public function cancelhapus()
    {
        $this->toast()->info('Dibatalkan', 'Hapus data dibatalkan.')->send();
    }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.master.jadwal-aturan.index');
    }
}
