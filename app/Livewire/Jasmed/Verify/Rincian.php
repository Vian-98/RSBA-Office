<?php

namespace App\Livewire\Jasmed\Verify;

use App\Models\JmPasien;
use App\Models\JmProsentase;
use App\Models\JmRincian;
use App\Services\JasaMedisBpjsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Rincian extends Component
{
    use Interactions;

    public ?JmPasien $jmPasien;

    public ?JmProsentase $jmProsentase;

    public $chosaring;
    public $prosedur_non_bedah;
    public $prosedur_bedah;
    public $konsultasi;
    public $tenaga_ahli;
    public $keperawatan;
    public $penunjang;
    public $radiologi;
    public $laboratorium;
    public $pelayanan_darah;
    public $rehabilitasi;
    public $kamar_akomodasi;
    public $rawat_intensif;
    public $obat;
    public $alkes;
    public $bmhp;
    public $sewa_alat;
    public $obat_kronis;
    public $obat_kemo;
    public $real_billing_jasa;

    protected JasaMedisBpjsService $jasaMedisBpjsService;

    public function boot(JasaMedisBpjsService $jasaMedisBpjsService)
    {
        $this->jasaMedisBpjsService = $jasaMedisBpjsService;
    }


    public function mount($id)
    {
        $jmPasien = JmPasien::with(['rincian', 'prosentase'])->find($id);

        $this->jmPasien = $jmPasien;
        $rincian = $jmPasien?->rincian;

        $this->chosaring = $rincian->chosaring;
        $this->prosedur_non_bedah = $rincian->prosedur_non_bedah;
        $this->prosedur_bedah = $rincian->prosedur_bedah;
        $this->konsultasi = $rincian->konsultasi;
        $this->tenaga_ahli = $rincian->tenaga_ahli;
        $this->keperawatan = $rincian->keperawatan;
        $this->penunjang = $rincian->penunjang;
        $this->radiologi = $rincian->radiologi;
        $this->laboratorium = $rincian->laboratorium;
        $this->pelayanan_darah = $rincian->pelayanan_darah;
        $this->rehabilitasi = $rincian->rehabilitasi;
        $this->kamar_akomodasi = $rincian->kamar_akomodasi;
        $this->rawat_intensif = $rincian->rawat_intensif;
        $this->obat = $rincian->obat;
        $this->alkes = $rincian->alkes;
        $this->bmhp = $rincian->bmhp;
        $this->sewa_alat = $rincian->sewa_alat;
        $this->obat_kronis = $rincian->obat_kronis;
        $this->obat_kemo = $rincian->obat_kemo;
        $this->real_billing_jasa = $rincian->real_billing_jasa;

        $this->jmProsentase = $jmPasien->prosentase;
    }

    #[Computed]
    public function getTotal(): int
    {
        // In Blade or Component
        return collect($this->jmPasien?->rincian?->toArray())
            ->except(['id', 'jm_pasien_id', 'chosaring', 'created_at', 'updated_at']) // exclude keys
            ->sum();
    }

    public function recalc()
    {
        // $this->validate();
        $pasien = $this->jmPasien;


        // 01. Mulai Hitung Ulang
        try {
            // 02. Simpan rincian edit
            $this->updateRincian();

            // 03 Hitung Ulan
            $this->jasaMedisBpjsService->processPasien($pasien);

            $this->jmProsentase = $this->jmPasien->prosentase;

            $this->toast()
                ->success('Berhasil', 'Data disimpan.')
                ->send();
        } catch (\Throwable $e) {
            $this->toast()
                ->error('Tidak Berhasil', $e->getMessage())
                ->send();
        }
    }

    private function updateRincian()
    {
        JmRincian::where('jm_pasien_id', $this->jmPasien->id)
            ->update([
                'chosaring' => $this->chosaring,
                'prosedur_non_bedah' => $this->prosedur_non_bedah,
                'prosedur_bedah' => $this->prosedur_bedah,
                'konsultasi' => $this->konsultasi,
                'tenaga_ahli' => $this->tenaga_ahli,
                'keperawatan' => $this->keperawatan,
                'penunjang' => $this->penunjang,
                'radiologi' => $this->radiologi,
                'laboratorium' => $this->laboratorium,
                'pelayanan_darah' => $this->pelayanan_darah,
                'rehabilitasi' => $this->rehabilitasi,
                'kamar_akomodasi' => $this->kamar_akomodasi,
                'rawat_intensif' => $this->rawat_intensif,
                'obat' => $this->obat,
                'alkes' => $this->alkes,
                'bmhp' => $this->bmhp,
                'sewa_alat' => $this->sewa_alat,
                'obat_kronis' => $this->obat_kronis,
                'real_billing_jasa' => $this->real_billing_jasa,
            ]);
    }


    public function render()
    {
        return view('livewire.jasmed.verify.rincian');
    }
}
