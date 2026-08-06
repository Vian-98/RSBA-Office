<?php

namespace App\Livewire\Master\BagianKoordinator;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Throwable;
use Livewire\Component;
use App\Models\Sdm\RuanganKoordinator;
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

use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

#[Lazy]
#[Title('Master Koordinator Ruangan')]
class Index extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use AuthorizesFromRoute;
    use InteractsWithForms, InteractsWithTable;
    use Interactions;

    public ?int $editingId = null;

    protected $listeners = ['ruangan-koordinator-updated' => '$refresh', 'new-ruangan-koordinator-created' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->query(RuanganKoordinator::query()->with(['ruangan', 'karyawan.dokterRecord.spesialis', 'user']))
            ->columns([
                TextColumn::make('ruangan.nama')->label('Ruangan')->searchable()->sortable(),
                TextColumn::make('karyawan.nama')
                    ->label('Koordinator (Karyawan)')
                    ->formatStateUsing(fn(RuanganKoordinator $record) => $record->karyawan?->full_nama ?? '-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipe_koordinator')
                    ->label('Tipe')
                    ->badge()
                    ->getStateUsing(fn(RuanganKoordinator $record) => $record->karyawan?->dokterRecord ? 'Dokter' : 'Non-Dokter')
                    ->color(fn(string $state): string => match ($state) {
                        'Dokter' => 'info',
                        'Non-Dokter' => 'gray',
                    }),
                TextColumn::make('user.email')->label('Akun Login')->placeholder('-')->searchable()->sortable(),
                IconColumn::make('aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('tipe_koordinator')
                    ->label('Tipe Koordinator')
                    ->options([
                        'dokter' => 'Dokter',
                        'non_dokter' => 'Non-Dokter',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        if ($value === 'dokter') {
                            return $query->whereHas('karyawan.dokterRecord');
                        }
                        if ($value === 'non_dokter') {
                            return $query->whereDoesntHave('karyawan.dokterRecord');
                        }
                    })
            ])
            ->recordActions([
                Action::make('koor-ruangan')
                    ->iconButton()
                    ->icon('tabler-building-hospital')
                    ->tooltip('Atur Ruangan Koordinasi (Multi-Ruangan)')
                    ->color('info')
                    ->action(function (RuanganKoordinator $record, $livewire) {
                        $livewire->dispatch('load-koor-ruangan', karyawanId: $record->karyawan_id);
                        $livewire->dispatch('open-modal', id: 'modal-koor-ruangan');
                    }),
                Action::make('edit')
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->color('warning')
                    ->action(function (RuanganKoordinator $record, $livewire) {
                        $livewire->editingId = $record->id;
                        $livewire->dispatch('open-modal', id: 'edit-ruangan-koordinator');
                    }),
                Action::make('delete')
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->color('danger')
                    ->action(
                        fn(RuanganKoordinator $record, $livewire) => $livewire->delete($record->getKey())
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
        $record = RuanganKoordinator::findOrFail($id);

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
        return view('livewire.master.bagian-koordinator.index');
    }
}
