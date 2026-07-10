<?php

namespace App\Livewire\Master\RuanganShift;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Throwable;
use Livewire\Component;
use App\Models\Sdm\RuanganShift;
use App\Traits\AuthorizesFromRoute;
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
#[Title('Shift per Unit Kerja (Ruangan)')]
class Index extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use AuthorizesFromRoute;
    use InteractsWithForms, InteractsWithTable;
    use Interactions;

    public ?int $editingId = null;

    protected $listeners = ['ruangan-shift-updated' => '$refresh', 'new-ruangan-shift-created' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->query(RuanganShift::query()->with(['ruangan', 'shift']))
            ->columns([
                TextColumn::make('ruangan.nama')->label('Unit Kerja / Ruangan')->searchable()->sortable(),
                TextColumn::make('shift.nama')->label('Shift')->searchable()->sortable(),
                TextColumn::make('jam_masuk_efektif')->label('Jam Masuk')
                    ->badge()
                    ->color(fn ($record) => $record->jam_masuk_override ? 'warning' : 'success'),
                TextColumn::make('jam_keluar_efektif')->label('Jam Keluar')
                    ->badge()
                    ->color(fn ($record) => $record->jam_keluar_override ? 'warning' : 'success'),
                TextColumn::make('toleransi_telat_menit_override')->label('Toleransi (Ovr)')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(fn ($record) => $record->toleransi_telat_menit_override ? $record->toleransi_telat_menit_override . ' mnt' : '-'),
            ])
            ->recordActions([
                Action::make('edit')
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->color('warning')
                    ->action(function (RuanganShift $record, $livewire) {
                        $livewire->editingId = $record->id;
                        $livewire->dispatch('open-modal', id: 'edit-ruangan-shift');
                    }),
                Action::make('delete')
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->color('danger')
                    ->action(
                        fn(RuanganShift $record, $livewire) => $livewire->delete($record->getKey())
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
        $record = RuanganShift::findOrFail($id);

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
        return view('livewire.master.ruangan-shift.index');
    }
}
