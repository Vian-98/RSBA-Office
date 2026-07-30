<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700 border border-indigo-100">
                <x-ts:icon name="tabler.book" class="h-3.5 w-3.5 text-indigo-600" />
                Master Akuntansi & Keuangan
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Chart of Accounts (COA)</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500 font-medium">
                Daftar Bagan Akun Standar (BAS) Rumah Sakit untuk pengklasifikasian transaksi keuangan.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <x-ts:button sm icon="tabler.plus" class="py-2.5 px-4 font-bold" x-on:click="$dispatch('open-modal', {id:'modal-new-coa'})">
                Tambah Akun Baru
            </x-ts:button>
        </div>
    </div>

    {{-- Category Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <button wire:click="$set('kategori', '')" class="rounded-2xl p-4 text-left border transition-all {{ empty($kategori) ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
            <span class="text-[10px] font-extrabold uppercase tracking-wider block opacity-80">Semua Kategori</span>
            <span class="text-xl font-extrabold block mt-1">18 Akun</span>
        </button>
        <button wire:click="$set('kategori', 'aset')" class="rounded-2xl p-4 text-left border transition-all {{ $kategori === 'aset' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
            <span class="text-[10px] font-extrabold uppercase tracking-wider block opacity-80">1. Aset</span>
            <span class="text-xl font-extrabold block mt-1">6 Akun</span>
        </button>
        <button wire:click="$set('kategori', 'kewajiban')" class="rounded-2xl p-4 text-left border transition-all {{ $kategori === 'kewajiban' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
            <span class="text-[10px] font-extrabold uppercase tracking-wider block opacity-80">2. Kewajiban</span>
            <span class="text-xl font-extrabold block mt-1">3 Akun</span>
        </button>
        <button wire:click="$set('kategori', 'pendapatan')" class="rounded-2xl p-4 text-left border transition-all {{ $kategori === 'pendapatan' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
            <span class="text-[10px] font-extrabold uppercase tracking-wider block opacity-80">4. Pendapatan</span>
            <span class="text-xl font-extrabold block mt-1">3 Akun</span>
        </button>
        <button wire:click="$set('kategori', 'beban')" class="rounded-2xl p-4 text-left border transition-all {{ $kategori === 'beban' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
            <span class="text-[10px] font-extrabold uppercase tracking-wider block opacity-80">5. Beban</span>
            <span class="text-xl font-extrabold block mt-1">4 Akun</span>
        </button>
    </div>

    {{-- Filter & Data Table Card --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100 space-y-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pb-2 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Daftar Akun Standar (COA)</h3>
                <p class="text-xs text-slate-500">Struktur kode dan nama akun akuntansi RS Bintang Amin</p>
            </div>
            <div class="w-full sm:w-72">
                <x-ts:input wire:model.live.debounce.300ms="search" icon="tabler.search" placeholder="Cari Kode atau Nama Akun..." />
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3">Kode Akun</th>
                        <th class="px-4 py-3">Nama Akun Akuntansi</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Saldo Normal</th>
                        <th class="px-4 py-3 text-right">Saldo Berjalan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($accounts as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3 font-mono font-bold text-indigo-700 text-[12px]">{{ $item['kode'] }}</td>
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $item['nama'] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ strtoupper($item['kategori']) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold uppercase text-slate-600">{{ $item['saldo_normal'] }}</td>
                            <td class="px-4 py-3 text-right font-extrabold text-slate-900">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">Aktif</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">Tidak ada akun akuntansi yang sesuai kriteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Akun --}}
    <x-filament::modal id="modal-new-coa" width="md">
        <x-slot:heading>Tambah Akun Baru (COA)</x-slot:heading>
        <div class="space-y-4 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Kode Akun</label>
                <x-ts:input wire:model="kode_akun" placeholder="Contoh: 1105" />
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Akun</label>
                <x-ts:input wire:model="nama_akun" placeholder="Contoh: Kas Kecil Operasional" />
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Kategori Akun</label>
                <select wire:model="kategori_akun" class="w-full rounded-xl border-slate-200 text-xs text-slate-700 font-semibold focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="aset">1 - Aset (Aktiva)</option>
                    <option value="kewajiban">2 - Kewajiban (Utang)</option>
                    <option value="ekuitas">3 - Ekuitas (Modal)</option>
                    <option value="pendapatan">4 - Pendapatan</option>
                    <option value="beban">5 - Beban Operasional</option>
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Saldo Normal</label>
                <select wire:model="saldo_normal" class="w-full rounded-xl border-slate-200 text-xs text-slate-700 font-semibold focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="debit">Debit</option>
                    <option value="kredit">Kredit</option>
                </select>
            </div>
            <div class="pt-2 flex justify-end">
                <x-ts:button sm class="font-bold py-2 px-4" x-on:click="$tsui.close.modal('modal-new-coa')">Simpan Akun</x-ts:button>
            </div>
        </div>
    </x-filament::modal>
</div>
