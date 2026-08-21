<?php

namespace App\Livewire\Maintenance;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\Maintenance\Jadwal;
use TallStackUi\Traits\Interactions;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

#[Isolate]
class ListJadwal extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;
    use Interactions;

    #[Locked]
    public int $selectedId; // selected jadwal id

    #[On('maintenance-work-updated')]
    #[On('maintenance-ticket-created')]
    public function refreshTable(): void
    {
        $this->resetTable();
    }


    public function table(Table $tabel): Table
    {
        $userLogin = auth()->user(); // user login

        return $tabel
            ->query(
                Jadwal::with('request', 'asset', 'asset.barang', 'asset.ruangan', 'teknisi.user', 'work')
                    ->when(
                        !$userLogin->can('view-umum-maintenance'),
                        function ($query) use ($userLogin) {
                            // filter jadwal as user login
                            $query->whereHas('teknisi', function ($q) use ($userLogin) {
                                $q->where('teknisi_id', $userLogin->id);
                            });
                        }

                    )
                    ->latest('created_at')

            )
            ->columns([
                TextColumn::make('request.nomor_tiket')
                    ->label('No. Tiket')
                    ->searchable()
                    ->fontFamily('mono')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('asset.kode')
                    ->label('Kode Asset')
                    ->searchable(),

                TextColumn::make('asset.barang.nama')
                    ->label('Asset Item'),

                TextColumn::make('asset.ruangan.nama')
                    ->label('Lokasi'),

                TextColumn::make('tanggal')
                    ->label('Jadwal')
                    ->date(),

                TextColumn::make('teknisi.user.karyawan.nama')
                    ->label('Teknisi'),

                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->badge()
                    ->color(
                        fn($state) => match ($state) {
                            'normal' => 'info',
                            'penting' => 'warning',
                            'darurat' => 'danger',
                            default => 'secondary',
                        }
                    ),

                TextColumn::make('work.status')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        return $record->work?->status;
                    })
                    ->formatStateUsing(
                        fn($state) => match ($state) {
                            'pending' => 'Belum Dikerjakan',
                            'in_progress' => 'Sedang Dikerjakan',
                            'done' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                            default => 'Tidak Diketahui',
                        }
                    )
                    ->badge()
                    ->color(
                        fn($state) => match ($state) {
                            'pending' => 'warning',
                            'in_progress' => 'info',
                            'done' => 'success',
                            'cancelled' => 'danger',
                            default => 'secondary',
                        }
                    ),
            ])

            ->filters([
                // Add any filters if needed
            ])
            ->recordActions([
                Action::make('maintenance')
                    ->iconButton()
                    ->recordTitle('asset.barang.nama')
                    ->icon('tabler-settings-exclamation')
                    ->color('primary')
                    ->action(
                        fn($record, $livewire)  => $livewire->openModal(
                            id: $record->getKey(),
                            modal: 'modal-maintenance-work'
                        )
                    )
                    ->visible(fn($record) => $record->work?->status === 'in_progress'),


                Action::make('report')
                    ->iconButton()
                    ->recordTitle('asset.barang.nama')
                    ->icon('tabler-report')
                    ->color('gray')
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            id: $record->getKey(),
                            modal: 'modal-maintenance-work-report'
                        )
                    )
                    ->visible(fn($record) => $record->work?->status === 'done'),

                Action::make('start_maintenance')
                    ->iconButton()
                    ->icon('tabler-player-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mulai Maintenance?')
                    ->modalDescription('Maintenance akan dimulai. Apakah anda yakin akan mulai sekarang?')
                    ->modalSubmitActionLabel('Mulai')
                    ->action(function ($record) {
                        $record->work()->create([
                            'asset_id' => $record->asset_id,
                            'maintc_jadwal_id' => $record->id,
                            'mulai' => now(),
                            'mulai_by' => auth()->id(),
                            'status' => 'in_progress',
                        ]);

                        // Log sistem
                        if ($record->request_id ?? $record->maintc_request_id ?? null) {
                            $reqId = $record->request->id ?? null;
                            if ($reqId) {
                                \App\Models\Maintenance\TicketComment::create([
                                    'request_id' => $reqId,
                                    'user_id'    => null,
                                    'body'       => 'Pekerjaan dimulai oleh ' . (auth()->user()?->karyawan?->nama ?? auth()->user()?->name ?? 'Teknisi'),
                                    'type'       => 'log',
                                ]);
                            }
                        }
                    })
                    ->after(
                        fn($record) => $this->openModal(
                            id: $record->getKey(),
                            modal: 'modal-maintenance-work'
                        )

                    )
                    ->visible(fn($record) => $record->work === null || $record->work->status === 'pending'),

                Action::make('lihat_tiket')
                    ->iconButton()
                    ->icon('tabler-ticket')
                    ->color('gray')
                    ->url(fn($record) => $record->request ? route('umum.maintenance.ticket.detail', $record->request->id) : null)
                    ->openUrlInNewTab(false),
            ]);
    }

    public function openModal($id, $modal)
    {
        $this->selectedId = $id;
        $this->dispatch('open-modal', id: $modal);
    }

    public function render()
    {
        return view('livewire.maintenance.list-jadwal');
    }
}
