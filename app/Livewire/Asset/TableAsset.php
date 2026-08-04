<?php

namespace App\Livewire\Asset;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use App\Models\Ruangan;
use Livewire\Component;
use Filament\Tables\Table;
use Livewire\Attributes\Locked;
use App\Models\Assets\AssetBarang;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class TableAsset extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    #[Locked]
    public $selectedId;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AssetBarang::with(['barang', 'ruangan', 'barang.kategori', 'maintenanceRequests' => fn($q) => $q->active()])
                    ->withHierarchySort()
                    ->when(
                        !auth()->user()->hasRole('Super-Admin') && !auth()->user()->can('view-umum-asset'),
                        function (Builder $query) {
                            $user = auth()->user();
                            if ($user && $user->karyawan && $user->karyawan->ruangan_id) {
                                $query->where('ruangan_id', $user->karyawan->ruangan_id);
                            } else {
                                $query->whereNull('id'); // Hide all if no ruangan
                            }
                        }
                    )
            )
            ->columns([
                TextColumn::make('kode')
                    ->label('Asset Kode')
                    ->getStateUsing(
                        fn($record) => $record->kode ?? 'Belum didaftarkan'
                    )
                    ->action(
                        fn($record, $livewire) => match ($record->kode) {
                            null =>  $livewire->modalAsset(
                                modal: 'modal-catat-asset',
                                id: $record->getKey()
                            ),
                            default => '',
                        }

                    )
                    ->color(
                        fn($record) => $record->kode ? '' : 'danger'
                    )
                    ->copyable(
                        fn($record) => $record->kode ?? false
                    )
                    ->copyMessage(
                        fn(string $state): string => "Copied : {$state}"
                    )
                    ->searchable(),

                TextColumn::make('barang.nama')
                    ->label('Item')
                    ->searchable()
                    ->getStateUsing(
                        fn($record) => "<span class='ms-{$record->level}'>{$record->barang->nama}</span>"
                    )
                    ->html(),

                TextColumn::make('barang.kategori.nama')
                    ->label('Kategori'),

                TextColumn::make('ruangan.nama')
                    ->label('Di Ruangan'),

                TextColumn::make('tanggal_catat')
                    ->label('Tanggal Pencatatan'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            'Baik' => 'success',
                            'Dalam Perbaikan' => 'warning',
                            'Rusak' => 'danger',
                            'Hilang' => 'danger',
                            default => 'secondary'
                        }
                    )
                    ->getStateUsing(
                        fn($record) => $record->status == 'diperbaiki' ? "Dalam Perbaikan" : ucwords($record->status)
                    )
                    ->action(
                        fn($record, $livewire) => match ($record->status) {
                            'diperbaiki' => $livewire->modalAsset(
                                modal: 'modal-maintenance-status',
                                id: $record->getKey()
                            ),
                            default => ''
                        }
                    ),

                TextColumn::make('active_ticket')
                    ->label('Tiket Aktif')
                    ->getStateUsing(fn($record) => $record->maintenanceRequests->first()?->nomor_tiket)
                    ->badge()
                    ->color('warning')
                    ->fontFamily('mono')
                    ->placeholder('-'),
            ])
            ->filters([
                Filter::make('main_asset')
                    ->label('Hanya Asset Utama')
                    ->toggle()
                    ->default('true')
                    ->query(function (Builder $query): Builder {
                        return $query->where('jenis', 'main');
                    }),

                // TODO: Pencarian Main Asset dan tampilkan beserta komponen nya
                // SelectFilter::make('asset_and_component')
                //     ->label('Asset dan komponennya')
                //     ->searchable()
                //     ->options(function () {
                //         return Barang::pluck('nama', 'id')->toArray();
                //     })
                //     ->query(function (Builder $query, $state) {
                //         if (!$state) {
                //             return $query;
                //         }
                //         $query->where(function (Builder $query) use ($state) {
                //             $query->where('barang_id', $state)
                //                 ->orWhere('main_asset_id', function ($subQuery) use ($state) {});

                //             // $query data get data again where main_asset_id = $query->id;
                //             // ->where('main_asset_id', $state);
                //         });
                //     }),
                // ->getOptionLabelFromRecordUsing(
                //     fn($record) => 'Asset Nama ' . $record->nama
                // ),

                SelectFilter::make('main_asset_id')
                    ->label('Asset')
                    ->searchable()
                    ->relationship('barang', 'nama')
                    ->getOptionLabelFromRecordUsing(
                        fn($record) => 'Asset : ' . $record->nama
                    ),

                SelectFilter::make('ruangan_id')
                    ->label('Ruangan')
                    ->searchable()
                    ->options(
                        fn() => Ruangan::pluck('nama', 'id')->toArray()
                    )
            ])
            ->recordActions([
                Action::make('maintenance')
                    ->iconButton()
                    ->extraAttributes(['title' => 'Buat Tiket Maintenance'])
                    ->icon('tabler-tools')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Select::make('target_asset_id')
                            ->label('Item yang Diperbaiki')
                            ->options(function ($record) {
                                $options = [
                                    $record->id => 'Asset Utama: ' . ($record->barang->nama ?? 'Asset') . ' (' . ($record->kode ?? 'Belum ada kode') . ')'
                                ];
                                foreach ($record->components as $comp) {
                                    $options[$comp->id] = 'Komponen: ' . ($comp->barang->nama ?? 'Komponen') . ' (' . ($comp->kode ?? '-') . ')';
                                }
                                return $options;
                            })
                            ->default(fn ($record) => $record->id)
                            ->required()
                            ->visible(fn ($record) => $record->components->count() > 0),
                        \Filament\Forms\Components\Select::make('priority')
                            ->label('Prioritas')
                            ->options([
                                'normal' => 'Normal',
                                'penting' => 'Penting',
                                'darurat' => 'Darurat',
                            ])
                            ->default('normal')
                            ->required()
                            ->live(),
                        \Filament\Forms\Components\TextInput::make('ket_priority')
                            ->label('Keterangan Prioritas')
                            ->required(fn (\Filament\Forms\Get $get) => $get('priority') !== 'normal')
                            ->visible(fn (\Filament\Forms\Get $get) => $get('priority') !== 'normal')
                            ->maxLength(255),
                        \Filament\Forms\Components\Textarea::make('note')
                            ->label('Keluhan / Masalah (Note)')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\FileUpload::make('lampiran')
                            ->label('Lampiran / Foto')
                            ->multiple()
                            ->directory('maintenanceReqs/lampiran'),
                    ])
                    ->action(function (array $data, $record, $livewire) {
                        \Illuminate\Support\Facades\DB::beginTransaction();
                        try {
                            $targetAssetId = $data['target_asset_id'] ?? $record->id;
                            $targetAsset = AssetBarang::find($targetAssetId) ?? $record;

                            // Create the maintenance request
                            $maintReq = $targetAsset->maintenanceRequests()->create([
                                'user_req_id' => auth()->id(),
                                'priority' => $data['priority'],
                                'ket_priority' => $data['ket_priority'] ?? null,
                                'note' => $data['note'],
                                'lampiran' => $data['lampiran'] ?? null,
                                'status' => 'pending',
                            ]);

                            $itemName = $targetAsset->id === $record->id 
                                ? 'Asset Utama (' . ($targetAsset->barang->nama ?? '-') . ')'
                                : 'Komponen (' . ($targetAsset->barang->nama ?? '-') . ')';

                            // Log sistem
                            \App\Models\Maintenance\TicketComment::create([
                                'request_id' => $maintReq->id,
                                'user_id'    => auth()->id(),
                                'body'       => 'Tiket dibuat oleh ' . (auth()->user()?->karyawan?->nama ?? auth()->user()?->name ?? 'User') . ' untuk perbaikan ' . $itemName,
                                'type'       => 'log',
                            ]);

                            // Update asset status
                            $targetAsset->update(['status' => 'diperbaiki']);

                            if ($targetAsset->id === $record->id) {
                                $record->components->each(function ($component) {
                                    $component->update(['status' => 'diperbaiki']);
                                });
                            } else {
                                $record->update(['status' => 'diperbaiki']);
                            }

                            \Illuminate\Support\Facades\DB::commit();

                            // Use TallStackUI Toast for notification since we might not have Filament Notifications configured
                            $livewire->toast()->success('Berhasil', 'Tiket perbaikan berhasil dikirim.')->send();
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\DB::rollBack();
                            $livewire->toast()->error('Gagal', 'Terjadi kesalahan saat membuat tiket.')->send();
                        }
                    })
                    ->modalHeading(fn ($record) => 'Buat Tiket Perbaikan - ' . $record->nama)
                    ->modalWidth('lg')
                    ->visible(fn($record) => !$record->maintenanceRequests->count()),

                Action::make('lihat_tiket')
                    ->iconButton()
                    ->icon('tabler-ticket')
                    ->color('warning')
                    ->url(fn($record) => $record->maintenanceRequests->first()
                        ? route('umum.maintenance.ticket.detail', $record->maintenanceRequests->first()->id)
                        : null
                    )
                    ->visible(fn($record) => $record->maintenanceRequests->count() > 0),

                Action::make('catat')
                    ->iconButton()
                    ->icon('tabler-library-plus')
                    ->color('success')
                    ->action(
                        fn($record, $livewire) => $livewire->modalAsset(
                            modal: 'modal-catat-asset',
                            id: $record->getKey()
                        )
                    )
                    ->visible(
                        fn($record) => !$record->kode
                    ),


                ActionGroup::make([
                    Action::make('lihat')
                        ->label('Detail Asset')
                        ->icon('tabler-eye')
                        ->action(
                            fn($record) => $this->modalAsset(
                                modal: 'modal-asset-details',
                                id: $record->getKey()
                            )
                        )
                        ->visible(
                            fn($record) => $record->kode
                        ),


                    // edit asset, dan edit spesiikasi asset
                    Action::make('Spesifikasi')
                        ->icon('tabler-device-desktop-question')
                        ->action(
                            fn($record) => $this->modalAsset(
                                modal: 'modal-asset-specs',
                                id: $record->getKey()
                            )
                        ),

                    // input maintenance
                    Action::make('maintenance')
                        ->icon('tabler-device-desktop-cog')
                        ->label('Maintenance')
                        ->action(
                            fn($record) => $this->modalAsset(
                                modal: 'modal-maintenance-asset',
                                id: $record->getKey()
                            )
                        ),
                    // ->url(fn($record): string => route('umum.asset.maintenance', $record->getKey())),

                    Action::make('mutasi')
                        ->icon('tabler-device-desktop-share')
                        ->label('Mutasi Asset')
                        ->action(
                            fn($record) => $this->modalAsset(
                                modal: 'modal-mutasi-asset',
                                id: $record->getKey()
                            )
                        ),

                    Action::make('label')
                        ->label('Cetak Label')
                        ->icon('tabler-tags')
                        ->action(function ($record) {
                            $this->selectedId = $record->getKey();
                            $this->dispatch('print-label');
                        }),

                    // logs
                    Action::make('logs')
                        ->icon('tabler-history')
                        ->action(
                            fn($record) => $this->modalAsset(
                                modal: 'modal-logs-asset',
                                id: $record->getKey()
                            )
                        ),
                ])->visible(
                    fn($record) => $record->kode
                ),
            ]);
    }

    private function getQuery() {}

    function modalAsset($modal, $id)
    {
        $this->selectedId = $id;
        $this->dispatch('open-modal', id: $modal);
    }

    public function render()
    {
        return view('livewire.asset.table-asset');
    }
}
