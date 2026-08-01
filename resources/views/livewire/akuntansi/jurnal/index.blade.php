<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700 border border-indigo-100">
                <x-ts:icon name="tabler.notebook" class="h-3.5 w-3.5 text-indigo-600" />
                Pencatatan Akuntansi Double-Entry
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Jurnal Umum (General Journal)</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500 font-medium">
                Pencatatan saldo Debit dan Kredit otomatis dari Payroll Gaji, Jasmed, Pembelian Gudang, dan Jurnal Manual.
            </p>
        </div>

        <div class="flex items-end gap-3 shrink-0">
            <div class="w-44">
                <x-ts:input wire:model.live.debounce.300ms="periode" type="month" label="Periode Jurnal" />
            </div>
            <x-ts:button sm icon="tabler.plus" class="py-2 px-4 font-bold h-[38px] mb-[1px]" x-on:click="$dispatch('open-modal', {id:'modal-new-jurnal'})">
                Jurnal Manual
            </x-ts:button>
        </div>
    </div>

    {{-- Journal Stat Cards --}}
    @php
        $totalDebit = $entries->sum('debit');
        $totalKredit = $entries->sum('kredit');
        $isBalanced = $totalDebit === $totalKredit;
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700">Total Debit (Rp)</span>
                <span class="rounded-lg bg-indigo-100 p-1.5 text-indigo-600">
                    <x-ts:icon name="tabler.arrow-down-left" class="h-4 w-4" />
                </span>
            </div>
            <h3 class="mt-2 text-2xl font-extrabold text-indigo-900">Rp {{ number_format($totalDebit, 0, ',', '.') }}</h3>
            <p class="text-xs text-indigo-600 font-semibold mt-1">Total Saldo Debit Periode Ini</p>
        </div>

        <div class="rounded-2xl border border-purple-100 bg-purple-50/50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-700">Total Kredit (Rp)</span>
                <span class="rounded-lg bg-purple-100 p-1.5 text-purple-600">
                    <x-ts:icon name="tabler.arrow-up-right" class="h-4 w-4" />
                </span>
            </div>
            <h3 class="mt-2 text-2xl font-extrabold text-purple-900">Rp {{ number_format($totalKredit, 0, ',', '.') }}</h3>
            <p class="text-xs text-purple-600 font-semibold mt-1">Total Saldo Kredit Periode Ini</p>
        </div>

        <div class="rounded-2xl border {{ $isBalanced ? 'border-emerald-100 bg-emerald-50/50' : 'border-rose-100 bg-rose-50/50' }} p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider {{ $isBalanced ? 'text-emerald-700' : 'text-rose-700' }}">Status Keseimbangan</span>
                <span class="rounded-full {{ $isBalanced ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }} px-2 py-0.5 text-[10px] font-bold">
                    {{ $isBalanced ? 'VALID' : 'UNBALANCED' }}
                </span>
            </div>
            <h3 class="mt-2 text-2xl font-extrabold {{ $isBalanced ? 'text-emerald-900' : 'text-rose-900' }}">{{ $isBalanced ? 'BALANCED (SEIMBANG)' : 'UNBALANCED' }}</h3>
            <p class="text-xs {{ $isBalanced ? 'text-emerald-600' : 'text-rose-600' }} font-semibold mt-1">Double-Entry Verification</p>
        </div>
    </div>

    {{-- Filter & Data Table --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100 space-y-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pb-2 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Transaksi Jurnal Umum</h3>
                <p class="text-xs text-slate-500">Rincian posting entri jurnal debit dan kredit</p>
            </div>
            <div class="w-full sm:w-72">
                <x-ts:input wire:model.live.debounce.300ms="search" icon="tabler.search" placeholder="Cari No Jurnal / Akun / Ket..." />
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">No. Jurnal</th>
                        <th class="px-4 py-3">Ref Trx</th>
                        <th class="px-4 py-3">Kode & Nama Akun</th>
                        <th class="px-4 py-3">Keterangan Transaksi</th>
                        <th class="px-4 py-3 text-right">Debit (Rp)</th>
                        <th class="px-4 py-3 text-right">Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($entries as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3 font-mono text-[11px] text-slate-600 font-semibold">{{ $item['tgl'] }}</td>
                            <td class="px-4 py-3 font-mono font-bold text-slate-900 text-[11px]">{{ $item['no_jurnal'] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-extrabold uppercase {{ $item['ref'] === 'PAYROLL' ? 'bg-blue-50 text-blue-700 border border-blue-100' : ($item['ref'] === 'JASMED' ? 'bg-purple-50 text-purple-700 border border-purple-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100') }}">
                                    {{ $item['ref'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $item['kode_akun'] }}</td>
                            <td class="px-4 py-3 text-slate-600 font-medium">{{ $item['keterangan'] }}</td>
                            <td class="px-4 py-3 text-right font-extrabold text-slate-900">
                                @if($item['debit'] > 0)
                                    <span>Rp {{ number_format($item['debit'], 0, ',', '.') }}</span>
                                @else
                                    <span class="text-slate-300 font-normal">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-extrabold text-slate-900">
                                @if($item['kredit'] > 0)
                                    <span>Rp {{ number_format($item['kredit'], 0, ',', '.') }}</span>
                                @else
                                    <span class="text-slate-300 font-normal">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada transaksi jurnal untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Jurnal Manual --}}
    <x-filament::modal id="modal-new-jurnal" width="lg">
        <x-slot:heading>Buat Jurnal Manual Baru</x-slot:heading>
        <div class="space-y-4 text-xs">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal</label>
                    <x-ts:input type="date" value="{{ date('Y-m-d') }}" />
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ref Transaksi</label>
                    <x-ts:input placeholder="Contoh: MANUAL-001" />
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Keterangan / Narasi</label>
                <x-ts:input placeholder="Contoh: Penyesuaian Saldo Kas Kecil" />
            </div>
            <div class="grid grid-cols-2 gap-3 border-t border-slate-100 pt-3">
                <div>
                    <label class="block font-bold text-indigo-700 mb-1">Akun Debit</label>
                    <x-ts:input placeholder="Nominal Debit (Rp)" />
                </div>
                <div>
                    <label class="block font-bold text-purple-700 mb-1">Akun Kredit</label>
                    <x-ts:input placeholder="Nominal Kredit (Rp)" />
                </div>
            </div>
            <div class="pt-2 flex justify-end">
                <x-ts:button sm class="font-bold py-2 px-4" x-on:click="$tsui.close.modal('modal-new-jurnal')">Simpan Jurnal</x-ts:button>
            </div>
        </div>
    </x-filament::modal>
</div>
