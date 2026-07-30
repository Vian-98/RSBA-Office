<div class="space-y-6">
    {{-- Header Banner Card --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100 relative overflow-hidden">
        <div class="absolute -right-6 -top-6 h-32 w-32 rounded-full bg-emerald-50/60 blur-xl"></div>
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-100">
                    <x-ts:icon name="tabler.report-analytics" class="h-3.5 w-3.5 text-emerald-600" />
                    Laporan Keuangan Integratif (PSAP 13 / PMK 129)
                </div>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Akuntansi & Laporan Keuangan RS</h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500 font-medium">
                    Konsolidasi data real-time dari Payroll Gaji, Jasa Medis (Jasmed), Pembelian Gudang, Hutang Supplier, & Piutang Klaim.
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <div class="w-44">
                    <x-ts:input wire:model.live.debounce.300ms="periode" type="month" label="Periode Laporan" />
                </div>
            </div>
        </div>
    </div>

    {{-- Executive Summary Stat Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700">Total Pendapatan</span>
            <h3 class="mt-1 text-xl font-extrabold text-indigo-900">{{ $summary['totalPendapatan'] }}</h3>
            <p class="text-[11px] text-indigo-600 mt-0.5">BPJS, JKMD & Pasien Tunai</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-600">Total Beban Operasional</span>
            <h3 class="mt-1 text-xl font-extrabold text-slate-800">{{ $summary['totalBeban'] }}</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Gaji, Jasmed, BHP & Maint.</p>
        </div>

        <div class="rounded-2xl border {{ $summary['isSurplus'] ? 'border-emerald-100 bg-emerald-50/50' : 'border-rose-100 bg-rose-50/50' }} p-4 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-wider {{ $summary['isSurplus'] ? 'text-emerald-700' : 'text-rose-700' }}">Surplus / Defisit</span>
            <h3 class="mt-1 text-xl font-extrabold {{ $summary['isSurplus'] ? 'text-emerald-900' : 'text-rose-900' }}">{{ $summary['surplusDefisit'] }}</h3>
            <p class="text-[11px] {{ $summary['isSurplus'] ? 'text-emerald-600' : 'text-rose-600' }} mt-0.5">Hasil Operasional Periode Ini</p>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-amber-50/50 p-4 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700">Piutang Klaim Pending</span>
            <h3 class="mt-1 text-xl font-extrabold text-amber-900">{{ $summary['piutangKlaim'] }}</h3>
            <p class="text-[11px] text-amber-600 mt-0.5">Klaim BPJS & JKMD Unpaid</p>
        </div>

        <div class="rounded-2xl border border-purple-100 bg-purple-50/50 p-4 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-700">Hutang Supplier</span>
            <h3 class="mt-1 text-xl font-extrabold text-purple-900">{{ $summary['hutangSupplier'] }}</h3>
            <p class="text-[11px] text-purple-600 mt-0.5">Kewajiban Pembelian Farmasi</p>
        </div>
    </div>

    {{-- Main Tab Navigation Bar --}}
    <div class="overflow-x-auto scrollbar-hidden pb-1">
        <nav class="inline-flex gap-2.5 sm:gap-3 min-w-max p-1.5 bg-slate-100/80 rounded-2xl border border-slate-200/60" aria-label="Keuangan Tabs">
            <button wire:click="setTab('hutang')"
                class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ $tab === 'hutang' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                <x-ts:icon name="tabler.receipt-2" class="h-4 w-4 shrink-0" />
                Hutang Supplier
            </button>

            <button wire:click="setTab('piutang')"
                class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ $tab === 'piutang' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                <x-ts:icon name="tabler.file-invoice" class="h-4 w-4 shrink-0" />
                Piutang Klaim
            </button>

            <button wire:click="setTab('buku_besar')"
                class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ $tab === 'buku_besar' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                <x-ts:icon name="tabler.book" class="h-4 w-4 shrink-0" />
                Buku Besar & Jurnal
            </button>

            <button wire:click="setTab('neraca')"
                class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ $tab === 'neraca' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                <x-ts:icon name="tabler.scale" class="h-4 w-4 shrink-0" />
                Neraca (PSAP 13)
            </button>

            <button wire:click="setTab('laba_rugi')"
                class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ $tab === 'laba_rugi' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                <x-ts:icon name="tabler.chart-bar" class="h-4 w-4 shrink-0" />
                Laporan Operasional
            </button>

            <button wire:click="setTab('arus_kas')"
                class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ $tab === 'arus_kas' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                <x-ts:icon name="tabler.cash-banknote" class="h-4 w-4 shrink-0" />
                Arus Kas
            </button>

            <button wire:click="setTab('perubahan_modal')"
                class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ $tab === 'perubahan_modal' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                <x-ts:icon name="tabler.trending-up" class="h-4 w-4 shrink-0" />
                Perubahan Ekuitas
            </button>
        </nav>
    </div>

    {{-- Content Body Section --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100">
        {{-- 1. TAB HUTANG SUPPLIER --}}
        @if($tab === 'hutang')
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pb-2 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Daftar Hutang Usaha & Pembelian Supplier</h3>
                        <p class="text-xs text-slate-500">Integrasi otomatis dari transaksi pengadaan obat, alkes, dan BHP farmasi</p>
                    </div>
                    <div class="w-full sm:w-64">
                        <x-ts:input wire:model.live.debounce.300ms="search" icon="tabler.search" placeholder="Cari No Nota / Supplier..." />
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-100">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Tgl Pembelian</th>
                                <th class="px-4 py-3">No. Nota / Faktur</th>
                                <th class="px-4 py-3">Nama Supplier</th>
                                <th class="px-4 py-3 text-right">Total Tagihan</th>
                                <th class="px-4 py-3 text-center">Status Bayar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                            @forelse($hutangData as $index => $item)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-4 py-3 font-semibold text-slate-400">{{ $hutangData->firstItem() + $index }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3 font-bold text-slate-800 font-mono text-[11px]">{{ $item->no ?? '-' }}</td>
                                    <td class="px-4 py-3 font-bold text-indigo-900">{{ $item->supplier->nama ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right font-extrabold text-slate-900">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if(strtolower($item->status_pembayaran) === 'lunas')
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">Lunas</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-100">Belum Lunas</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400">Tidak ada data hutang supplier pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($hutangData->hasPages())
                    <div class="pt-2">{{ $hutangData->links() }}</div>
                @endif
            </div>
        @endif

        {{-- 2. TAB PIUTANG KLAIM --}}
        @if($tab === 'piutang')
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pb-2 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Daftar Piutang Klaim Pasien & BPJS/JKMD</h3>
                        <p class="text-xs text-slate-500">Integrasi otomatis dari klaim Jasa Medis (INA-CBGs BPJS, Jamkesda JKMD, & Pasien Tunai)</p>
                    </div>
                    <div class="w-full sm:w-64">
                        <x-ts:input wire:model.live.debounce.300ms="search" icon="tabler.search" placeholder="Cari Pasien / MRN / SEP..." />
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-100">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Pasien & MRN</th>
                                <th class="px-4 py-3">Cara Bayar</th>
                                <th class="px-4 py-3">SEP / No. Registrasi</th>
                                <th class="px-4 py-3 text-right">Tagihan Klaim</th>
                                <th class="px-4 py-3 text-right">Klaim Disetujui</th>
                                <th class="px-4 py-3 text-center">Status Pencairan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                            @forelse($piutangData as $index => $item)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-4 py-3 font-semibold text-slate-400">{{ $piutangData->firstItem() + $index }}</td>
                                    <td class="px-4 py-3 font-bold text-slate-800">
                                        {{ $item->nama_pasien }}
                                        <span class="block text-[10px] text-slate-400 font-normal">MRN: {{ $item->mrn }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $item->cabar === 'bpjs' ? 'bg-blue-50 text-blue-700 border border-blue-100' : ($item->cabar === 'jkmd' ? 'bg-purple-50 text-purple-700 border border-purple-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100') }}">
                                            {{ strtoupper($item->cabar) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-[11px] text-slate-600">{{ $item->sep ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right font-extrabold text-slate-900">Rp {{ number_format($item->klaim, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-extrabold text-emerald-700">Rp {{ number_format($item->disetujui, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($item->disetujui > 0)
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">Disetujui</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-100">Pending Klaim</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-400">Tidak ada data piutang klaim pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($piutangData->hasPages())
                    <div class="pt-2">{{ $piutangData->links() }}</div>
                @endif
            </div>
        @endif

        {{-- 3. TAB BUKU BESAR & JURNAL --}}
        @if($tab === 'buku_besar')
            <div class="space-y-4">
                <div class="pb-2 border-b border-slate-100">
                    <h3 class="text-base font-extrabold text-slate-900">Jurnal Umum & Buku Besar (Double-Entry)</h3>
                    <p class="text-xs text-slate-500">Pencatatan saldo Debit dan Kredit otomatis dari transaksi operasional RS</p>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-100">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Ref Trx</th>
                                <th class="px-4 py-3">Kode & Nama Akun</th>
                                <th class="px-4 py-3">Keterangan Transaksi</th>
                                <th class="px-4 py-3 text-right">Debit (Rp)</th>
                                <th class="px-4 py-3 text-right">Kredit (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                            @forelse($bukuBesarData as $entry)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-4 py-3 text-slate-500 font-mono text-[11px]">{{ $entry['tgl'] }}</td>
                                    <td class="px-4 py-3 font-bold text-indigo-700 font-mono text-[11px]">{{ $entry['ref'] }}</td>
                                    <td class="px-4 py-3 font-bold text-slate-900">{{ $entry['kode'] }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $entry['deskripsi'] }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-800">{{ $entry['debit'] > 0 ? 'Rp ' . number_format($entry['debit'], 0, ',', '.') : '-' }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-800">{{ $entry['kredit'] > 0 ? 'Rp ' . number_format($entry['kredit'], 0, ',', '.') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada transaksi jurnal untuk periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- 4. TAB NERACA (PSAP 13) --}}
        @if($tab === 'neraca')
            <div class="space-y-6">
                <div class="pb-2 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Neraca / Laporan Posisi Keuangan (PSAP 13)</h3>
                        <p class="text-xs text-slate-500">Posisi Aset, Kewajiban, dan Ekuitas RS per {{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</p>
                    </div>
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700 border border-indigo-100">Standard BLU/BLUD</span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- SISI ASET (AKTIVA) --}}
                    <div class="rounded-xl border border-slate-200 p-5 space-y-4 bg-slate-50/40">
                        <h4 class="text-sm font-extrabold text-indigo-900 uppercase tracking-wider border-b border-indigo-100 pb-2">A. ASET (AKTIVA)</h4>
                        
                        {{-- Aset Lancar --}}
                        <div class="space-y-2">
                            <span class="text-xs font-bold text-slate-700 uppercase">1. Aset Lancar</span>
                            <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                                <span>Kas dan Setara Kas</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($neracaData['kasBank'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                                <span>Piutang Klaim (BPJS / JKMD / Tunai)</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($neracaData['piutang'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                                <span>Persediaan Obat & BHP</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($neracaData['persediaan'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs font-extrabold text-indigo-900 py-1.5 bg-indigo-50/50 px-2 rounded-lg">
                                <span>Total Aset Lancar</span>
                                <span>Rp {{ number_format($neracaData['totalAsetLancar'], 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Aset Tetap --}}
                        <div class="space-y-2 pt-2">
                            <span class="text-xs font-bold text-slate-700 uppercase">2. Aset Tetap / Tidak Lancar</span>
                            <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                                <span>Gedung & Bangunan Sarana RS</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($neracaData['asetTetapGedung'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                                <span>Peralatan & Mesin Medis</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($neracaData['asetTetapPeralatan'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs py-1 border-b border-slate-100 text-rose-600">
                                <span>Akumulasi Penyusutan Aset</span>
                                <span class="font-bold">Rp {{ number_format($neracaData['akumulasiPenyusutan'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs font-extrabold text-indigo-900 py-1.5 bg-indigo-50/50 px-2 rounded-lg">
                                <span>Total Aset Tetap (Netto)</span>
                                <span>Rp {{ number_format($neracaData['totalAsetTetap'], 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between text-sm font-extrabold text-white bg-indigo-600 p-3 rounded-xl shadow-sm">
                            <span>TOTAL ASET</span>
                            <span>Rp {{ number_format($neracaData['totalAset'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- SISI KEWAJIBAN & EKUITAS (PASIVA) --}}
                    <div class="rounded-xl border border-slate-200 p-5 space-y-4 bg-slate-50/40">
                        <h4 class="text-sm font-extrabold text-indigo-900 uppercase tracking-wider border-b border-indigo-100 pb-2">B. KEWAJIBAN & EKUITAS (PASIVA)</h4>
                        
                        {{-- Kewajiban --}}
                        <div class="space-y-2">
                            <span class="text-xs font-bold text-slate-700 uppercase">1. Kewajiban Jangka Pendek</span>
                            <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                                <span>Utang Usaha Supplier Farmasi</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($neracaData['utangSupplier'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                                <span>Utang Gaji & Tunjangan SDM</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($neracaData['utangGaji'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                                <span>Utang Remunerasi Dokter (Jasmed)</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($neracaData['utangJasmed'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs font-extrabold text-purple-900 py-1.5 bg-purple-50/50 px-2 rounded-lg">
                                <span>Total Kewajiban</span>
                                <span>Rp {{ number_format($neracaData['totalKewajiban'], 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Ekuitas --}}
                        <div class="space-y-2 pt-2">
                            <span class="text-xs font-bold text-slate-700 uppercase">2. Ekuitas</span>
                            <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                                <span>Ekuitas Awal Saldo</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($neracaData['ekuitasAwal'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                                <span>Surplus / (Defisit) Periode Berjalan</span>
                                <span class="font-bold {{ $neracaData['surplusBerjalan'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($neracaData['surplusBerjalan'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs font-extrabold text-indigo-900 py-1.5 bg-indigo-50/50 px-2 rounded-lg">
                                <span>Total Ekuitas</span>
                                <span>Rp {{ number_format($neracaData['totalEkuitas'], 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between text-sm font-extrabold text-white bg-slate-900 p-3 rounded-xl shadow-sm">
                            <span>TOTAL KEWAJIBAN & EKUITAS</span>
                            <span>Rp {{ number_format($neracaData['totalPasiva'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 5. TAB LABA RUGI / LAPORAN OPERASIONAL --}}
        @if($tab === 'laba_rugi')
            <div class="space-y-6">
                <div class="pb-2 border-b border-slate-100">
                    <h3 class="text-base font-extrabold text-slate-900">Laporan Operasional / Laba Rugi</h3>
                    <p class="text-xs text-slate-500">Perhitungan Pendapatan vs Beban Operasional RS Periode {{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</p>
                </div>

                <div class="max-w-3xl mx-auto rounded-2xl border border-slate-200 p-6 space-y-6 bg-slate-50/30">
                    {{-- Pendapatan --}}
                    <div class="space-y-3">
                        <h4 class="text-xs font-extrabold text-indigo-700 uppercase tracking-wider border-b border-indigo-100 pb-1">I. PENDAPATAN OPERASIONAL</h4>
                        <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                            <span>Pendapatan Layanan BPJS Kesehatan</span>
                            <span class="font-bold text-slate-800">Rp {{ number_format($labaRugiData['pendapatanBPJS'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                            <span>Pendapatan Layanan JKMD / Jamkesda</span>
                            <span class="font-bold text-slate-800">Rp {{ number_format($labaRugiData['pendapatanJKMD'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                            <span>Pendapatan Layanan Pasien Tunai / Umum</span>
                            <span class="font-bold text-slate-800">Rp {{ number_format($labaRugiData['pendapatanTunai'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs font-extrabold text-indigo-900 bg-indigo-50 p-2.5 rounded-xl">
                            <span>JUMLAH PENDAPATAN OPERASIONAL</span>
                            <span>Rp {{ number_format($labaRugiData['totalPendapatan'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Beban --}}
                    <div class="space-y-3">
                        <h4 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider border-b border-slate-200 pb-1">II. BEBAN OPERASIONAL</h4>
                        <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                            <span>Beban Gaji & Tunjangan SDM</span>
                            <span class="font-bold text-slate-800">Rp {{ number_format($labaRugiData['bebanGaji'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                            <span>Beban Remunerasi & Jasa Medis Dokter</span>
                            <span class="font-bold text-slate-800">Rp {{ number_format($labaRugiData['bebanJasmed'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                            <span>Beban Farmasi, Obat, & Alkes (HPP)</span>
                            <span class="font-bold text-slate-800">Rp {{ number_format($labaRugiData['bebanFarmasi'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                            <span>Beban Pemeliharaan & Servis Asset</span>
                            <span class="font-bold text-slate-800">Rp {{ number_format($labaRugiData['bebanMaintenance'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                            <span>Beban Umum & Operasional Kantor</span>
                            <span class="font-bold text-slate-800">Rp {{ number_format($labaRugiData['bebanOperasionalLain'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs font-extrabold text-slate-900 bg-slate-100 p-2.5 rounded-xl">
                            <span>JUMLAH BEBAN OPERASIONAL</span>
                            <span>Rp {{ number_format($labaRugiData['totalBeban'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Hasil --}}
                    <div class="flex justify-between text-sm font-extrabold text-white {{ $labaRugiData['surplusDefisit'] >= 0 ? 'bg-emerald-600' : 'bg-rose-600' }} p-4 rounded-xl shadow-md">
                        <span>SURPLUS / (DEFISIT) OPERASIONAL BERJALAN</span>
                        <span>Rp {{ number_format($labaRugiData['surplusDefisit'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- 6. TAB ARUS KAS --}}
        @if($tab === 'arus_kas')
            <div class="space-y-6">
                <div class="pb-2 border-b border-slate-100">
                    <h3 class="text-base font-extrabold text-slate-900">Laporan Arus Kas (Cash Flow Statement)</h3>
                    <p class="text-xs text-slate-500">Penerimaan dan Pengeluaran Kas untuk Aktivitas Operasi, Investasi, & Pendanaan</p>
                </div>

                <div class="max-w-3xl mx-auto rounded-2xl border border-slate-200 p-6 space-y-5 bg-slate-50/30 text-xs">
                    {{-- Operasi --}}
                    <div class="space-y-2">
                        <h4 class="font-extrabold text-indigo-900 uppercase">1. Arus Kas dari Aktivitas Operasi</h4>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span>Penerimaan Kas dari Pasien & Klaim BPJS/JKMD</span>
                            <span class="font-bold text-emerald-700">+ Rp {{ number_format($arusKasData['penerimaanKasPasien'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span>Pembayaran Kas untuk Gaji SDM</span>
                            <span class="font-bold text-rose-600">- Rp {{ number_format($arusKasData['pembayaranGaji'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span>Pembayaran Kas kepada Supplier Farmasi</span>
                            <span class="font-bold text-rose-600">- Rp {{ number_format($arusKasData['pembayaranSupplier'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span>Pembayaran Remunerasi Dokter (Jasmed)</span>
                            <span class="font-bold text-rose-600">- Rp {{ number_format($arusKasData['pembayaranJasmed'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span>Pembayaran Operasional & Maintenance</span>
                            <span class="font-bold text-rose-600">- Rp {{ number_format($arusKasData['pembayaranOperasional'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between font-extrabold text-indigo-900 bg-indigo-50 p-2 rounded-lg">
                            <span>Arus Kas Netto dari Aktivitas Operasi</span>
                            <span>Rp {{ number_format($arusKasData['arusKasOperasi'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Investasi --}}
                    <div class="space-y-2 pt-2">
                        <h4 class="font-extrabold text-indigo-900 uppercase">2. Arus Kas dari Aktivitas Investasi</h4>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span>Pengadaan Asset & Peralatan Medis Baru</span>
                            <span class="font-bold text-rose-600">Rp {{ number_format($arusKasData['arusKasInvestasi'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between font-extrabold text-indigo-900 bg-indigo-50 p-2 rounded-lg">
                            <span>Arus Kas Netto dari Aktivitas Investasi</span>
                            <span>Rp {{ number_format($arusKasData['arusKasInvestasi'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Rekap --}}
                    <div class="pt-3 space-y-2 border-t border-slate-300">
                        <div class="flex justify-between font-bold py-1">
                            <span>Saldo Kas & Bank Awal Periode</span>
                            <span>Rp {{ number_format($arusKasData['kasAwal'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between font-extrabold text-sm text-white bg-indigo-600 p-3 rounded-xl shadow-sm">
                            <span>SALDO KAS & BANK AKHIR PERIODE</span>
                            <span>Rp {{ number_format($arusKasData['kasAkhir'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 7. TAB PERUBAHAN EKUITAS --}}
        @if($tab === 'perubahan_modal')
            <div class="space-y-6">
                <div class="pb-2 border-b border-slate-100">
                    <h3 class="text-base font-extrabold text-slate-900">Laporan Perubahan Ekuitas / Modal</h3>
                    <p class="text-xs text-slate-500">Rekonsiliasi Modal Awal hingga Saldo Ekuitas Akhir Rumah Sakit</p>
                </div>

                <div class="max-w-2xl mx-auto rounded-2xl border border-slate-200 p-6 space-y-4 bg-slate-50/30 text-xs">
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="font-bold text-slate-700">Saldo Ekuitas Awal Periode</span>
                        <span class="font-extrabold text-slate-900">Rp {{ number_format($perubahanModalData['ekuitasAwal'], 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="font-bold text-slate-700">Surplus / (Defisit) Operasional Periode Berjalan</span>
                        <span class="font-extrabold {{ $perubahanModalData['surplusBerjalan'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($perubahanModalData['surplusBerjalan'], 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="font-bold text-slate-700">Koreksi Saldo Ekuitas / Penyesuaian</span>
                        <span class="font-extrabold text-slate-500">Rp {{ number_format($perubahanModalData['koreksiSaldo'], 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between font-extrabold text-sm text-white bg-slate-900 p-4 rounded-xl shadow-md">
                        <span>SALDO EKUITAS AKHIR PERIODE</span>
                        <span>Rp {{ number_format($perubahanModalData['ekuitasAkhir'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
