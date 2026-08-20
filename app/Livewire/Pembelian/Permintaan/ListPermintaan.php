<?php

namespace App\Livewire\Pembelian\Permintaan;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use App\Models\Gudang\PembelianRequest;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class ListPermintaan extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    public function table(Table $table): Table
    {
        return  $table->query(
            PembelianRequest::query()
                ->when(!auth()->user()->can('approve-umum-pengajuan'), function ($query) {
                    $query->where('user_req_id', auth()->id());
                })
        )
            ->columns([
                TextColumn::make('id')
                    ->label('Request Id')
                    ->searchable()
                    ->action(
                        fn($record, $livewire) => $livewire->modal(
                            modal: 'modal-detail-pengajuan',
                            id: $record->getKey()
                        )
                    ),

                TextColumn::make('user_request')
                    ->label('User Pengaju'),

                TextColumn::make('note')
                    ->label('Keterangan')
                    ->limit(100)
                    ->wrap(),

                TextColumn::make('priority')
                    ->label('Urgensi')
                    ->formatStateUsing(
                        fn($state): string => ucwords($state)
                    )
                    ->color(
                        fn(string $state): string => match ($state) {
                            'normal' => 'primary',
                            'penting' => 'warning',
                            'darurat' => 'danger',
                        }
                    ),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            'pending' => 'warning',
                            'approved' => 'primary',
                            'rejected' => 'danger',
                            'completed' => 'success'
                        }
                    )
                    ->formatStateUsing(
                        fn($state): string => ucwords($state)
                    ),

                TextColumn::make('user_verify')
                    ->label('User Verify')
                    ->default('Belum Diverifikasi')

            ])
            ->filters([
                // Define any filters if needed
            ])
            ->recordActions([
                // Define actions like view, edit, delete
                Action::make('persetujuan')
                    ->iconButton()
                    ->icon('tabler-checklist')
                    ->color('primary')
                    ->action(
                        fn($record) => $this->modal(
                            modal: 'modal-approval-pengajuan-pembelian',
                            id: $record->getKey(),
                        )
                    )
                    ->visible(
                        fn($record) => $record->status === 'pending' && auth()->user()->can('approve-umum-pengajuan')
                    ),

                Action::make('report')
                    ->iconButton()
                    ->icon('tabler-report')
                    ->color('gray')
                    ->action(
                        fn($record) => $this->modal(
                            modal: 'modal-approval-pengajuan-report',
                            id: $record->getKey(),
                        )
                    )
                    ->visible(
                        fn($record) => $record->status != 'pending'
                    ),

            ]);
    }


    #[Locked]
    public ?int $beliReqIdSelected;

    public function modal($modal, $id)
    {
        $this->beliReqIdSelected = $id;
        $this->dispatch('open-modal', id: $modal);
    }


    #[On('new-request-pembelian-created')]
    public function refreshComponent()
    {
        $this->render();
    }

    public function render()
    {
        return view('livewire.pembelian.permintaan.list-permintaan');
    }
}
