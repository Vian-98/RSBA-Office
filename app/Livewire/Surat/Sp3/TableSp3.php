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
                SuratSp3::withSum('details', 'nominal')->with(['approvals', 'createdBy'])
                    ->when(!$userLogin->can('approve-kepegawaian-sp3'), function ($query) use ($userLogin) {
                        $jabatanId = $userLogin->karyawan?->jabatan?->first()?->id;
                        $userId = $userLogin->id;

                        $query->where(function ($q) use ($jabatanId, $userId) {
                            $q->orWhere('surat_sp3.created_by', $userId); // where dibuat oleh user login

                            if ($jabatanId) {
                                $q->orWhere('surat_sp3.jabatan_id', $jabatanId); //atau where mengetahui user login
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
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            modal: 'modal-detail-sp3',
                            id: $record->getKey()
                        )
                    ),

                Action::make('approval')
                    ->icon('tabler-file-check')
                    ->iconButton()
                    ->color('success')
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            modal: 'modal-approval-sp3',
                            id: $record->getKey()
                        )
                    )
                    ->visible(
                        fn($record) => (
                            (
                                auth()->user()->can('approve-kepegawaian-sp3')
                                or
                                $record->jabatan_id === auth()->user()?->karyawan?->jabatan?->first()?->id //jabatan yg mengetahui
                            ) and
                            $record->approvals->count() === 0
                        )

                    )

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
