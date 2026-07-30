<?php

namespace App\Livewire\Akuntansi\Jurnal;

use Carbon\Carbon;
use App\Models\JmPasien;
use App\Models\Gudang\Pembelian;
use App\Traits\AuthorizesFromRoute;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Jurnal Umum')]
class Index extends Component
{
    use AuthorizesFromRoute, WithPagination;

    public string $periode = '';
    public string $search = '';

    public function mount(): void
    {
        $this->periode = Carbon::now()->format('Y-m');
    }

    public function updatedPeriode(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function getJurnalEntriesProperty()
    {
        $periodeKey = $this->periode ?: Carbon::now()->format('Y-m');
        $entries = collect();

        // 1. Entri Gaji Payroll
        $gajiVal = DB::table('sdm_payroll_slips')->where('periode', $periodeKey)->sum('gaji_bersih') ?? 0;
        if ($gajiVal > 0) {
            $entries->push([
                'tgl' => $periodeKey . '-25',
                'no_jurnal' => 'JRN-' . str_replace('-', '', $periodeKey) . '-001',
                'kode_akun' => '5101 - Beban Gaji SDM',
                'keterangan' => 'Pengakuan Beban Gaji & Tunjangan Karyawan Periode ' . $periodeKey,
                'debit' => $gajiVal,
                'kredit' => 0,
                'ref' => 'PAYROLL',
            ]);
            $entries->push([
                'tgl' => $periodeKey . '-25',
                'no_jurnal' => 'JRN-' . str_replace('-', '', $periodeKey) . '-001',
                'kode_akun' => '2102 - Utang Gaji SDM',
                'keterangan' => 'Kewajiban Penggajian Karyawan Periode ' . $periodeKey,
                'debit' => 0,
                'kredit' => $gajiVal,
                'ref' => 'PAYROLL',
            ]);
        }

        // 2. Entri Jasa Medis (Jasmed)
        $jasmedVal = JmPasien::where('tgl_checkout', 'like', $periodeKey . '%')->sum('disetujui');
        if ($jasmedVal > 0) {
            $entries->push([
                'tgl' => $periodeKey . '-26',
                'no_jurnal' => 'JRN-' . str_replace('-', '', $periodeKey) . '-002',
                'kode_akun' => '5102 - Beban Jasmed Dokter',
                'keterangan' => 'Pengakuan Beban Remunerasi & Jasa Medis Dokter',
                'debit' => $jasmedVal * 0.45,
                'kredit' => 0,
                'ref' => 'JASMED',
            ]);
            $entries->push([
                'tgl' => $periodeKey . '-26',
                'no_jurnal' => 'JRN-' . str_replace('-', '', $periodeKey) . '-002',
                'kode_akun' => '2103 - Utang Jasmed Dokter',
                'keterangan' => 'Utang Remunerasi Dokter Pembagian Jasa Medis',
                'debit' => 0,
                'kredit' => $jasmedVal * 0.45,
                'ref' => 'JASMED',
            ]);
        }

        // 3. Entri Pembelian Supplier Farmasi
        $pembelianVal = Pembelian::where('created_at', 'like', $periodeKey . '%')->sum('total') ?? 0;
        if ($pembelianVal > 0) {
            $entries->push([
                'tgl' => $periodeKey . '-28',
                'no_jurnal' => 'JRN-' . str_replace('-', '', $periodeKey) . '-003',
                'kode_akun' => '1104 - Persediaan Obat & BHP',
                'keterangan' => 'Pembelian Obat, Alkes, dan BHP Farmasi Supplier',
                'debit' => $pembelianVal,
                'kredit' => 0,
                'ref' => 'PURCHASING',
            ]);
            $entries->push([
                'tgl' => $periodeKey . '-28',
                'no_jurnal' => 'JRN-' . str_replace('-', '', $periodeKey) . '-003',
                'kode_akun' => '2101 - Utang Supplier Farmasi',
                'keterangan' => 'Kewajiban Tagihan Faktur Supplier Pembelian Obat',
                'debit' => 0,
                'kredit' => $pembelianVal,
                'ref' => 'PURCHASING',
            ]);
        }

        return $entries->when($this->search, function ($col) {
            return $col->filter(function ($item) {
                return str_contains(strtolower($item['no_jurnal']), strtolower($this->search))
                    || str_contains(strtolower($item['kode_akun']), strtolower($this->search))
                    || str_contains(strtolower($item['keterangan']), strtolower($this->search));
            });
        });
    }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.akuntansi.jurnal.index', [
            'entries' => $this->jurnalEntries,
        ]);
    }
}
