<?php

namespace App\Livewire\Surat\BalasanPenelitian;

use App\Enums\StatusApproval;
use App\Models\Surat\SuratBalasanPenelitian;
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

class TableBalasanPenelitian extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions, InteractsWithTable, InteractsWithForms;

    #[Locked]
    public ?SuratBalasanPenelitian $suratBalasanPenelitian = null;

    public ?string $filterStatus = null;

    public function table(Table $table): Table
    {
        $userLogin = auth()->user();

        return $table
            ->query(
                SuratBalasanPenelitian::with(['mahasiswa', 'biaya', 'createdBy.karyawan', 'direktur', 'jabatan'])
                    ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
                    ->latest()
            )
            ->columns([
                TextColumn::make('no')
                    ->label('Nomor Surat')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('tgl')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('tujuan_universitas')
                    ->label('Universitas')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('tujuan_fakultas')
                    ->label('Fakultas')
                    ->searchable(),

                TextColumn::make('perihal_surat_masuk')
                    ->label('Perihal')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('mahasiswa_count')
                    ->label('Peneliti')
                    ->counts('mahasiswa')
                    ->formatStateUsing(fn($state) => "{$state} Orang"),

                TextColumn::make('total_biaya')
                    ->label('Total Biaya')
                    ->money('IDR'),

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
                    ->tooltip('Detail & Cetak Surat')
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            modal: 'modal-detail-balasan-penelitian',
                            id: $record->getKey()
                        )
                    ),

                Action::make('pdf')
                    ->icon('tabler-file-type-pdf')
                    ->iconButton()
                    ->color('danger')
                    ->tooltip('Unduh Dokumen PDF')
                    ->url(fn(SuratBalasanPenelitian $record) => route('kepegawaian.surat.balasan-penelitian.pdf', $record->getKey()))
                    ->openUrlInNewTab(),

                Action::make('approval')
                    ->icon('tabler-file-check')
                    ->iconButton()
                    ->color('success')
                    ->tooltip('Persetujuan Direktur')
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            modal: 'modal-approval-balasan-penelitian',
                            id: $record->getKey()
                        )
                    )
                    ->visible(function (SuratBalasanPenelitian $record) use ($userLogin) {
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
                    ->tooltip('Batalkan Surat')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Surat Balasan Penelitian')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan surat ini?')
                    ->action(function (SuratBalasanPenelitian $record) {
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
                    ->visible(function (SuratBalasanPenelitian $record) use ($userLogin) {
                        return $record->status === StatusApproval::PENDING && $userLogin->hasRole('Super-Admin');
                    }),
            ]);
    }

    public function openModal(string $modal, int $id)
    {
        $this->suratBalasanPenelitian = SuratBalasanPenelitian::findOrFail($id);
        $this->dispatch('open-modal', id: $modal);
    }

    #[On('refresh-table-balasan-penelitian')]
    #[On('update-approval')]
    public function refreshTable()
    {
        $this->resetTable();
    }

    public function render()
    {
        return view('livewire.surat.balasan-penelitian.table-balasan-penelitian');
    }
}
