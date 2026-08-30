<?php

namespace App\Livewire\Surat\BalasanPkl;

use App\Enums\StatusApproval;
use App\Models\Surat\SuratBalasanPkl;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class TableBalasanPkl extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions, InteractsWithTable, InteractsWithForms;

    #[Locked]
    public ?SuratBalasanPkl $suratBalasanPkl = null;

    public ?string $filterStatus = null;

    public function table(Table $table): Table
    {
        $userLogin = auth()->user();

        return $table
            ->query(
                SuratBalasanPkl::with(['mahasiswa', 'createdBy.karyawan', 'direktur', 'jabatan'])
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

                TextColumn::make('display_universitas')
                    ->label('Universitas')
                    ->searchable(['tujuan_universitas'])
                    ->wrap(),

                TextColumn::make('prodi')
                    ->label('Program Studi')
                    ->searchable(),

                TextColumn::make('jumlah_mahasiswa')
                    ->label('Jumlah Mhs')
                    ->formatStateUsing(fn($record) => "{$record->jumlah_mahasiswa} Orang ({$record->lama_praktik_bulan} Bln)"),

                TextColumn::make('grand_total_biaya')
                    ->label('Total Biaya')
                    ->money('IDR'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(StatusApproval $state) => $state->nama())
                    ->color(fn(StatusApproval $state) => $state->color())
                    ->sortable(),

                TextColumn::make('dibuatOleh')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('view')
                    ->iconButton()
                    ->icon('tabler-file-description')
                    ->tooltip('Detail & Cetak Surat')
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            modal: 'modal-detail-balasan-pkl',
                            id: $record->getKey()
                        )
                    ),

                Action::make('pdf')
                    ->icon('tabler-file-type-pdf')
                    ->iconButton()
                    ->color('danger')
                    ->tooltip('Unduh Dokumen PDF')
                    ->url(fn(SuratBalasanPkl $record) => route('kepegawaian.surat.balasan-pkl.pdf', $record->getKey()))
                    ->openUrlInNewTab(),

                Action::make('approval')
                    ->icon('tabler-file-check')
                    ->iconButton()
                    ->color('success')
                    ->tooltip('Persetujuan Direktur')
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            modal: 'modal-approval-balasan-pkl',
                            id: $record->getKey()
                        )
                    )
                    ->visible(function (SuratBalasanPkl $record) use ($userLogin) {
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
                    ->modalHeading('Batalkan Surat Balasan PKL')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan surat ini?')
                    ->form([
                        Textarea::make('alasan_batal')
                            ->label('Alasan Pembatalan')
                            ->required()
                            ->rows(3)
                            ->placeholder('Contoh: Mahasiswa membatalkan rencana PKL / revisi jadwal.'),
                    ])
                    ->action(function (SuratBalasanPkl $record, array $data) {
                        $record->update([
                            'status' => StatusApproval::CANCELLED,
                            'catatan_approval' => trim(($record->catatan_approval ? $record->catatan_approval . "\n" : '') . '[Dibatalkan]: ' . $data['alasan_batal']),
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
                    ->visible(function (SuratBalasanPkl $record) use ($userLogin) {
                        $canCancel = $record->status !== StatusApproval::CANCELLED;
                        $isAuthorized = $userLogin->hasRole('Super-Admin')
                            || ($userLogin->karyawan && $record->disetujui_oleh == $userLogin->karyawan->id)
                            || ($userLogin->karyawan && $record->jabatan_id === optional($userLogin->karyawan->jabatan->first())->id)
                            || ($record->created_by === $userLogin->id);

                        return $canCancel && $isAuthorized;
                    }),
            ]);
    }

    public function openModal(string $modal, int $id)
    {
        $this->suratBalasanPkl = SuratBalasanPkl::findOrFail($id);
        $this->dispatch('open-modal', id: $modal);
    }

    #[On('refresh-table-balasan-pkl')]
    #[On('update-approval')]
    public function refreshTable()
    {
        $this->resetTable();
    }

    public function render()
    {
        return view('livewire.surat.balasan-pkl.table-balasan-pkl');
    }
}
