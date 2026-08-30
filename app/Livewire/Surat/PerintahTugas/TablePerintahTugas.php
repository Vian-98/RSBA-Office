<?php

namespace App\Livewire\Surat\PerintahTugas;

use App\Enums\StatusApproval;
use App\Models\Surat\SuratPerintahTugas;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class TablePerintahTugas extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions, InteractsWithTable, InteractsWithForms;

    #[Locked]
    public ?SuratPerintahTugas $suratPerintahTugas = null;

    public ?string $filterStatus = null;

    public function table(Table $table): Table
    {
        $userLogin = auth()->user();

        return $table
            ->query(
                SuratPerintahTugas::with(['karyawanTugas.karyawan.jabatan', 'createdBy.karyawan', 'direktur', 'jabatan'])
                    ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
                    ->latest()
            )
            ->columns([
                TextColumn::make('no')
                    ->label('Nomor SPT')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('tgl')
                    ->label('Tanggal Surat')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('perihal')
                    ->label('Perihal / Tugas')
                    ->limit(65)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('hari_tanggal')
                    ->label('Pelaksanaan')
                    ->formatStateUsing(fn($record) => "{$record->hari_tanggal} ({$record->tempat})"),

                TextColumn::make('karyawan_tugas_count')
                    ->label('Karyawan Ditugaskan')
                    ->counts('karyawanTugas')
                    ->formatStateUsing(fn($state) => "{$state} Orang"),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(StatusApproval $state) => $state->nama())
                    ->color(fn(StatusApproval $state) => $state->color())
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->iconButton()
                    ->icon('tabler-file-description')
                    ->tooltip('Detail & Cetak SPT')
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            modal: 'modal-detail-perintah-tugas',
                            id: $record->getKey()
                        )
                    ),

                Action::make('pdf')
                    ->icon('tabler-file-type-pdf')
                    ->iconButton()
                    ->color('danger')
                    ->tooltip('Unduh Dokumen PDF')
                    ->url(fn(SuratPerintahTugas $record) => route('kepegawaian.surat.perintah-tugas.pdf', $record->getKey()))
                    ->openUrlInNewTab(),

                Action::make('approval')
                    ->icon('tabler-file-check')
                    ->iconButton()
                    ->color('success')
                    ->tooltip('Persetujuan Direktur')
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            modal: 'modal-approval-perintah-tugas',
                            id: $record->getKey()
                        )
                    )
                    ->visible(function (SuratPerintahTugas $record) use ($userLogin) {
                        $isPending = $record->status === StatusApproval::PENDING;
                        $isDirektur = $userLogin->hasRole('Super-Admin')
                            || ($userLogin->karyawan && $record->disetujui_oleh == $userLogin->karyawan->id)
                            || ($userLogin->karyawan && $record->jabatan_id === optional($userLogin->karyawan->jabatan->first())->id);

                        return $isPending && $isDirektur;
                    }),

                Action::make('cancel')
                    ->icon('tabler-ban')
                    ->iconButton()
                    ->color('danger')
                    ->tooltip('Batalkan Surat Perintah Tugas')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Surat Perintah Tugas')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan surat perintah tugas ini?')
                    ->action(function (SuratPerintahTugas $record) {
                        $record->update([
                            'status' => StatusApproval::CANCELLED,
                        ]);

                        if (class_exists(\App\Services\DocstoreSyncService::class)) {
                            try {
                                $sync = app(\App\Services\DocstoreSyncService::class);
                                $sync->syncDocument($record);
                                if ($record->docstore_key) {
                                    $sync->invalidateCache($record->docstore_key);
                                }
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::warning('Docstore cancellation sync failed: ' . $e->getMessage());
                            }
                        }
                    })
                    ->visible(function (SuratPerintahTugas $record) use ($userLogin) {
                        return $record->status === StatusApproval::PENDING && $userLogin->hasRole('Super-Admin');
                    }),
            ]);
    }

    public function openModal(string $modal, int $id)
    {
        $this->suratPerintahTugas = SuratPerintahTugas::findOrFail($id);
        $this->dispatch('open-modal', id: $modal);
    }

    #[On('refresh-table-perintah-tugas')]
    #[On('update-approval')]
    public function refreshTable()
    {
        $this->resetTable();
    }

    public function render()
    {
        return view('livewire.surat.perintah-tugas.table-perintah-tugas');
    }
}
