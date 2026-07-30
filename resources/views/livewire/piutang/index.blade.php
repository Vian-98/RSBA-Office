<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 border border-blue-100">
                <x-ts:icon name="tabler.file-invoice" class="h-3.5 w-3.5 text-blue-600" />
                Piutang & Realisasi Klaim
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Piutang Klaim Pasien</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500 font-medium">
                Pengelolaan piutang tagihan klaim BPJS Kesehatan, Jamkesda JKMD, dan Billing Pasien Umum/Tunai.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <div class="w-44">
                <x-ts:input wire:model.live.debounce.300ms="periode" type="month" label="Periode Checkout" />
            </div>
            <x-ts:button sm icon="tabler.file-plus" class="py-2.5 px-4 font-bold mt-5" x-on:click="$dispatch('open-modal', {id:'modal-new-invoices'})">
                Buat Invoice Klaim
            </x-ts:button>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-5 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700">Total Tagihan Piutang</span>
            <h3 class="mt-1 text-2xl font-extrabold text-indigo-900">{{ $summary['totalPiutang'] }}</h3>
            <p class="text-xs text-indigo-600 font-semibold mt-1">Seluruh Klaim Diajukan</p>
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Telah Disetujui / Cair</span>
            <h3 class="mt-1 text-2xl font-extrabold text-emerald-900">{{ $summary['totalCair'] }}</h3>
            <p class="text-xs text-emerald-600 font-semibold mt-1">Realisasi Klaim Valid</p>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700">Pending Unpaid Klaim</span>
            <h3 class="mt-1 text-2xl font-extrabold text-amber-900">{{ $summary['totalPending'] }}</h3>
            <p class="text-xs text-amber-600 font-semibold mt-1">Sisa Piutang Berjalan</p>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-blue-50/50 p-5 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-700">Piutang BPJS Kesehatan</span>
            <h3 class="mt-1 text-2xl font-extrabold text-blue-900">{{ $summary['piutangBPJS'] }}</h3>
            <p class="text-xs text-blue-600 font-semibold mt-1">Klaim INA-CBGs BPJS</p>
        </div>
    </div>

    {{-- Filter & Data Table Card --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100 space-y-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pb-2 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-44">
                    <label class="block text-xs font-bold text-slate-600 mb-1">Cara Bayar</label>
                    <select wire:model.live="cabar" class="w-full rounded-xl border-slate-200 text-xs text-slate-700 font-semibold focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua Cara Bayar</option>
                        <option value="bpjs">BPJS Kesehatan</option>
                        <option value="jkmd">JKMD / Jamkesda</option>
                        <option value="tunai">Pasien Tunai / Umum</option>
                    </select>
                </div>
            </div>

            <div class="w-full sm:w-72">
                <x-ts:input wire:model.live.debounce.300ms="search" icon="tabler.search" placeholder="Cari Nama / MRN / SEP..." />
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
                        <th class="px-4 py-3">Tgl Checkout</th>
                        <th class="px-4 py-3 text-right">Tagihan Klaim</th>
                        <th class="px-4 py-3 text-right">Disetujui / Cair</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($piutangList as $index => $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-400">{{ $piutangList->firstItem() + $index }}</td>
                            <td class="px-4 py-3 font-bold text-slate-900">
                                {{ $item->nama_pasien ?? '-' }}
                                <span class="block text-[10px] text-slate-400 font-semibold">MRN: {{ $item->mrn ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $item->cabar === 'bpjs' ? 'bg-blue-50 text-blue-700 border border-blue-100' : ($item->cabar === 'jkmd' ? 'bg-purple-50 text-purple-700 border border-purple-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100') }}">
                                    {{ strtoupper($item->cabar) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-[11px] text-slate-600">{{ $item->sep ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ \Carbon\Carbon::parse($item->tgl_checkout)->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-right font-extrabold text-slate-900">Rp {{ number_format($item->klaim, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-extrabold text-emerald-700">Rp {{ number_format($item->disetujui, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($item->disetujui > 0)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">Disetujui</span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-100">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada data piutang klaim untuk kriteria ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($piutangList->hasPages())
            <div class="pt-2">{{ $piutangList->links() }}</div>
        @endif
    </div>

    {{-- Modal New Invoice --}}
    <x-filament::modal id="modal-new-invoices" width="w-11/12">
        <x-slot:heading>Buat Invoice Klaim Baru</x-slot:heading>
        <livewire:Piutang.AddInvoice key="add-new-invoice" />
    </x-filament::modal>
</div>
