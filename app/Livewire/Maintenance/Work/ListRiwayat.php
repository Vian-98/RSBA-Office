<?php

namespace App\Livewire\Maintenance\Work;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use App\Models\Maintenance\Work;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

#[Lazy]
class ListRiwayat extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    public ?object $assetBarang = null;
    public ?int $workId = null;
    public ?int $jadwalId = null;

    #[Locked]
    public ?int $jadwalIdSelected = null;

    public function mount($assetBarang = null, $workId = null, $jadwalId = null)
    {
        $this->assetBarang = $assetBarang;
        $this->workId = $workId;
        $this->jadwalId = $jadwalId;
    }

    #[On('maintenance-work-finished')]
    public function refreshTable(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Work::with('jadwal', 'jadwal.teknisi.user')
                    ->when($this->assetBarang?->id, fn($q) => $q->where('asset_id', $this->assetBarang->id))
                    ->when(!$this->assetBarang?->id && $this->workId, fn($q) => $q->where('id', $this->workId))
                    ->when(!$this->assetBarang?->id && !$this->workId && $this->jadwalId, fn($q) => $q->where('maintc_jadwal_id', $this->jadwalId))
                    ->latest('created_at')
            )
            ->columns([
                TextColumn::make('jadwal.tanggal')
                    ->label('Tanggal')
                    ->date(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(
                        fn($state) => match ($state) {
                            'pending' => 'Menunggu',
                            'in_progress' => 'Sedang Dikerjakan',
                            'done' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                            default => 'Tidak Diketahui',
                        }
                    ),
                TextColumn::make('jadwal.teknisi.user.karyawan.nama')
                    ->label('Teknisi'),

                TextColumn::make('total_biaya')
                    ->label('Biaya'),

            ])
            ->recordActions([
                Action::make('maintenance')
                    ->iconButton()
                    ->icon('tabler-settings-exclamation')
                    ->color('primary')
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            id: $record->getKey(),
                            modal: 'modal-maintenance-work-add',
                        )
                    )
                    ->visible(fn($record) => $record->status === 'in_progress'),

                Action::make('report')
                    ->iconButton()
                    ->icon('tabler-report')
                    ->color('gray')
                    ->action(
                        function ($record) {
                            $this->jadwalIdSelected = $record->jadwal->id;
                            $this->dispatch('open-modal', id: 'modal-maintenance-work-report-on-list-riwayat');
                        }
                    )
                    ->visible(fn($record) => $record->status === 'done'),
            ])
            ->emptyStateIcon('tabler-history')
            ->emptyStateHeading('Tidak ada riwayat')
            ->emptyStateDescription('Riwayat perbaikan atau pemeliharaan tidak ditemukan.');
    }


    #[Locked]
    public int $selectedId = 0;

    private function openModal($id, $modal)
    {
        $this->selectedId = $id;
        $this->dispatch('open-modal', id: $modal);
    }

    public function render()
    {
        return view('livewire.maintenance.work.list-riwayat');
    }
}
