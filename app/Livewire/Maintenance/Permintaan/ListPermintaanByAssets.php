<?php

namespace App\Livewire\Maintenance\Permintaan;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Carbon\Carbon;
use Filament\Actions\Action;
use Livewire\Component;
use Filament\Tables\Table;
use Livewire\Attributes\Locked;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\Maintenance\Request as MaintenanceRequest;

class ListPermintaanByAssets extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    // public $maintenanceRequest;

    // public function headers(): array
    // {
    //     return [
    //         ['index' => 'tanggal', 'label' => 'Tanggal'],
    //         ['index' => 'user_id', 'label' => 'Pengaju'],
    //         ['index' => 'priority', 'label' => 'Prioritas'],
    //         ['index' => 'status', 'label' => 'Status'],
    //         ['index' => 'jadwal', 'label' => 'Jadwal'],
    //         ['index' => 'user_verify', 'label' => 'verifikasi Oleh']
    //     ];
    // }

    // public function rows(): array
    // {
    //     return $this->maintenanceRequest->map(function ($item) {
    //         return [
    //             'tanggal' => $item->created_at->format('d M Y H:i'),
    //             'user_id' => $item->user_request,
    //             'priority' => ucwords($item->priority),
    //             'status' => ucwords($item->status),
    //             'jadwal' => $item->jadwal?->tanggal ? \Carbon\Carbon::parse($item->jadwal?->tanggal)->locale('ID')->translatedFormat('d M Y') : 'Belum dijadwalkan',
    //             'user_verify' => $item->user_verify ? $item->user_verify : 'Belum diverifikasi',
    //         ];
    //     })->toArray();
    // }
    #[Locked]
    public ?int $assetId;

    #[Locked]
    public ?int $jadwalIdSelected;

    public function mount($asset_id)
    {
        $this->assetId = $asset_id;
        // $this->maintenanceRequest = MaintenanceRequest::where('asset_id', $asset_id)
        // ->orderBy('created_at', 'desc')
        // ->get();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MaintenanceRequest::with(['jadwal', 'jadwal.work'])
                    ->where('asset_id', $this->assetId)
                    ->orderBy('created_at', 'desc')
            )
            ->striped()
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->formatStateUsing(
                        fn($state) => Carbon::parse($state)->locale('ID')->translatedFormat('d M Y')
                    ),

                TextColumn::make('user_request')
                    ->label('Pengaju'),

                TextColumn::make('priority')
                    ->label('Priority'),

                TextColumn::make('status')
                    ->label('Status'),

                TextColumn::make('jadwal.tanggal')
                    ->label('Jadwal')
                    ->default('Belum Dijadwalkan')
                    ->formatStateUsing(
                        function ($state) {
                            // Jika state masih default atau null
                            if ($state === 'Belum Dijadwalkan' || $state === null) {
                                return "Belum Dijadwalkan";
                            }
                            return Carbon::parse($state)->locale('ID')->translatedFormat('d M Y');
                        }

                    ),

                TextColumn::make('user_verify')
                    ->label('Diverifikasi')
                    ->default('Belum Diverifikasi')
                    ->formatStateUsing(
                        function ($state) {
                            if ($state === 'Belum Diverifikasi' || $state === null) {
                                return "Belum Diverifikasi";
                            }

                            return $state;
                        }
                    ),
            ])
            ->recordActions([

                Action::make('Report')
                    ->iconButton()
                    ->icon('tabler-report')
                    ->color('primary')
                    ->action(
                        function ($record) {
                            $this->jadwalIdSelected = $record->jadwal?->id;
                            $this->dispatch(
                                'open-modal',
                                id: 'modal-maintenance-work-report'
                            );
                        }
                    )
                    ->visible(
                        fn($record) => $record->jadwal?->work?->status === 'done'
                    )
            ])

        ;
    }



    public function render()
    {
        return view('livewire.maintenance.permintaan.list-permintaan-by-assets');
    }
}
