<?php

namespace App\Livewire\Maintenance\Ticket;

use App\Exports\MaintenanceTicketLogExport;
use App\Models\Maintenance\Request as MaintenanceRequest;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Lazy]
class AllTickets extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    #[On('maintenance-ticket-created')]
    #[On('maintenance-ticket-updated')]
    public function refreshTable(): void
    {
        // Auto refresh table when tickets change
    }

    public function exportExcel(): BinaryFileResponse
    {
        $fileName = 'log-seluruh-tiket-maintenance-' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new MaintenanceTicketLogExport(), $fileName);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MaintenanceRequest::with([
                    'asset.barang',
                    'asset.ruangan',
                    'ruangan',
                    'user_req.karyawan',
                    'user_verif.karyawan',
                    'jadwal.teknisi.user.karyawan',
                    'jadwal.work',
                ])->latest('created_at')
            )
            ->columns([
                TextColumn::make('nomor_tiket')
                    ->label('No. Tiket')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('item_nama')
                    ->label('Item / Perihal')
                    ->getStateUsing(fn($record) => $record->item_nama)
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->where('note', 'like', "%{$search}%")
                            ->orWhereHas('asset.barang', fn($q) => $q->where('nama', 'like', "%{$search}%"));
                    })
                    ->wrap()
                    ->description(fn($record) => $record->asset?->kode ? 'Kode: ' . $record->asset->kode : 'Non-Aset / Umum'),

                TextColumn::make('lokasi_nama')
                    ->label('Lokasi')
                    ->getStateUsing(fn($record) => $record->lokasi_nama)
                    ->icon('tabler-map-pin')
                    ->iconColor('gray'),

                TextColumn::make('user_request')
                    ->label('Pelapor')
                    ->getStateUsing(fn($record) => $record->user_request)
                    ->description(fn($record) => $record->pelapor_kontak ? '📞 ' . $record->pelapor_kontak : null),

                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'normal'  => 'Normal',
                        'penting' => 'Urgent',
                        'darurat' => 'Emergency',
                        default   => ucfirst((string) $state),
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'normal'  => 'gray',
                        'penting' => 'warning',
                        'darurat' => 'danger',
                        default   => 'secondary',
                    }),

                TextColumn::make('ticket_status')
                    ->label('Status Tiket')
                    ->getStateUsing(fn($record) => $record->ticket_status_label)
                    ->badge()
                    ->color(fn($record) => match ($record->ticket_status) {
                        'open'        => 'warning',
                        'rejected'    => 'danger',
                        'assigned'    => 'primary',
                        'in_progress' => 'info',
                        'resolved'    => 'success',
                        default       => 'secondary',
                    })
                    ->description(function ($record) {
                        if ($record->ticket_status === 'rejected' && $record->ket_reject) {
                            return 'Alasan: ' . \Illuminate\Support\Str::limit($record->ket_reject, 30);
                        }
                        if ($record->jadwal && $record->jadwal->teknisi->isNotEmpty()) {
                            $tk = $record->jadwal->teknisi->first();
                            return 'Teknisi: ' . ($tk->user?->karyawan?->nama ?? $tk->user?->name ?? '-');
                        }
                        return null;
                    }),

                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Otorisasi')
                    ->options([
                        'pending'  => 'Pending (Menunggu Otorisasi)',
                        'approved' => 'Approved (Disetujui)',
                        'rejected' => 'Rejected (Ditolak)',
                    ]),

                SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'normal'  => 'Normal',
                        'penting' => 'Urgent / Penting',
                        'darurat' => 'Emergency / Darurat',
                    ]),

                SelectFilter::make('jenis')
                    ->label('Kategori / Bidang')
                    ->options([
                        'it'      => 'IT / Sistem',
                        'sarpras' => 'Sarpras / Umum',
                        'medis'   => 'Alat Medis',
                        'umum'    => 'Umum',
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Data Log Excel')
                    ->icon('tabler-file-spreadsheet')
                    ->color('success')
                    ->action(fn() => $this->exportExcel()),
            ])
            ->recordActions([
                Action::make('detail')
                    ->iconButton()
                    ->icon('tabler-eye')
                    ->color('primary')
                    ->tooltip('Buka Detail Tiket')
                    ->url(fn($record) => route('umum.maintenance.ticket.detail', $record->getKey()))
                    ->openUrlInNewTab(false),
            ])
            ->emptyStateHeading('Belum Ada Riwayat Tiket')
            ->emptyStateDescription('Semua riwayat pengajuan tiket (baik disetujui, ditolak, maupun selesai) akan tercatat di sini.')
            ->emptyStateIcon('tabler-ticket');
    }

    public function render()
    {
        return view('livewire.maintenance.ticket.all-tickets');
    }
}
