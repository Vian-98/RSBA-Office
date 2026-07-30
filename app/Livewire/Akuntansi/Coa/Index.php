<?php

namespace App\Livewire\Akuntansi\Coa;

use Carbon\Carbon;
use App\Models\JmPasien;
use App\Models\Gudang\Pembelian;
use App\Traits\AuthorizesFromRoute;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Chart Of Account')]
class Index extends Component
{
    use AuthorizesFromRoute, WithPagination;

    public string $search = '';
    public string $kategori = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedKategori(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function getAccountsProperty()
    {
        // Total Angka Real Dari Database Transaksi
        $realBpjsClaim = JmPasien::where('cabar', 'bpjs')->sum('klaim') ?? 0;
        $realBpjsApproved = JmPasien::where('cabar', 'bpjs')->sum('disetujui') ?? 0;
        $realJkmdClaim = JmPasien::where('cabar', 'jkmd')->sum('klaim') ?? 0;
        $realJkmdApproved = JmPasien::where('cabar', 'jkmd')->sum('disetujui') ?? 0;
        $realTunai = JmPasien::where('cabar', 'tunai')->sum('klaim') ?? 0;

        $realPembelianTotal = Pembelian::sum('total') ?? 0;
        $realPembelianHutang = Pembelian::where(function ($q) {
            $q->where('status_pembayaran', '!=', 'lunas')->orWhereNull('status_pembayaran');
        })->sum('total') ?? 0;

        $realPayrollGaji = DB::table('sdm_payroll_slips')->sum('gaji_bersih') ?? 0;
        $realMaintenanceBiaya = \Illuminate\Support\Facades\Schema::hasTable('asset_maintc_requests') ? (DB::table('asset_maintc_requests')->count() * 500000) : 0;

        $piutangBpjs = max(0, $realBpjsClaim - $realBpjsApproved);
        $piutangJkmd = max(0, $realJkmdClaim - $realJkmdApproved);
        $utangJasmed = ($realBpjsApproved + $realJkmdApproved + $realTunai) * 0.4;
        $bebanJasmed = ($realBpjsApproved + $realJkmdApproved + $realTunai) * 0.45;

        $kasBank = max(0, $realTunai + $realBpjsApproved - $realPayrollGaji - ($realPembelianTotal - $realPembelianHutang));

        $totalPendapatan = $realBpjsApproved + $realJkmdApproved + $realTunai;
        $totalBeban = $realPayrollGaji + $bebanJasmed + $realPembelianTotal + $realMaintenanceBiaya;
        $surplusBerjalan = $totalPendapatan - $totalBeban;

        $defaultCoa = collect([
            // 1. Aset
            ['kode' => '1101', 'nama' => 'Kas & Bank Utama RS', 'kategori' => 'aset', 'saldo_normal' => 'debit', 'saldo' => $kasBank],
            ['kode' => '1102', 'nama' => 'Piutang Klaim BPJS Kesehatan', 'kategori' => 'aset', 'saldo_normal' => 'debit', 'saldo' => $piutangBpjs],
            ['kode' => '1103', 'nama' => 'Piutang Klaim Pemda JKMD', 'kategori' => 'aset', 'saldo_normal' => 'debit', 'saldo' => $piutangJkmd],
            ['kode' => '1104', 'nama' => 'Persediaan Obat & BHP Farmasi', 'kategori' => 'aset', 'saldo_normal' => 'debit', 'saldo' => $realPembelianTotal],
            ['kode' => '1201', 'nama' => 'Aset Tetap Gedung & Sarana RS', 'kategori' => 'aset', 'saldo_normal' => 'debit', 'saldo' => 8500000000],
            ['kode' => '1202', 'nama' => 'Aset Tetap Peralatan & Mesin Medis', 'kategori' => 'aset', 'saldo_normal' => 'debit', 'saldo' => 3200000000],

            // 2. Kewajiban
            ['kode' => '2101', 'nama' => 'Utang Usaha Supplier Farmasi', 'kategori' => 'kewajiban', 'saldo_normal' => 'kredit', 'saldo' => $realPembelianHutang],
            ['kode' => '2102', 'nama' => 'Utang Gaji & Tunjangan SDM', 'kategori' => 'kewajiban', 'saldo_normal' => 'kredit', 'saldo' => $realPayrollGaji],
            ['kode' => '2103', 'nama' => 'Utang Remunerasi Dokter (Jasmed)', 'kategori' => 'kewajiban', 'saldo_normal' => 'kredit', 'saldo' => $utangJasmed],

            // 3. Ekuitas
            ['kode' => '3101', 'nama' => 'Ekuitas Awal RS Bintang Amin', 'kategori' => 'ekuitas', 'saldo_normal' => 'kredit', 'saldo' => 10000000000],
            ['kode' => '3201', 'nama' => 'Surplus / (Defisit) Periode Berjalan', 'kategori' => 'ekuitas', 'saldo_normal' => 'kredit', 'saldo' => $surplusBerjalan],

            // 4. Pendapatan
            ['kode' => '4101', 'nama' => 'Pendapatan Layanan Pasien BPJS', 'kategori' => 'pendapatan', 'saldo_normal' => 'kredit', 'saldo' => $realBpjsApproved],
            ['kode' => '4102', 'nama' => 'Pendapatan Layanan Pasien JKMD', 'kategori' => 'pendapatan', 'saldo_normal' => 'kredit', 'saldo' => $realJkmdApproved],
            ['kode' => '4103', 'nama' => 'Pendapatan Pasien Tunai / Umum', 'kategori' => 'pendapatan', 'saldo_normal' => 'kredit', 'saldo' => $realTunai],

            // 5. Beban
            ['kode' => '5101', 'nama' => 'Beban Gaji & Tunjangan SDM', 'kategori' => 'beban', 'saldo_normal' => 'debit', 'saldo' => $realPayrollGaji],
            ['kode' => '5102', 'nama' => 'Beban Jasa Medis Dokter (Jasmed)', 'kategori' => 'beban', 'saldo_normal' => 'debit', 'saldo' => $bebanJasmed],
            ['kode' => '5103', 'nama' => 'Beban Obat & BHP Farmasi', 'kategori' => 'beban', 'saldo_normal' => 'debit', 'saldo' => $realPembelianTotal],
            ['kode' => '5104', 'nama' => 'Beban Pemeliharaan & Maintenance Asset', 'kategori' => 'beban', 'saldo_normal' => 'debit', 'saldo' => $realMaintenanceBiaya],
        ]);

        return $defaultCoa
            ->when($this->kategori, fn($col) => $col->where('kategori', $this->kategori))
            ->when($this->search, function ($col) {
                return $col->filter(function ($item) {
                    return str_contains(strtolower($item['kode']), strtolower($this->search))
                        || str_contains(strtolower($item['nama']), strtolower($this->search));
                });
            });
    }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.akuntansi.coa.index', [
            'accounts' => $this->accounts,
        ]);
    }
}
