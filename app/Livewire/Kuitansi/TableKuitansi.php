<?php

namespace App\Livewire\Kuitansi;

use App\Enums\StatusKuitansi;
use App\Models\Keuangan\Kuitansi;
use App\Models\Keuangan\MetodeBayar;
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
use TallStackUi\Traits\Interactions;

class TableKuitansi extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions, InteractsWithTable, InteractsWithForms, Interactions;

    #[Locked]
    public ?Kuitansi $kuitansi = null;

    public ?string $filterStatus = null;
    public ?string $filterMetode = null;

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $userKaryawanId = $user?->karyawan_id;

        return $table
            ->query(
                Kuitansi::query()
                    ->with(['createdBy.karyawan', 'penerima', 'approvals.disetujuiOleh'])
                    ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
                    ->when($this->filterMetode, fn ($q) => $q->where('metode_bayar', $this->filterMetode))
                    ->latest()
            )
            ->columns([
                TextColumn::make('nomor')
                    ->label('Nomor')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('diterima_dari')
                    ->label('Diterima Dari')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state) => formatRupiah($state, true, false))
                    ->sortable(),

                TextColumn::make('penerima_nama')
                    ->label('Penerima')
                    ->searchable(),

                TextColumn::make('metode_bayar')
                    ->label('Metode')
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => is_object($state) ? $state->nama() : ucfirst($state))
                    ->color(fn ($state) => is_object($state) ? $state->color() : 'warning')
                    ->sortable(),

                TextColumn::make('createdBy.name')
                    ->label('Kasir')
                    ->placeholder('-'),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('tabler-file-description')
                    ->iconButton()
                    ->tooltip('Detail Kuitansi')
                    ->action(fn (Kuitansi $record, $livewire) => $livewire->openModal('modal-detail-kuitansi', $record->id)),

                Action::make('approval')
                    ->icon('tabler-file-check')
                    ->iconButton()
                    ->color('success')
                    ->tooltip('Persetujuan / TTD')
                    ->visible(function (Kuitansi $record) use ($user, $userKaryawanId) {
                        if (in_array($record->status, [StatusKuitansi::APPROVED, StatusKuitansi::DIBATALKAN, StatusKuitansi::REJECTED])) {
                            return false;
                        }

                        if ($user->hasRole('Super-Admin')) {
                            return true;
                        }

                        // Check if current user is an assigned approver who hasn't approved yet
                        return $record->approvals->contains(function ($app) use ($userKaryawanId) {
                            return $app->disetujui_oleh == $userKaryawanId
                                && !in_array($app->status, [\App\Enums\StatusApproval::APPROVED, \App\Enums\StatusApproval::MANUAL]);
                        });
                    })
                    ->action(fn (Kuitansi $record, $livewire) => $livewire->openModal('modal-approval-kuitansi', $record->id)),

                Action::make('cetak')
                    ->icon('tabler-printer')
                    ->iconButton()
                    ->color('primary')
                    ->tooltip('Cetak Kuitansi')
                    ->visible(fn (Kuitansi $record) => in_array($record->status, [StatusKuitansi::APPROVED, StatusKuitansi::MANUAL]) || auth()->user()->can('print-keuangan-kuitansi'))
                    ->action(fn (Kuitansi $record, $livewire) => $livewire->openModal('modal-print-kuitansi', $record->id)),

                Action::make('batalkan')
                    ->icon('tabler-circle-x')
                    ->iconButton()
                    ->color('danger')
                    ->tooltip('Batalkan Kuitansi')
                    ->visible(function (Kuitansi $record) use ($user) {
                        if ($record->status === StatusKuitansi::DIBATALKAN) {
                            return false;
                        }

                        // Kasir boleh batalkan jika belum diarsipkan ke bank surat (docstore_key === null)
                        $isCreator = $record->created_by === $user->id;
                        $canVoid = $user->can('void-keuangan-kuitansi') || $user->hasRole(['Super-Admin', 'Wadir-Keuangan']) || $isCreator;

                        return $canVoid && empty($record->docstore_key);
                    })
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('alasan_batal')
                            ->label('Alasan Pembatalan')
                            ->placeholder('Masukkan alasan pembatalan kuitansi...')
                            ->required(),
                    ])
                    ->action(fn (Kuitansi $record, array $data) => $this->batalkanKuitansi($record, $data['alasan_batal'])),
            ]);
    }

    public function openModal(string $modal, int $id): void
    {
        $this->kuitansi = Kuitansi::with(['createdBy.karyawan', 'penerima', 'approvals.disetujuiOleh', 'details'])->findOrFail($id);
        $this->dispatch('buka-modal-kuitansi', id: $id, modal: $modal);
        $this->dispatch('open-modal', id: $modal);
    }

    public function batalkanKuitansi(Kuitansi $record, string $alasan): void
    {
        $user = auth()->user();
        $isCreator = $record->created_by === $user->id;
        $canVoid = $user->can('void-keuangan-kuitansi') || $user->hasRole(['Super-Admin', 'Wadir-Keuangan']) || $isCreator;

        abort_unless($canVoid && empty($record->docstore_key), 403, 'Kuitansi yang telah diarsipkan ke bank surat tidak dapat dibatalkan.');

        $record->update([
            'status'        => StatusKuitansi::DIBATALKAN,
            'dibatalkan_by' => $user->id,
            'dibatalkan_at' => now(),
            'alasan_batal'  => $alasan,
        ]);

        $this->toast()->success('Dibatalkan', "Kuitansi {$record->nomor} telah dibatalkan.")->send();
        $this->resetTable();
    }

    #[On('kuitansi-tersimpan')]
    #[On('update-approval-kuitansi')]
    public function refreshTable(): void
    {
        $this->resetTable();
    }

    public function updatedFilterStatus(): void { $this->resetTable(); }
    public function updatedFilterMetode(): void { $this->resetTable(); }

    public function render()
    {
        return view('livewire.kuitansi.table-kuitansi', [
            'statusOptions' => StatusKuitansi::options(),
            'metodeOptions' => MetodeBayar::pluck('nama', 'nama')->toArray(),
        ]);
    }
}
