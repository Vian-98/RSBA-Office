<?php

namespace App\Livewire\Maintenance\Permintaan;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Livewire\Component;
use Filament\Tables\Table;
use Livewire\Attributes\Locked;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use App\Models\Maintenance\Request as MaintenanceRequest;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\SelectFilter;
use Livewire\Attributes\On;

#[Isolate]
class ListPermintaan extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    #[On('maintenance-ticket-created')]
    public function refreshTable(): void
    {
        // Table auto refreshes
    }

    #[Locked]
    public ?MaintenanceRequest $maintenanceRequest;


    public static function table(Table $table): Table
    {
        return $table
            ->query(
                MaintenanceRequest::with('asset')
                    ->where('status', 'pending')
                    ->latest('created_at')
            )
            ->columns([
                TextColumn::make('nomor_tiket')
                    ->label('No. Tiket')
                    ->searchable()
                    ->fontFamily('mono')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('asset.kode')
                    ->label('Kode Asset'),

                TextColumn::make('asset.barang.nama')
                    ->label('Asset Item'),

                TextColumn::make('asset.ruangan.nama')
                    ->label('Lokasi'),

                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->formatStateUsing(fn($state) => ucfirst($state)),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->badge()
                    ->color(
                        fn($state) => match ($state) {
                            'pending' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            'cancelled' => 'warning',
                        }
                    ),

                TextColumn::make('user_request')
                    ->label('Diajukan Oleh'),

                TextColumn::make('created_at')
                    ->label('Tanggal Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable()

            ])
            ->filters([
                SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'normal' => 'Normal',
                        'penting' => 'Penting',
                        'darurat' => 'Darurat',

                    ]),

            ])
            ->recordActions([
                Action::make('view')
                    ->iconButton()
                    ->icon('tabler-file-check')
                    ->color('success')
                    ->action(
                        fn($record, $livewire) => $livewire->openModal(
                            modal: 'modal-apporoval-permintaan',
                            id: $record->getKey()
                        )
                    ),

                Action::make('lihat_tiket')
                    ->iconButton()
                    ->icon('tabler-ticket')
                    ->color('primary')
                    ->url(fn($record) => route('umum.maintenance.ticket.detail', $record->getKey()))
                    ->openUrlInNewTab(false),
            ]);
    }

    public function openModal($modal, $id)
    {
        $this->maintenanceRequest = MaintenanceRequest::find($id);
        $this->dispatch('open-modal', id: $modal);
    }


    public function render()
    {
        return view('livewire.maintenance.permintaan.list-permintaan');
    }
}
