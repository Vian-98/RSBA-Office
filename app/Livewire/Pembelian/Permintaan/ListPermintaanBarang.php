<?php

namespace App\Livewire\Pembelian\Permintaan;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\BulkAction;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Gudang\PembelianRequestDetails;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class ListPermintaanBarang extends Component implements HasTable, HasForms, HasActions
{

    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table->query(
            PembelianRequestDetails::with(['request', 'barang'])
                ->whereNull('pembelian_id')
                ->whereHas('request', function ($query) {
                    $query->whereNotIn('status', ['completed', 'rejected']);
                })
                ->where(function ($query) {
                    $query->whereHas('request', function ($q) {
                        $q->where('status', '!=', 'approved');
                    })
                    ->orWhere('jml_disetujui', '>', 0);
                })
        )
            ->defaultGroup('barang.nama')
            ->columns([
                TextColumn::make('barang.nama')
                    ->label('Barang')
                    ->searchable(),

                TextColumn::make('request.id')
                    ->label('Request ID')
                    ->icon('tabler-external-link')
                    ->action(
                        fn($record) => $this->dispatch('open-modal')
                    ),

                TextColumn::make('request.user_request')
                    ->label('User Pengaju'),

                TextColumn::make('request.status')
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn($record): string => match ($this->getComputedStatus($record)) {
                            'pending' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            'completed' => 'primary'
                        }
                    )
                    ->formatStateUsing(
                        fn($record): string => ucwords($this->getComputedStatus($record))
                    ),

                TextColumn::make('jml_req')
                    ->label('Jumlah Permintaan'),

                TextColumn::make('jml_disetujui')
                    ->label('Disetujui')
                // ->formatStateUsing(
                //     function ($record) {
                //         if ($record->request->status === 'approved') {
                //         }
                // }
                // )


            ])
            ->filters([
                SelectFilter::make('request_id')
                    ->label('Request ID')
                    ->searchable()
                    ->relationship('request', 'id')
                    ->getOptionLabelFromRecordUsing(fn($record) => 'Request #' . $record->id),
            ])
            ->toolbarActions([
                BulkAction::make('buat_pembelian')
                    ->label('Buat Pembelian')
                    ->icon('tabler-shopping-cart-plus')
                    ->requiresConfirmation()
                    ->modalHeading('Buat Transaksi Pembelian')
                    ->modalDescription('Anda akan melakukan transaksi pembelian item-item yang anda pilih.')
                    ->modalSubmitActionLabel('Buat Pembelian')
                    ->schema([
                        Select::make('jenis_pembelian')
                            ->label('Jenis Pembelian')
                            ->options([
                                'langsung' => 'Pembelian Langsung',
                                'pesanan' => 'Buat Pesanan / PO',
                            ])
                            ->required()
                            ->reactive()
                            ->preload()
                            ->searchable(),
                    ])
                    ->action(function (Collection $records, $data) {

                        // data selected id
                        $selectedIds = $records->pluck('id')->toArray();

                        // buat chache key
                        $cacheKey = 'cart_pengajuan:' . auth()->id() . ':' . session()->getId();

                        // Simpan di cache dengan expiry
                        Cache::put($cacheKey, $selectedIds, now()->addMinutes(30));

                        // Simpan cache key di session
                        session()->put('cart_pengajuan_cache_key', $cacheKey);

                        if ($data['jenis_pembelian'] === 'langsung') {
                            $this->dispatch('open-modal', id: 'modal-pengajuan-to-langsung');
                        } else {
                            $this->dispatch('open-modal', id: 'modal-pengajuan-to-pesanan');
                        }
                    })
            ])
            ->checkIfRecordIsSelectableUsing(
                fn(Model $record): bool => $this->getComputedStatus($record) === 'approved',
            )
            ->selectCurrentPageOnly()
            ->recordActions([])
            ->emptyStateIcon('tabler-shopping-cart')
            ->emptyStateHeading('Pada saat ini Pembelian sedang kosong')
            ->emptyStateDescription('Tidak ada permintaan pembelian barang yang aktif saat ini.');
    }

    // to update request.status
    protected function getComputedStatus($record): string
    {
        if ($record->request->status === 'approved' && $record->jml_disetujui == 0) {
            return 'rejected';
        }

        return $record->request->status;
    }

    public function render()
    {
        return view('livewire.pembelian.permintaan.list-permintaan-barang');
    }
}
