<div class="space-y-6">
    {{-- Summary Header Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Pasien Tunai</span>
            <h3 class="mt-1 text-2xl font-extrabold text-slate-800">{{ $summary['totalPasien'] }}</h3>
            <p class="text-xs text-slate-400 mt-0.5">Pasien Berbayar Tunai</p>
        </div>

        <div class="rounded-2xl border border-indigo-100 bg-indigo-50/40 p-5 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700">Total Tagihan Jasa</span>
            <h3 class="mt-1 text-2xl font-extrabold text-indigo-900">{{ $summary['totalKlaim'] }}</h3>
            <p class="text-xs text-indigo-600 mt-0.5">Billing Jasa Medis</p>
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/40 p-5 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Telah Disetujui</span>
            <h3 class="mt-1 text-2xl font-extrabold text-emerald-900">{{ $summary['totalDisetujui'] }}</h3>
            <p class="text-xs text-emerald-600 mt-0.5">Remunerasi Valid</p>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-amber-50/40 p-5 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Pending Verifikasi</span>
            <h3 class="mt-1 text-2xl font-extrabold text-amber-900">{{ $summary['totalPending'] }}</h3>
            <p class="text-xs text-amber-600 mt-0.5">Menunggu Approval</p>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                {{-- Periode Month Filter --}}
                <div class="w-full sm:w-44">
                    <x-ts:input wire:model.live.debounce.300ms="periode" type="month" label="Periode Checkout" />
                </div>

                {{-- Layanan Select --}}
                <div class="w-full sm:w-40">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Layanan</label>
                    <select wire:model.live="layanan" class="w-full rounded-xl border-slate-200 text-xs text-slate-700 font-semibold focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua Layanan</option>
                        <option value="rajal">Rawat Jalan (Rajal)</option>
                        <option value="ranap">Rawat Inap (Ranap)</option>
                    </select>
                </div>
            </div>

            {{-- Search Box --}}
            <div class="w-full sm:w-72">
                <x-ts:input wire:model.live.debounce.300ms="search" icon="tabler.search" placeholder="Cari Nama / MRN / SEP..." />
            </div>
        </div>

        {{-- Table Section --}}
        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Pasien & MRN</th>
                        <th class="px-4 py-3">SEP / Reg</th>
                        <th class="px-4 py-3">Tgl Checkout</th>
                        <th class="px-4 py-3">Layanan</th>
                        <th class="px-4 py-3 text-right">Tagihan Klaim</th>
                        <th class="px-4 py-3 text-right">Jasa Disetujui</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($pasien as $index => $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-400">
                                {{ $pasien->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-800">
                                {{ $item->nama_pasien ?? '-' }}
                                <span class="block text-[10px] font-semibold text-slate-400">MRN: {{ $item->mrn ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 font-mono text-[11px]">
                                {{ $item->sep ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ \Carbon\Carbon::parse($item->tgl_checkout)->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $item->layanan === 'ranap' ? 'bg-purple-50 text-purple-700 border border-purple-100' : 'bg-blue-50 text-blue-700 border border-blue-100' }}">
                                    {{ strtoupper($item->layanan) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-800">
                                Rp {{ number_format($item->klaim, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-700">
                                Rp {{ number_format($item->disetujui, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($item->disetujui > 0)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        Disetujui
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-100">
                                        Pending
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                Belum ada data Jasa Medis Pasien Tunai untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($pasien->hasPages())
            <div class="pt-2">
                {{ $pasien->links() }}
            </div>
        @endif
    </div>
</div>
