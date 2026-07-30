<?php

namespace App\Livewire\Maintenance\Ticket;

use App\Models\Maintenance\Request as MaintenanceRequest;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class MyTickets extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    public function table(Table $table): Table
    {
        $userId = auth()->id();

        return $table
            ->query(
                MaintenanceRequest::with(['asset.barang', 'asset.ruangan', 'jadwal.work'])
                    ->where('user_req_id', $userId)
                    ->latest('created_at')
            )
            ->columns([
                TextColumn::make('nomor_tiket')
                    ->label('No. Tiket')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->color('primary'),

                TextColumn::make('asset.barang.nama')
                    ->label('Asset'),

                TextColumn::make('asset.ruangan.nama')
                    ->label('Lokasi'),

                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->formatStateUsing(fn($state) => match($state) {
                        'normal'  => 'Normal',
                        'penting' => 'Urgent',
                        'darurat' => 'Emergency',
                        default   => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'normal'  => 'gray',
                        'penting' => 'warning',
                        'darurat' => 'danger',
                        default   => 'secondary',
                    }),

                TextColumn::make('ticket_status_label')
                    ->label('Status')
                    ->getStateUsing(fn($record) => $record->ticket_status_label)
                    ->badge()
                    ->color(fn($record) => match($record->ticket_status) {
                        'open'        => 'warning',
                        'rejected'    => 'danger',
                        'assigned'    => 'primary',
                        'in_progress' => 'info',
                        'resolved'    => 'success',
                        default       => 'secondary',
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('detail')
                    ->iconButton()
                    ->icon('tabler-eye')
                    ->color('primary')
                    ->url(fn($record) => route('umum.maintenance.ticket.detail', $record->getKey()))
                    ->openUrlInNewTab(false),
            ])
            ->emptyStateHeading('Belum Ada Tiket')
            ->emptyStateDescription('Buat permintaan maintenance dari halaman Asset untuk memulai.')
            ->emptyStateIcon('tabler-ticket-off');
    }

    public function render()
    {
        return view('livewire.maintenance.ticket.my-tickets');
    }
}
