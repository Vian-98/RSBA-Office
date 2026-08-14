<?php

namespace App\Livewire\Surat\BalasanPkl;

use App\Models\Surat\SuratTarifPkl;
use App\Models\Surat\SuratTemplateNomor;
use Livewire\Component;
use TallStackUi\Traits\Interactions;
use Throwable;

class KonfigTarif extends Component
{
    use Interactions;

    public $biaya_praktik_per_bulan;
    public $biaya_orientasi_per_orang;
    public $nomor_sk;
    public $tgl_berlaku;
    public $keterangan;
    public $format_nomor;

    public function mount()
    {
        $tarif = SuratTarifPkl::getAktif();
        $this->biaya_praktik_per_bulan   = $tarif->biaya_praktik_per_bulan;
        $this->biaya_orientasi_per_orang = $tarif->biaya_orientasi_per_orang;
        $this->nomor_sk                  = $tarif->nomor_sk;
        $this->tgl_berlaku               = $tarif->tgl_berlaku ? $tarif->tgl_berlaku->format('Y-m-d') : date('Y-m-d');
        $this->keterangan                = $tarif->keterangan;

        $this->format_nomor = SuratTemplateNomor::getTemplate('balasan_pkl');
    }

    public function save()
    {
        $this->validate([
            'biaya_praktik_per_bulan'   => 'required|numeric|min:0',
            'biaya_orientasi_per_orang' => 'required|numeric|min:0',
            'nomor_sk'                  => 'required|string|max:100',
            'tgl_berlaku'               => 'required|date',
            'format_nomor'              => 'required|string|max:255',
        ]);

        try {
            // Buat record tarif baru jika tanggal berlaku berbeda atau update jika sama
            SuratTarifPkl::create([
                'biaya_praktik_per_bulan'   => $this->biaya_praktik_per_bulan,
                'biaya_orientasi_per_orang' => $this->biaya_orientasi_per_orang,
                'nomor_sk'                  => $this->nomor_sk,
                'tgl_berlaku'               => $this->tgl_berlaku,
                'keterangan'                => $this->keterangan,
                'created_by'                => auth()->id(),
            ]);

            // Update template nomor
            SuratTemplateNomor::updateOrCreate(
                ['jenis_surat' => 'balasan_pkl'],
                [
                    'format_nomor' => $this->format_nomor,
                    'updated_by'   => auth()->id(),
                ]
            );

            $this->dispatch('close-modal', id: 'modal-konfig-tarif-pkl');
            $this->toast()->success('Berhasil', 'Konfigurasi tarif dan template nomor Surat Balasan PKL berhasil disimpan.')->send();
        } catch (Throwable $th) {
            $this->toast()->error('Gagal', 'Error: ' . $th->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.surat.balasan-pkl.konfig-tarif', [
            'riwayatTarif' => SuratTarifPkl::orderBy('tgl_berlaku', 'desc')->take(5)->get()
        ]);
    }
}
