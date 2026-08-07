<?php

namespace App\Livewire\Surat\Cuti;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use App\Enums\StatusApproval;
use App\Models\Surat\SuratCuti;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

use TallStackUi\Traits\Interactions;

class TableCuti extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;
    use Interactions;

    #[Locked]
    public ?SuratCuti $surat;

    public ?string $tglMelahirkanAktual = null;
    public ?string $catatanPenyesuaian = null;

    public static function table(Table $tableCuti): Table
    {
        return $tableCuti
            ->query(
                SuratCuti::with(['approvals', 'jenis'])->latest()
            )
            ->columns([
                TextColumn::make('no_surat')
                    ->label('No Surat')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('karyawan.nama')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenis.nama')
                    ->label('Urgensi'),

                TextColumn::make('lama_cuti')
                    ->label('Lama Cuti')
                    ->formatStateUsing(fn($record) => $record->lama_cuti . " Hari")
                    ->action(
                        fn($record, $livewire) => $livewire->modal(
                            modal: 'detil-surat-cuti',
                            id: $record->getKey()
                        )
                    ),
                TextColumn::make('tgl_mulai')
                    ->label('Tgl Mulai'),

                TextColumn::make('tgl_akhir')
                    ->label('Tgl Akhir'),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(
                        fn($state) => $state->nama()
                    )
                    ->badge()
                    ->color(fn($state) => $state->color())
                    ->action(
                        fn($record, $livewire) => $livewire->modal(
                            modal: 'modal-status-cuti',
                            id: $record->getKey()
                        )
                    ),
            ])
            ->recordActions([
                Action::make('print')
                    ->label('Print')
                    ->iconButton()
                    ->icon('tabler-printer')
                    ->color(
                        fn($record) => match ($record->status) {
                            StatusApproval::APPROVED => 'success',
                            default                  => 'gray',
                        }
                    )
                    ->visible(
                        fn($record) => in_array($record->status, [
                            StatusApproval::APPROVED,
                            StatusApproval::MANUAL,
                            StatusApproval::WAITING,
                            StatusApproval::PENDING,
                        ])
                    )
                    ->action(
                        function ($record, $livewire) {

                            $livewire->surat = $record->fresh();

                            return match ($record->status) {
                                StatusApproval::APPROVED, StatusApproval::MANUAL => $livewire->dispatch('trigger-print', noSurat: $record->no_surat),
                                StatusApproval::WAITING, StatusApproval::PENDING => $livewire->modal(modal: 'modal-options-approval-manual', id: $record->getKey()),
                            };
                        }
                    ),

                Action::make('approval')
                    ->iconButton()
                    ->icon('tabler-file-check')
                    ->color('success')
                    ->action(
                        fn($record, $livewire) => $livewire->modal(
                            modal: 'modal-approval-cuti',
                            id: $record->getKey()
                        )
                    )
                    ->visible(
                        function (SuratCuti $record) {
                            $approved_me = $record->approvals
                                ->contains('disetujui_oleh', auth()->user()->karyawan_id);

                            $isSuperAdmin = auth()->user()->hasRole('Super-Admin');

                            return ($record->status === StatusApproval::WAITING || $record->status === StatusApproval::PENDING)
                                && ($approved_me || $isSuperAdmin);
                        }
                    ),

                Action::make('adjustMelahirkan')
                    ->label('Penyesuaian Tanggal Melahirkan')
                    ->iconButton()
                    ->icon('tabler-baby-carriage')
                    ->color('warning')
                    ->tooltip('Penyesuaian Tanggal Persalinan (SDM)')
                    ->visible(
                        function (SuratCuti $record) {
                            $isMelahirkan = (int)$record->urgensi_id === 3 || str_contains(strtolower($record->jenis?->nama ?? ''), 'melahirkan') || str_contains(strtolower($record->jenis?->nama ?? ''), 'bersalin');
                            $hasPermission = auth()->user()->can('view-kepegawaian-cuti') || auth()->user()->can('edit-kepegawaian-cuti');
                            return $isMelahirkan && $hasPermission;
                        }
                    )
                    ->action(
                        function ($record, $livewire) {
                            $livewire->surat = $record->fresh();
                            $livewire->tglMelahirkanAktual = $record->tgl_melahirkan_aktual ?? date('Y-m-d');
                            $livewire->catatanPenyesuaian = $record->catatan_penyesuaian ?? '';
                            $livewire->dispatch('open-modal', id: 'modal-adjust-cuti-melahirkan');
                        }
                    ),
            ]);
    }

    public function modal($modal, $id)
    {
        $this->surat = SuratCuti::findOrFail($id);
        if ($modal === 'modal-adjust-cuti-melahirkan') {
            $this->tglMelahirkanAktual = $this->surat->tgl_melahirkan_aktual ?? date('Y-m-d');
            $this->catatanPenyesuaian = $this->surat->catatan_penyesuaian ?? '';
        }
        $this->dispatch('open-modal', id: $modal);
    }

    public function saveAdjustmentMelahirkan()
    {
        $this->validate([
            'tglMelahirkanAktual' => 'required|date',
        ]);

        if (!$this->surat) {
            return;
        }

        $this->surat->adjustCutiMelahirkan($this->tglMelahirkanAktual, $this->catatanPenyesuaian);

        $this->dispatch('close-modal', id: 'modal-adjust-cuti-melahirkan');
        $this->toast()
            ->success('Berhasil', 'Tanggal Cuti Melahirkan berhasil disesuaikan H+45 dari tanggal persalinan.')
            ->send();

        $this->resetTable();
    }


    #[On('modal-approval-cuti')]
    public function refreshTable()
    {
        $this->resetTable();
    }

    public function render()
    {
        return view('livewire.surat.cuti.table-cuti');
    }
}
