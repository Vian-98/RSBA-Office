<?php

namespace App\Livewire\Surat\Sp3;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Livewire\Component;
use Filament\Tables\Table;
use App\Enums\StatusApproval;
use App\Models\Surat\SuratSp3;
use App\Models\Sdm\Jabatan;
use Livewire\Attributes\Locked;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Attributes\On;

class TableSp3 extends Component implements HasTable, HasForms, HasActions
{

    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    #[Locked]
    public ?SuratSp3 $suratSp3;

    // Manual filter properties
    public ?string $filterStatus   = null;
    public ?string $filterKategori = null;
    public ?int    $filterJabatan  = null;

    public function table(Table $table): Table
    {
        $userLogin = auth()->user(); // user login

        return $table
            ->query(
                SuratSp3::withSum('details', 'nominal')->with(['approvals', 'createdBy', 'verifikasiKeuangan', 'ttdAtasan'])
                    ->when(!$userLogin->hasRole('Super-Admin'), function ($query) use ($userLogin) {
                        $jabatanId = $userLogin->karyawan?->jabatan?->first()?->id;
                        $karyawanId = $userLogin->karyawan_id;
                        $userId = $userLogin->id;
                        $isKeuangan = $userLogin->hasRole(['Keuangan', 'Wadir-Keuangan']) || $userLogin->isKabagKeuangan();

                        $query->where(function ($q) use ($jabatanId, $userId, $karyawanId, $isKeuangan) {
                            $q->orWhere('surat_sp3.created_by', $userId);

                            if ($jabatanId) {
                                $q->orWhere('surat_sp3.jabatan_id', $jabatanId);
                            }

                            if ($karyawanId) {
                                $q->orWhere('surat_sp3.verifikator_keuangan_id', $karyawanId);
                            }

                            if ($isKeuangan) {
                                $q->orWhereNotNull('surat_sp3.id');
                            }
                        });
                    })
                    ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
                    ->when($this->filterKategori === 'gaji', fn($q) => $q->whereNotNull('payroll_periode'))
                    ->when($this->filterKategori === 'umum', fn($q) => $q->whereNull('payroll_periode'))
                    ->when($this->filterJabatan, fn($q) => $q->where('jabatan_id', $this->filterJabatan))
                    ->latest()
            )
            ->columns([
                TextColumn::make('no')
                    ->label('Nomor')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('tgl')
                    ->label('Tanggal')
                    ->sortable(),

                TextColumn::make('rekanan')
                    ->label('Rekanan'),

                TextColumn::make('keterangan')
                    ->label('Subject / Berita')
                    ->limit(72)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('dibuatOleh')
                    ->label('Dibuat Oleh'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(StatusApproval $state) => $state->nama())
                    ->color(fn(StatusApproval $state) => $state->color())
                    ->sortable(),

                TextColumn::make('details_sum_nominal')
                    ->label('Jumlah')
                    ->money('IDR')

            ])
            ->recordActions([
                Action::make('view')
                    ->iconButton()
                    ->icon('tabler-file-description')
                    ->tooltip('Detail SP3')
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            modal: 'modal-detail-sp3',
                            id: $record->getKey()
                        )
                    ),

                Action::make('verifikasi_keuangan')
                    ->icon('tabler-file-dollar')
                    ->iconButton()
                    ->color('warning')
                    ->tooltip('Verifikasi Keuangan')
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            modal: 'modal-verifikasi-keuangan-sp3',
                            id: $record->getKey()
                        )
                    )
                    ->visible(function (SuratSp3 $record) use ($userLogin) {
                        if ($record->status !== StatusApproval::PENDING) {
                            return false;
                        }

                        $isAssignedVerifier = $record->verifikator_keuangan_id && $record->verifikator_keuangan_id == $userLogin->karyawan_id;
                        $isKeuanganAuthorized = $userLogin->hasRole(['Super-Admin', 'Wadir-Keuangan', 'Keuangan']) || $userLogin->isKabagKeuangan();

                        return ($isAssignedVerifier || $isKeuanganAuthorized) && !$record->verifikasiKeuangan;
                    }),

                Action::make('approval')
                    ->icon('tabler-file-check')
                    ->iconButton()
                    ->color('success')
                    ->tooltip('Persetujuan / TTD Atasan')
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            modal: 'modal-approval-sp3',
                            id: $record->getKey()
                        )
                    )
                    ->visible(function (SuratSp3 $record) use ($userLogin) {
                        if ($record->status !== StatusApproval::WAITING) {
                            return false;
                        }

                        $isApprover = $userLogin->hasRole('Super-Admin')
                            || $record->jabatan_id === $userLogin->karyawan?->jabatan?->first()?->id;

                        return $isApprover && !$record->ttdAtasan;
                    }),

                Action::make('edit')
                    ->icon('tabler-edit')
                    ->iconButton()
                    ->color('info')
                    ->tooltip('Edit & Ajukan Ulang')
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            modal: 'modal-edit-sp3',
                            id: $record->getKey()
                        )
                    )
                    ->visible(function (SuratSp3 $record) use ($userLogin) {
                        $canEdit = $record->status === StatusApproval::REJECTED || $record->status === StatusApproval::PENDING;
                        $isOwner = $record->created_by === $userLogin->id || $userLogin->hasRole('Super-Admin');

                        return $canEdit && $isOwner;
                    }),
            ]);


    }

    public function updatedFilterStatus()   { $this->resetTable(); }
    public function updatedFilterKategori() { $this->resetTable(); }
    public function updatedFilterJabatan()  { $this->resetTable(); }

    public function resetFilters(): void
    {
        $this->filterStatus   = null;
        $this->filterKategori = null;
        $this->filterJabatan  = null;
        $this->resetTable();
    }

    public function hasActiveFilters(): bool
    {
        return $this->filterStatus !== null
            || $this->filterKategori !== null
            || $this->filterJabatan !== null;
    }

    function openModal($modal, $id)
    {
        $this->suratSp3 = SuratSp3::findOrFail($id);
        if ($modal === 'modal-edit-sp3') {
            $this->dispatch('buka-edit-sp3', id: $id);
        }
        return $this->dispatch('open-modal', id: $modal);
    }


    #[On('update-approval')]
    public function refreshTable()
    {
        $this->resetTable();
    }

    public function render()
    {
        return view('livewire.surat.sp3.table-sp3', [
            'statusOptions'  => collect(StatusApproval::options())->pluck('label', 'value')->toArray(),
            'jabatanOptions' => Jabatan::pluck('nama', 'id')->toArray(),
        ]);
    }
}
