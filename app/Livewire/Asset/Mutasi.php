<?php

namespace App\Livewire\Asset;

use Throwable;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use App\Models\Assets\AssetBarang;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Mutasi extends Component
{
    use Interactions;

    public ?AssetBarang $assetBarang;

    public bool $is_inc_component = true;
    public int $ruanganTujuan;
    public $tanggalMutasi;
    public string $keterangan = '';

    public $rules = [
        'ruanganTujuan' => 'required|exists:ruangan,id',
        'tanggalMutasi' => 'required',
        'keterangan' => 'nullable|string|max:255',
    ];

    public string $tab = 'Mutasi';

    public function mount($id): void
    {
        $this->assetBarang = AssetBarang::with(['barang', 'ruangan', 'child'])
            ->findOrFail($id);

        // dump();
        $this->is_inc_component = $this->assetBarang->child->count() > 0;

        $this->tanggalMutasi = now()->format('Y-m-d');
    }

    /**
     * Handle the asset mutation.
     *
     * @param  int  $ruanganId
     * @return void
     */
    public function submitMutasi()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // 01 : prepare history mutasi
            $dataHistoryMustasi = [
                'asset_id' => $this->assetBarang->id,
                'ruangan_asal_id' => $this->assetBarang->ruangan_id,
                'ruangan_tujuan_id' => $this->ruanganTujuan,
                'tanggal' => $this->tanggalMutasi,
                'keterangan' => $this->keterangan,
                'user_id' => auth()->id(),
            ];


            // 02 : Update Ruangan Asset Barang 
            $this->assetBarang->ruangan_id = $this->ruanganTujuan;
            $this->assetBarang->save();

            // 03 : Create History Mutasi
            $mutasi = $this->assetBarang->mutasis()->create($dataHistoryMustasi);

            // 04 : Catat Logs Asset
            $this->assetBarang->logs()->create([
                'status' => 'mutasi',
                'keterangan' => "Asset {$this->assetBarang->barang->nama} [{$this->assetBarang->kode}] dimutasikan dari ruangan {$mutasi->ruangan_asal->nama} ke ruangan " .
                    $mutasi->ruangan_tujuan->nama,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            $this->dispatch('mutasi-asset-saved');

            $this->toast()
                ->success(
                    'Mutasi Asset',
                    'Asset berhasil dimutasikan.'
                )->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error(
                    'Mutasi Asset',
                    'Asset tidak berhasil dimutasikan. Error : ' . $e->getMessage()
                )->send();
        }
    }

    // Table Riwayat Mutasi
    #[Computed]
    public function riwayatMutasis()
    {
        return $this->assetBarang->mutasis()
            ->with(['ruangan_asal', 'ruangan_tujuan', 'user'])
            ->orderBy('tanggal', 'desc')
            ->paginate(10);
    }

    function rows(): array
    {
        return $this->riwayatMutasis()->map(function ($item) {
            return [
                'tanggal' => Carbon::parse($item->tanggal)->locale('ID')->translatedFormat('d M Y'),
                'asal' => $item->ruangan_asal->nama,
                'tujuan' => $item->ruangan_tujuan->nama,
                'keterangan' => $item->keterangan,
                'user' => $item->user->karyawan->nama
            ];
        })->toArray();
    }

    public function headers(): array
    {
        return [
            ['index' => 'tanggal', 'label' => 'Tanggal'],
            ['index' => 'asal', 'label' => 'Asal'],
            ['index' => 'tujuan', 'label' => 'Tujuan'],
            ['index' => 'keterangan', 'label' => 'Keterangan'],
            ['index' => 'user', 'label' => 'Oleh'],
        ];
    }


    public function render()
    {
        return view('livewire.asset.mutasi');
    }
}
