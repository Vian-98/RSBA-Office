<?php

namespace App\Livewire\Surat\BalasanPenelitian;

use App\Models\Surat\SuratTarifPenelitian;
use App\Models\Surat\SuratTemplateNomor;
use Livewire\Component;
use TallStackUi\Traits\Interactions;
use Throwable;

class KonfigTarif extends Component
{
    use Interactions;

    public $jenis_penelitian = 'Penelitian & Pendidikan';
    public $jasa_sarana = 100000;
    public $jasa_pelayanan = 150000;
    public $nomor_sk = '';
    public $tgl_berlaku;
    public $format_nomor;

    public function mount()
    {
        $tarif = SuratTarifPenelitian::latest('id')->first();
        if ($tarif) {
            $this->jenis_penelitian = $tarif->jenis_penelitian;
            $this->jasa_sarana      = $tarif->jasa_sarana;
            $this->jasa_pelayanan   = $tarif->jasa_pelayanan;
            $this->nomor_sk         = $tarif->nomor_sk;
            $this->tgl_berlaku      = $tarif->tgl_berlaku ? $tarif->tgl_berlaku->format('Y-m-d') : date('Y-m-d');
        } else {
            $this->tgl_berlaku = date('Y-m-d');
        }

        $this->format_nomor = SuratTemplateNomor::getTemplate('balasan_penelitian');
    }

    public function save()
    {
        $this->validate([
            'jenis_penelitian' => 'required|string|max:100',
            'jasa_sarana'      => 'required|numeric|min:0',
            'jasa_pelayanan'   => 'required|numeric|min:0',
            'tgl_berlaku'      => 'required|date',
            'format_nomor'     => 'required|string|max:255',
        ]);

        try {
            SuratTarifPenelitian::create([
                'jenis_penelitian' => $this->jenis_penelitian,
                'jasa_sarana'      => $this->jasa_sarana,
                'jasa_pelayanan'   => $this->jasa_pelayanan,
                'nomor_sk'         => $this->nomor_sk,
                'tgl_berlaku'      => $this->tgl_berlaku,
                'created_by'       => auth()->id(),
            ]);

            SuratTemplateNomor::updateOrCreate(
                ['jenis_surat' => 'balasan_penelitian'],
                [
                    'format_nomor' => $this->format_nomor,
                    'updated_by'   => auth()->id(),
                ]
            );

            $this->dispatch('close-modal', id: 'modal-konfig-tarif-penelitian');
            $this->toast()->success('Berhasil', 'Konfigurasi tarif penelitian & template nomor berhasil disimpan.')->send();
        } catch (Throwable $th) {
            $this->toast()->error('Gagal', 'Error: ' . $th->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.surat.balasan-penelitian.konfig-tarif', [
            'daftarTarif' => SuratTarifPenelitian::orderBy('id', 'desc')->take(6)->get()
        ]);
    }
}
