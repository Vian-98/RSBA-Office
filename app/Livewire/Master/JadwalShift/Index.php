<?php

namespace App\Livewire\Master\JadwalShift;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Throwable;
use Livewire\Component;
use App\Models\Sdm\JadwalShift;
use App\Traits\AuthorizesFromRoute;
use Filament\Tables\Table;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use TallStackUi\Traits\Interactions;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

#[Lazy]
#[Title('Master Jadwal Shift')]
class Index extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use AuthorizesFromRoute;
    use InteractsWithForms, InteractsWithTable;
    use Interactions;

    public ?int $editingId = null;

    protected $listeners = ['jadwal-shift-updated' => '$refresh', 'new-jadwal-shift-created' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->query(JadwalShift::query())
            ->columns([
                TextColumn::make('kode')->searchable()->sortable(),
                TextColumn::make('nama')->searchable()->sortable(),
                TextColumn::make('jam_masuk')->time('H:i')->sortable(),
                TextColumn::make('jam_keluar')->time('H:i')->sortable(),
                TextColumn::make('toleransi_telat_menit')->label('Toleransi Telat (m)'),
                ColorColumn::make('warna'),
                IconColumn::make('lintas_hari')->boolean(),
                IconColumn::make('aktif')->boolean(),
            ])
            ->recordActions([
                Action::make('edit')
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->color('warning')
                    ->action(function (JadwalShift $record, $livewire) {
                        $livewire->editingId = $record->id;
                        $livewire->dispatch('open-modal', id: 'edit-jadwal-shift');
                    }),
                Action::make('delete')
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->color('danger')
                    ->action(
                        fn(JadwalShift $record, $livewire) => $livewire->delete($record->getKey())
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
        $shift = JadwalShift::findOrFail($id);

        try {
            $shift->delete();
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
        return view('livewire.master.jadwal-shift.index');
    }
}
