<?php

namespace App\Livewire\Karyawan\Dokter;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Throwable;
use Livewire\Component;
use Filament\Tables\Table;
use App\Enums\StatusKaryawan;
use App\Models\Sdm\Dokter;
use App\Models\Sdm\DokterSpesialisasi;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\SelectFilter;
use TallStackUi\Traits\Interactions;

class TableDokter extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;
    use Interactions;

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Dokter::query()->with(['karyawan', 'spesialis'])
                    ->whereHas('karyawan', fn($query) => $query->where('resign', null))
            )
            ->columns([
                TextColumn::make('karyawan.nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('karyawan.status')
                    ->label('Status Pegawai')
                    ->formatStateUsing(fn(StatusKaryawan $state) => $state->nama())
                    ->badge()
                    ->color(fn(StatusKaryawan $state) => $state->color()),

                TextColumn::make('karyawan.latestJabatan.jabatan.nama')
                    ->label('Jabatan')
                    ->default('-'),

                TextColumn::make('spesialis.nama')
                    ->label('Sub Spesialis')
            ])
            ->filters([
                SelectFilter::make('spesialis_id')
                    ->label('Sub Spesialis')
                    ->options(
                        fn() => DokterSpesialisasi::pluck('nama', 'id')->toArray()
                    )->searchable()

            ])
            ->recordActions([
                Action::make('edit')
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->color('danger')
                    ->tooltip('Edit Data Dokter')
                    ->url(fn(Dokter $record): string => route('kepegawaian.karyawan.edit', $record->karyawan_id)),

                Action::make('jadwal')
                    ->iconButton()
                    ->icon('tabler-calendar')
                    ->tooltip('Jadwal')
                    ->color('warning')
                    ->url(fn(Dokter $record): string => route('kepegawaian.jadwal-kerja.index')),

                Action::make('koor-ruangan')
                    ->iconButton()
                    ->icon('tabler-building-hospital')
                    ->tooltip('Atur Ruangan Koordinasi')
                    ->color('info')
                    ->visible(
                        fn() => auth()->user()->hasAnyRole(['Super-Admin', 'Staff-SDM', 'Kepala-Bidang', 'Wakil-Direktur'])
                    )
                    ->action(fn(Dokter $dokter, $livewire) => $livewire->openKoorRuangan(
                        id: $dokter->getKey()
                    )),

                Action::make('koor-ruangan')
                    ->iconButton()
                    ->icon('tabler-building-hospital')
                    ->tooltip('Atur Ruangan Koordinasi')
                    ->color('info')
                    ->visible(
                        fn() => auth()->user()->hasAnyRole(['Super-Admin', 'Staff-SDM', 'Kepala-Bidang', 'Wakil-Direktur'])
                    )
                    ->action(fn(Dokter $dokter, $livewire) => $livewire->openKoorRuangan(
                        id: $dokter->getKey()
                    )),

                Action::make('koor-ruangan')
                    ->iconButton()
                    ->icon('tabler-building-hospital')
                    ->tooltip('Atur Ruangan Koordinasi')
                    ->color('info')
                    ->visible(
                        fn() => auth()->user()->hasAnyRole(['Super-Admin', 'Staff-SDM', 'Kepala-Bidang', 'Wakil-Direktur'])
                    )
                    ->action(fn(Dokter $dokter, $livewire) => $livewire->openKoorRuangan(
                        id: $dokter->getKey()
                    )),

                Action::make('koor-ruangan')
                    ->iconButton()
                    ->icon('tabler-building-hospital')
                    ->tooltip('Atur Ruangan Koordinasi')
                    ->color('info')
                    ->visible(
                        fn() => auth()->user()->hasAnyRole(['Super-Admin', 'Staff-SDM', 'Kepala-Bidang', 'Wakil-Direktur'])
                    )
                    ->action(fn(Dokter $dokter, $livewire) => $livewire->openKoorRuangan(
                        id: $dokter->getKey()
                    )),

                Action::make('koor-ruangan')
                    ->iconButton()
                    ->icon('tabler-building-hospital')
                    ->tooltip('Atur Ruangan Koordinasi')
                    ->color('info')
                    ->visible(
                        fn() => auth()->user()->hasAnyRole(['Super-Admin', 'Staff-SDM', 'Kepala-Bidang', 'Wakil-Direktur'])
                    )
                    ->action(fn(Dokter $dokter, $livewire) => $livewire->openKoorRuangan(
                        id: $dokter->getKey()
                    )),

                Action::make('koor-ruangan')
                    ->iconButton()
                    ->icon('tabler-building-hospital')
                    ->tooltip('Atur Ruangan Koordinasi')
                    ->color('info')
                    ->visible(
                        fn() => auth()->user()->hasAnyRole(['Super-Admin', 'Staff-SDM', 'Kepala-Bidang', 'Wakil-Direktur'])
                    )
                    ->action(fn(Dokter $dokter, $livewire) => $livewire->openKoorRuangan(
                        id: $dokter->getKey()
                    )),

                Action::make('koor-ruangan')
                    ->iconButton()
                    ->icon('tabler-building-hospital')
                    ->tooltip('Atur Ruangan Koordinasi')
                    ->color('info')
                    ->visible(
                        fn() => auth()->user()->hasAnyRole(['Super-Admin', 'Staff-SDM', 'Kepala-Bidang', 'Wakil-Direktur'])
                    )
                    ->action(fn(Dokter $dokter, $livewire) => $livewire->openKoorRuangan(
                        id: $dokter->getKey()
                    )),

                Action::make('koor-ruangan')
                    ->iconButton()
                    ->icon('tabler-building-hospital')
                    ->tooltip('Atur Ruangan Koordinasi')
                    ->color('info')
                    ->visible(
                        fn() => auth()->user()->hasAnyRole(['Super-Admin', 'Staff-SDM', 'Kepala-Bidang', 'Wakil-Direktur'])
                    )
                    ->action(fn(Dokter $dokter, $livewire) => $livewire->openKoorRuangan(
                        id: $dokter->getKey()
                    )),

                Action::make('delete')
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->color('danger')
                    ->action(fn(Dokter $dokter, $livewire) => $livewire->delete(
                        id: $dokter->getKey()
                    )),
            ]);
    }

    function openKoorRuangan($id)
    {
        $dokter = Dokter::find($id);
        $this->dispatch('load-koor-ruangan', karyawanId: $dokter?->karyawan_id, dokterId: $id);
        $this->dispatch('open-modal', id: 'modal-koor-ruangan');
    }

    function delete($id)
    {
        $dokter = Dokter::findOrFail($id);

        $this->dialog()
            ->question('Warning!', "Yakin hapus <b>{$dokter->karyawan->nama}</b> dari dokter. ?")
            ->confirm('Hapus', 'confirmed', $id)
            ->cancel('Batal', 'cancelled')
            ->send();
    }

    function confirmed($id)
    {
        $dokter = Dokter::findOrFail($id);
        try {
            $dokter->delete();

            $this->toast()
                ->success('Berhasil', "<b>{$dokter->karyawan->nama}</b> berhasil dihapus.")
                ->send();
        } catch (Throwable $th) {
            $this->toast()
                ->error('Gagal', "Error : " . $th->getMessage())
                ->send();
        }
    }

    function cancelled()
    {
        $this->toast()
            ->info('Dibatalkan', 'Hapus data dokter dibatalkan.')
            ->send();
    }


    public function render()
    {
        return view('livewire.karyawan.dokter.table-dokter');
    }
}
