<?php

namespace App\Livewire\StokOpname;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Throwable;
use Carbon\Carbon;
use Livewire\Component;
use Filament\Tables\Table;
use App\Exports\OpnameHasil;
use App\Models\Gudang\OpnameStok;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use TallStackUi\Traits\Interactions;
use Filament\Forms\Contracts\HasForms;
use App\Models\Gudang\OpnameStokDetail;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class TablePelaksanaan extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use Interactions;
    use InteractsWithTable, InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table->query(
            OpnameStok::query()->latest()
        )
            ->striped()
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal'),

                TextColumn::make('user_pj')
                    ->label('Penanggung Jawab'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            'process' => 'warning',
                            'investigating' => 'primary',
                            'completed' => 'success'
                        }
                    )
                    ->formatStateUsing(
                        fn($state) => match ($state) {
                            'process' => 'Berlangsung',
                            'investigating' => 'Sedang Investigasi',
                            'completed' => 'Selesai'
                        }
                    ),

                TextColumn::make('user_selesai')
                    ->label('Diselesaikan'),

                TextColumn::make('user_validasi')
                    ->label('Validator')

            ])
            ->recordActions([

                Action::make('input-so')
                    ->iconButton()
                    ->icon('tabler-list-check')
                    ->color('primary')
                    ->action(
                        fn($record) => $this->modal(
                            modal: 'modal-so-input',
                            id: $record->getKey()
                        )
                    )
                    ->visible(
                        fn($record) => ($record->status === 'process')
                    ),

                Action::make('selesaikan_so')
                    ->iconButton()
                    ->icon('tabler-circle-check')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Yakin Menyelesaikan Stok Opname ?')
                    ->modalDescription('Kegiatan stok opname akan ditutup, dan nilai stok opname akan disimpan sebagai draft mutasi untuk adjustment (penyesuaian).')
                    ->modalSubmitActionLabel('Oke !')
                    ->action(
                        function ($record) {
                            DB::beginTransaction();
                            try {
                                // update as draft mutasi
                                $record->status = 'investigating';
                                $record->selesai_by = auth()->user()->id;
                                $record->selesai = date('Y-m-d H:i:s');
                                $record->save();


                                // data selisih not null, send to stok_mutasi and posted is 0

                                DB::commit();

                                $this->toast()
                                    ->success('Berhasil.', "Stok opname selesai, silahkan lakukan investigasi.")
                                    ->send();
                            } catch (Throwable $e) {
                                DB::rollBack();
                                $this->toast()
                                    ->error('Tidak Berhasil.', "<i>{$e->getMessage()}</i>")
                                    ->send();
                            }
                        }
                    )
                    ->visible(
                        function ($record) {
                            $user = auth()->user();
                            return $user->can('finish-opname-gudang') and $record->status === 'process';
                        }
                    ),

                Action::make('adjustment')
                    ->iconButton()
                    ->icon('tabler-checklist')
                    ->action(
                        fn($record) => $this->modal(
                            modal: 'modal-so-investigasi',
                            id: $record->getKey(),
                        )
                    )
                    ->visible(
                        fn($record) => $record->status === 'investigating'
                    ),


                Action::make('report')
                    ->iconButton()
                    ->icon('tabler-file-download')
                    ->color('gray')
                    ->action(
                        function ($record) {
                            $tanggal = Carbon::parse($record->created_at)->locale('ID')->translatedFormat('d M Y');

                            return  Excel::download(
                                new OpnameHasil($record->getKey()),
                                "Stok Opname {$tanggal}.xlsx"
                            );
                        }
                    )
                    ->visible(
                        fn($record) => $record->status === 'completed'
                    ),
            ]);
    }

    public int $selectedId;

    public function modal($modal, $id): void
    {
        $this->selectedId = $id;
        $this->dispatch('open-modal', id: $modal);
    }

    private function selesaikan(int $id)
    {
        DB::beginTransaction();
        try {
            OpnameStok::where('id', $id)
                ->update([
                    'status' => 'completed',
                    'selesai_by' => auth()->user()->id,
                    'selesai' => date('Y-m-d H:i:s')
                ]);

            DB::commit();

            $this->toast()
                ->success('Berhasil', 'Kegiatan stok opanme telah selesai.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Tidak Berhasil', "<i>{$e->getMessage()}</i>")
                ->send();
        }
    }


    public function render()
    {
        return view('livewire.stok-opname.table-pelaksanaan');
    }
}
