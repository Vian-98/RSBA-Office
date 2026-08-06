<!-- Stats Grid Section -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    @if(isset($stats['karyawan_count']))
        <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{ isset($stats['ruangan_nama']) ? 'Karyawan ' . $stats['ruangan_nama'] : 'Total Karyawan' }}
                    </p>
                    <h3 class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['karyawan_count'] }}</h3>
                </div>
                <div class="rounded-xl bg-indigo-50 p-3 text-indigo-500 transition-colors duration-300 group-hover:bg-indigo-500 group-hover:text-white">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A11.386 11.386 0 0 1 10.089 20.8M4.12 18.046a8.81 8.81 0 0 1 4.12-.953c2.25 0 4.307.837 5.89 2.22M4.12 18.046a9.01 9.01 0 0 0-2.626.372c-.521.144-.915.544-1.019 1.077-.042.219-.071.44-.085.664a8.81 8.81 0 0 0 7.848 8.04M4.12 18.046a9.39 9.39 0 0 1 5.97-2.176M9 11.25a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM18 10.5a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1 text-xs text-indigo-600">
                <span class="font-medium">SDM & Kepegawaian</span>
            </div>
        </div>
    @endif

    @if(isset($stats['barang_count']))
        <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Barang Master</p>
                    <h3 class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['barang_count'] }}</h3>
                </div>
                <div class="rounded-xl bg-teal-50 p-3 text-teal-500 transition-colors duration-300 group-hover:bg-teal-500 group-hover:text-white">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1 text-xs text-teal-600">
                <span class="font-medium">Stok & Gudang</span>
            </div>
        </div>
    @endif

    @if(isset($stats['total_hutang']))
        <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tagihan Belum Bayar</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-800">{{ formatRupiah($stats['total_hutang'], true, false) }}</h3>
                </div>
                <div class="rounded-xl bg-rose-50 p-3 text-rose-500 transition-colors duration-300 group-hover:bg-rose-500 group-hover:text-white">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h.007v.008H3.75V4.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3 19.5h10.5m-10.5-6h9.75M3 9h10.5M19 5.25L16.5 7.75M16.5 5.25l2.5 2.5" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1 text-xs text-rose-600">
                <span class="font-medium">Keuangan & Hutang</span>
            </div>
        </div>
    @endif

    @if(isset($stats['pending_cuti']))
        <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cuti Pending</p>
                    <h3 class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['pending_cuti'] }}</h3>
                </div>
                <div class="rounded-xl bg-amber-50 p-3 text-amber-500 transition-colors duration-300 group-hover:bg-amber-500 group-hover:text-white">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75M9 11.25h.008v.008H9v-.008Zm3 0h.008v.008H12v-.008Zm3 0h.008v.008H15v-.008Zm-6 3h.008v.008H9v-.008Zm3 0h.008v.008H12v-.008Zm3 0h.008v.008H15v-.008Zm-6 3h.008v.008H9v-.008Zm3 0h.008v.008H12v-.008Zm3 0h.008v.008H15v-.008Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1 text-xs text-amber-600">
                <span class="font-medium">Menunggu Verifikasi</span>
            </div>
        </div>
    @endif

    @if(isset($stats['supplier_count']) && !isset($stats['barang_count']))
        <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mitra Supplier</p>
                    <h3 class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['supplier_count'] }}</h3>
                </div>
                <div class="rounded-xl bg-purple-50 p-3 text-purple-500 transition-colors duration-300 group-hover:bg-purple-500 group-hover:text-white">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.119c0-.451-.088-.892-.258-1.307a14.248 14.248 0 0 0-3.29-4.884 1.5 1.5 0 0 0-2.22 0 14.25 14.25 0 0 0-3.29 4.884c-.17.415-.258.856-.258 1.307v.119M16.5 18.75h-2.25" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1 text-xs text-purple-600">
                <span class="font-medium">Relasi Umum</span>
            </div>
        </div>
    @endif

    @if(isset($stats['total_pembayaran']))
        <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Hutang Terbayar</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-800">{{ formatRupiah($stats['total_pembayaran'], true, false) }}</h3>
                </div>
                <div class="rounded-xl bg-emerald-50 p-3 text-emerald-500 transition-colors duration-300 group-hover:bg-emerald-500 group-hover:text-white">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1 text-xs text-emerald-600">
                <span class="font-medium">Periode Transaksi</span>
            </div>
        </div>
    @endif
</div>
