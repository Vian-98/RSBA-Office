<div class="p-6 max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-emerald-500/10 text-emerald-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Tukar Jadwal Dokter</h1>
                    <p class="text-slate-500 text-sm">Alur Persetujuan Bertahap: Pengajuan Dokter A → Konfirmasi Dokter B → Approval Final Wadir</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('kepegawaian.jadwal-kerja.index') }}" class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                &larr; Kembali ke Jadwal Kerja
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('message') }}</span>
            </div>
            <button type="button" class="text-emerald-500 hover:text-emerald-700" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="text-rose-500 hover:text-rose-700" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-2xl p-2 shadow-sm border border-slate-200 flex flex-wrap gap-2">
        <button wire:click="$set('activeTab', 'pengajuan')" class="px-5 py-2.5 rounded-xl font-medium text-sm transition flex items-center gap-2 {{ $activeTab === 'pengajuan' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Pengajuan Tukar
        </button>

        <button wire:click="$set('activeTab', 'konfirmasi')" class="px-5 py-2.5 rounded-xl font-medium text-sm transition flex items-center gap-2 relative {{ $activeTab === 'konfirmasi' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            Konfirmasi Dokter B
            @if(count($listKonfirmasiSaya) > 0)
                <span class="ml-1.5 px-2 py-0.5 text-xs font-bold bg-amber-500 text-white rounded-full">{{ count($listKonfirmasiSaya) }}</span>
            @endif
        </button>

        @if($isWadir)
            <button wire:click="$set('activeTab', 'wadir')" class="px-5 py-2.5 rounded-xl font-medium text-sm transition flex items-center gap-2 relative {{ $activeTab === 'wadir' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Approval Wadir
                @if(count($listAntreanWadir) > 0)
                    <span class="ml-1.5 px-2 py-0.5 text-xs font-bold bg-blue-500 text-white rounded-full">{{ count($listAntreanWadir) }}</span>
                @endif
            </button>
        @endif

        <button wire:click="$set('activeTab', 'riwayat')" class="px-5 py-2.5 rounded-xl font-medium text-sm transition flex items-center gap-2 {{ $activeTab === 'riwayat' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Riwayat & Status
        </button>
    </div>

    <!-- Tab 1: Form Pengajuan (Dokter A) -->
    @if ($activeTab === 'pengajuan')
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-6">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <span class="w-3 h-3 bg-indigo-500 rounded-full"></span>
                Form Pengajuan Tukar Shift Dokter
            </h2>

            <form wire:submit.prevent="submitPengajuan" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Section Dokter A (Pengaju) -->
                    <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 space-y-4">
                        <div class="flex items-center gap-2 text-indigo-700 font-semibold border-b border-slate-200 pb-2">
                            <span class="px-2 py-1 bg-indigo-100 rounded text-xs">Pihak 1</span>
                            Dokter A (Pengaju)
                        </div>

                        <!-- Dropdown Custom Dokter A (Selalu Buka Ke Bawah) -->
                        <div x-data="{
                            open: false,
                            search: '',
                            canSelect: @json($canSelectDokterA),
                            selectedId: @entangle('dokterPengajuId'),
                            get dokters() { return $wire.doktersList || []; },
                            get filteredDokters() {
                                if (!this.search) return this.dokters;
                                const q = this.search.toLowerCase();
                                return this.dokters.filter(d => 
                                    d.nama.toLowerCase().includes(q) || 
                                    d.nip.toLowerCase().includes(q) || 
                                    d.ruangan.toLowerCase().includes(q)
                                );
                            },
                            get selectedDokter() {
                                return this.dokters.find(d => d.id == this.selectedId);
                            }
                        }" class="relative">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Pilih Dokter A (Pengaju)</label>
                            
                            <button type="button" @click="if (canSelect) open = !open" :disabled="!canSelect" class="w-full text-left bg-white text-sm rounded-xl border border-slate-300 px-3.5 py-2.5 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-xs transition disabled:bg-slate-100 disabled:cursor-not-allowed">
                                <div class="truncate flex items-center gap-2">
                                    <span x-text="selectedDokter ? selectedDokter.nama + ' (' + selectedDokter.ruangan + ')' : '-- Pilih Dokter A --'" :class="selectedDokter ? 'text-slate-800 font-semibold' : 'text-slate-400'"></span>
                                    <template x-if="!canSelect">
                                        <span class="px-2 py-0.5 text-[10px] font-bold bg-indigo-100 text-indigo-700 rounded-full">Akun Anda</span>
                                    </template>
                                </div>
                                <template x-if="canSelect">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </template>
                            </button>

                            <div x-show="open" @click.outside="open = false" x-transition.origin.top.duration.150ms class="absolute top-full left-0 right-0 mt-1 z-50 bg-white border border-slate-200 rounded-xl shadow-2xl overflow-hidden flex flex-col">
                                <div class="p-2 border-b border-slate-100 bg-slate-50">
                                    <input type="text" x-model="search" placeholder="🔍 Cari nama dokter / poli / NIP..." class="w-full text-xs rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-1.5" @click.stop>
                                </div>

                                <div class="overflow-y-auto max-h-60 divide-y divide-slate-50">
                                    <template x-for="d in filteredDokters" :key="d.id">
                                        <div @click="selectedId = d.id; open = false; $wire.set('dokterPengajuId', d.id)" class="px-3 py-2.5 text-xs hover:bg-indigo-50 cursor-pointer flex flex-col transition" :class="selectedId == d.id ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-700'">
                                            <div class="font-medium text-slate-800" x-text="d.nama"></div>
                                            <div class="text-[11px] text-slate-400 flex items-center justify-between mt-0.5">
                                                <span class="text-indigo-600 font-medium" x-text="d.ruangan"></span>
                                                <span x-text="'NIP: ' + d.nip"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="filteredDokters.length === 0" class="p-4 text-center text-xs text-slate-400">
                                        Dokter tidak ditemukan
                                    </div>
                                </div>
                            </div>
                            @error('dokterPengajuId') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Dropdown Custom Shift Dokter A (Selalu Buka Ke Bawah) -->
                        <div x-data="{
                            open: false,
                            search: '',
                            selectedId: @entangle('jadwalDetailPengajuId'),
                            get items() { return $wire.jadwalPengajuList || []; },
                            get filteredItems() {
                                if (!this.search) return this.items;
                                const q = this.search.toLowerCase();
                                return this.items.filter(i => i.label.toLowerCase().includes(q));
                            },
                            get selectedItem() {
                                return this.items.find(i => i.id == this.selectedId);
                            }
                        }" class="relative">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Pilih Jadwal/Shift Dokter A Yang Ingin Ditukar</label>

                            <button type="button" @click="if (items.length > 0) open = !open" :disabled="!@entangle('dokterPengajuId') || items.length === 0" class="w-full text-left bg-white text-sm rounded-xl border border-slate-300 px-3.5 py-2.5 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-xs hover:border-slate-400 transition disabled:bg-slate-100 disabled:cursor-not-allowed">
                                <span x-text="selectedItem ? selectedItem.label : (items.length === 0 ? (!@entangle('dokterPengajuId') ? '-- Pilih Dokter A Terlebih Dahulu --' : '-- Tidak Ada Jadwal Tersedia --') : '-- Pilih Shift/Tanggal --')" class="truncate" :class="selectedItem ? 'text-slate-800 font-semibold' : 'text-slate-400'"></span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="open" @click.outside="open = false" x-transition.origin.top.duration.150ms class="absolute top-full left-0 right-0 mt-1 z-50 bg-white border border-slate-200 rounded-xl shadow-2xl overflow-hidden flex flex-col">
                                <div class="p-2 border-b border-slate-100 bg-slate-50">
                                    <input type="text" x-model="search" placeholder="🔍 Cari tanggal / shift..." class="w-full text-xs rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-1.5" @click.stop>
                                </div>

                                <div class="overflow-y-auto max-h-60 divide-y divide-slate-50">
                                    <template x-for="item in filteredItems" :key="item.id">
                                        <div @click="selectedId = item.id; open = false; $wire.set('jadwalDetailPengajuId', item.id)" class="px-3 py-2.5 text-xs hover:bg-indigo-50 cursor-pointer flex items-center justify-between transition" :class="selectedId == item.id ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-700'">
                                            <div>
                                                <div class="font-medium text-slate-800" x-text="item.tanggal"></div>
                                                <div class="text-[11px] text-slate-400" x-text="item.jam"></div>
                                            </div>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-100 text-indigo-700" x-text="item.shift"></span>
                                        </div>
                                    </template>
                                    <div x-show="filteredItems.length === 0" class="p-4 text-center text-xs text-slate-400">
                                        Jadwal tidak ditemukan
                                    </div>
                                </div>
                            </div>
                            @error('jadwalDetailPengajuId') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Section Dokter B (Pengganti) -->
                    <div class="bg-amber-50/60 p-5 rounded-xl border border-amber-200 space-y-4">
                        <div class="flex items-center gap-2 text-amber-800 font-semibold border-b border-amber-200 pb-2">
                            <span class="px-2 py-1 bg-amber-200 text-amber-900 rounded text-xs">Pihak 2</span>
                            Dokter B (Pasangan Tukar)
                        </div>

                        <!-- Dropdown Custom Dokter B (Selalu Buka Ke Bawah) -->
                        <div x-data="{
                            open: false,
                            search: '',
                            selectedId: @entangle('dokterPenggantiId'),
                            pengajuId: @entangle('dokterPengajuId'),
                            get dokters() { return $wire.doktersList || []; },
                            get filteredDokters() {
                                let list = this.dokters.filter(d => d.id != this.pengajuId);
                                if (!this.search) return list;
                                const q = this.search.toLowerCase();
                                return list.filter(d => 
                                    d.nama.toLowerCase().includes(q) || 
                                    d.nip.toLowerCase().includes(q) || 
                                    d.ruangan.toLowerCase().includes(q)
                                );
                            },
                            get selectedDokter() {
                                return this.dokters.find(d => d.id == this.selectedId);
                            }
                        }" class="relative">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Pilih Dokter B</label>
                            
                            <button type="button" @click="open = !open" class="w-full text-left bg-white text-sm rounded-xl border border-slate-300 px-3.5 py-2.5 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-xs hover:border-slate-400 transition">
                                <span x-text="selectedDokter ? selectedDokter.nama + ' (' + selectedDokter.ruangan + ')' : '-- Pilih Dokter B --'" class="truncate" :class="selectedDokter ? 'text-slate-800 font-semibold' : 'text-slate-400'"></span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="open" @click.outside="open = false" x-transition.origin.top.duration.150ms class="absolute top-full left-0 right-0 mt-1 z-50 bg-white border border-slate-200 rounded-xl shadow-2xl overflow-hidden flex flex-col">
                                <div class="p-2 border-b border-slate-100 bg-amber-50/50">
                                    <input type="text" x-model="search" placeholder="🔍 Cari nama dokter / poli / NIP..." class="w-full text-xs rounded-lg border-slate-200 focus:border-amber-500 focus:ring-amber-500 px-3 py-1.5" @click.stop>
                                </div>

                                <div class="overflow-y-auto max-h-60 divide-y divide-slate-50">
                                    <template x-for="d in filteredDokters" :key="d.id">
                                        <div @click="selectedId = d.id; open = false; $wire.set('dokterPenggantiId', d.id)" class="px-3 py-2.5 text-xs hover:bg-amber-50 cursor-pointer flex flex-col transition" :class="selectedId == d.id ? 'bg-amber-50 text-amber-800 font-semibold' : 'text-slate-700'">
                                            <div class="font-medium text-slate-800" x-text="d.nama"></div>
                                            <div class="text-[11px] text-slate-400 flex items-center justify-between mt-0.5">
                                                <span class="text-amber-700 font-medium" x-text="d.ruangan"></span>
                                                <span x-text="'NIP: ' + d.nip"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="filteredDokters.length === 0" class="p-4 text-center text-xs text-slate-400">
                                        Dokter tidak ditemukan
                                    </div>
                                </div>
                            </div>
                            @error('dokterPenggantiId') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Dropdown Custom Shift Dokter B (Selalu Buka Ke Bawah) -->
                        <div x-data="{
                            open: false,
                            search: '',
                            selectedId: @entangle('jadwalDetailPenggantiId'),
                            get items() { return $wire.jadwalPenggantiList || []; },
                            get filteredItems() {
                                if (!this.search) return this.items;
                                const q = this.search.toLowerCase();
                                return this.items.filter(i => i.label.toLowerCase().includes(q));
                            },
                            get selectedItem() {
                                return this.items.find(i => i.id == this.selectedId);
                            }
                        }" class="relative">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Pilih Jadwal/Shift Dokter B Untuk Diambil</label>

                            <button type="button" @click="if (items.length > 0) open = !open" :disabled="!@entangle('dokterPenggantiId') || items.length === 0" class="w-full text-left bg-white text-sm rounded-xl border border-slate-300 px-3.5 py-2.5 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-xs hover:border-slate-400 transition disabled:bg-slate-100 disabled:cursor-not-allowed">
                                <span x-text="selectedItem ? selectedItem.label : (items.length === 0 ? (!@entangle('dokterPenggantiId') ? '-- Pilih Dokter B Terlebih Dahulu --' : '-- Tidak Ada Jadwal Tersedia --') : '-- Pilih Shift/Tanggal --')" class="truncate" :class="selectedItem ? 'text-slate-800 font-semibold' : 'text-slate-400'"></span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="open" @click.outside="open = false" x-transition.origin.top.duration.150ms class="absolute top-full left-0 right-0 mt-1 z-50 bg-white border border-slate-200 rounded-xl shadow-2xl overflow-hidden flex flex-col">
                                <div class="p-2 border-b border-slate-100 bg-amber-50/50">
                                    <input type="text" x-model="search" placeholder="🔍 Cari tanggal / shift..." class="w-full text-xs rounded-lg border-slate-200 focus:border-amber-500 focus:ring-amber-500 px-3 py-1.5" @click.stop>
                                </div>

                                <div class="overflow-y-auto max-h-60 divide-y divide-slate-50">
                                    <template x-for="item in filteredItems" :key="item.id">
                                        <div @click="selectedId = item.id; open = false; $wire.set('jadwalDetailPenggantiId', item.id)" class="px-3 py-2.5 text-xs hover:bg-amber-50 cursor-pointer flex items-center justify-between transition" :class="selectedId == item.id ? 'bg-amber-50 text-amber-800 font-semibold' : 'text-slate-700'">
                                            <div>
                                                <div class="font-medium text-slate-800" x-text="item.tanggal"></div>
                                                <div class="text-[11px] text-slate-400" x-text="item.jam"></div>
                                            </div>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800" x-text="item.shift"></span>
                                        </div>
                                    </template>
                                    <div x-show="filteredItems.length === 0" class="p-4 text-center text-xs text-slate-400">
                                        Jadwal tidak ditemukan
                                    </div>
                                </div>
                            </div>
                            @error('jadwalDetailPenggantiId') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Alasan Penukaran Shift</label>
                    <textarea wire:model="alasan" rows="3" class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tuliskan alasan keperluan tukar shift (misal: Keperluan dinas luar, seminar medis, dll)..."></textarea>
                    @error('alasan') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Kirim Pengajuan Ke Dokter B
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Tab 2: Konfirmasi Dokter B -->
    @if ($activeTab === 'konfirmasi')
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <span class="w-3 h-3 bg-amber-500 rounded-full"></span>
                Daftar Permintaan Konfirmasi Tukar Shift Dari Dokter Lain
            </h2>

            @if(count($listKonfirmasiSaya) === 0)
                <div class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-300 text-slate-500 text-sm">
                    Belum ada permintaan tukar shift dari dokter lain yang menunggu konfirmasi Anda.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($listKonfirmasiSaya as $item)
                        <div class="p-5 bg-amber-50/40 rounded-xl border border-amber-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800 rounded-full">Menunggu Konfirmasi Anda</span>
                                    <span class="text-xs text-slate-400">Diajukan: {{ $item->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-sm font-bold text-slate-800">
                                    <span class="text-indigo-600">{{ $item->dokterPengaju?->full_nama }}</span> ingin menukar shift dengan Anda:
                                </div>
                                <div class="text-xs text-slate-600 grid grid-cols-1 md:grid-cols-2 gap-2 bg-white p-3 rounded-lg border border-amber-200">
                                    <div>
                                        <span class="font-medium text-slate-500">Jadwal {{ $item->dokterPengaju?->nama }}:</span><br>
                                        <strong class="text-slate-800">{{ \Carbon\Carbon::parse($item->jadwalDetailPengaju?->tanggal)->translatedFormat('l, d M Y') }}</strong>
                                        ({{ $item->jadwalDetailPengaju?->shift?->nama }})
                                    </div>
                                    <div>
                                        <span class="font-medium text-slate-500">Jadwal Anda ({{ $item->dokterPengganti?->nama }}):</span><br>
                                        <strong class="text-slate-800">{{ \Carbon\Carbon::parse($item->jadwalDetailPengganti?->tanggal)->translatedFormat('l, d M Y') }}</strong>
                                        ({{ $item->jadwalDetailPengganti?->shift?->nama }})
                                    </div>
                                </div>
                                @if($item->alasan)
                                    <p class="text-xs text-slate-500 italic">"{{ $item->alasan }}"</p>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <button wire:click="openConfirmModal({{ $item->id }}, 'setuju_dokter')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs rounded-xl shadow transition">
                                    ✓ Setuju & Teruskan Ke Wadir
                                </button>
                                <button wire:click="openConfirmModal({{ $item->id }}, 'tolak_dokter')" class="px-4 py-2 bg-rose-100 hover:bg-rose-200 text-rose-700 font-medium text-xs rounded-xl transition">
                                    ✕ Tolak
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- Tab 3: Approval Wadir -->
    @if ($activeTab === 'wadir' && $isWadir)
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                Antrean Approval Wadir (Tukar Shift Dokter)
            </h2>

            @if(count($listAntreanWadir) === 0)
                <div class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-300 text-slate-500 text-sm">
                    Tidak ada pengajuan tukar shift dokter yang sedang menunggu persetujuan Wadir.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($listAntreanWadir as $item)
                        <div class="p-5 bg-blue-50/40 rounded-xl border border-blue-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">Menunggu Persetujuan Wadir</span>
                                    <span class="text-xs text-slate-400">Konfirmasi Dokter B: {{ $item->konfirmasi_dokter_at?->diffForHumans() }}</span>
                                </div>
                                <div class="text-sm font-bold text-slate-800">
                                    Penukaran Shift Antara Dokter A (<span class="text-indigo-600">{{ $item->dokterPengaju?->full_nama }}</span>) & Dokter B (<span class="text-amber-600">{{ $item->dokterPengganti?->full_nama }}</span>)
                                </div>
                                <div class="text-xs text-slate-600 grid grid-cols-1 md:grid-cols-2 gap-2 bg-white p-3 rounded-lg border border-blue-200">
                                    <div>
                                        <span class="font-medium text-slate-500">Semula {{ $item->dokterPengaju?->nama }}:</span><br>
                                        <strong class="text-slate-800">{{ \Carbon\Carbon::parse($item->jadwalDetailPengaju?->tanggal)->translatedFormat('l, d M Y') }}</strong> ({{ $item->jadwalDetailPengaju?->shift?->nama }})
                                    </div>
                                    <div>
                                        <span class="font-medium text-slate-500">Semula {{ $item->dokterPengganti?->nama }}:</span><br>
                                        <strong class="text-slate-800">{{ \Carbon\Carbon::parse($item->jadwalDetailPengganti?->tanggal)->translatedFormat('l, d M Y') }}</strong> ({{ $item->jadwalDetailPengganti?->shift?->nama }})
                                    </div>
                                </div>
                                @if($item->alasan)
                                    <p class="text-xs text-slate-500 italic">Alasan: "{{ $item->alasan }}"</p>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <button wire:click="openConfirmModal({{ $item->id }}, 'setuju_wadir')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-xl shadow transition">
                                    ✓ Setujui Final & Tukar
                                </button>
                                <button wire:click="openConfirmModal({{ $item->id }}, 'tolak_wadir')" class="px-4 py-2 bg-rose-100 hover:bg-rose-200 text-rose-700 font-medium text-xs rounded-xl transition">
                                    ✕ Tolak
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- Tab 4: Riwayat Pengajuan -->
    @if ($activeTab === 'riwayat')
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <h2 class="text-lg font-bold text-slate-800">Riwayat Pengajuan Tukar Shift Dokter</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 border-b border-slate-200 text-xs font-semibold uppercase">
                            <th class="p-3">Tanggal Pengajuan</th>
                            <th class="p-3">Dokter A (Pengaju)</th>
                            <th class="p-3">Dokter B (Pasangan)</th>
                            <th class="p-3">Detail Penukaran Shift</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Wadir / Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($listRiwayat as $row)
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3 text-xs text-slate-500">
                                    {{ $row->created_at->translatedFormat('d M Y H:i') }}
                                </td>
                                <td class="p-3 font-medium text-slate-800">
                                    {{ $row->dokterPengaju?->full_nama }}
                                </td>
                                <td class="p-3 font-medium text-slate-800">
                                    {{ $row->dokterPengganti?->full_nama }}
                                </td>
                                <td class="p-3 text-xs">
                                    <div><span class="text-slate-400">A:</span> {{ \Carbon\Carbon::parse($row->jadwalDetailPengaju?->tanggal)->translatedFormat('d/m/Y') }} ({{ $row->jadwalDetailPengaju?->shift?->nama }})</div>
                                    <div><span class="text-slate-400">B:</span> {{ \Carbon\Carbon::parse($row->jadwalDetailPengganti?->tanggal)->translatedFormat('d/m/Y') }} ({{ $row->jadwalDetailPengganti?->shift?->nama }})</div>
                                </td>
                                <td class="p-3">
                                    @php $color = $row->status->color(); @endphp
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800 border border-{{ $color }}-200">
                                        {{ $row->status->label() }}
                                    </span>
                                </td>
                                <td class="p-3 text-xs text-slate-600">
                                    @if($row->disetujuiOleh)
                                        <div>Approved by: <strong>{{ $row->disetujuiOleh->karyawan?->full_nama ?? $row->disetujuiOleh->email }}</strong></div>
                                    @endif
                                    @if($row->catatan_wadir)
                                        <div class="italic text-slate-400">"{{ $row->catatan_wadir }}"</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">Belum ada data riwayat tukar shift dokter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $listRiwayat->links() }}
            </div>
        </div>
    @endif

    <!-- Confirmation Modal -->
    @if ($showConfirmModal)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4 border border-slate-200">
                <h3 class="text-lg font-bold text-slate-800">
                    @if(in_array($confirmAction, ['setuju_dokter', 'setuju_wadir']))
                        Konfirmasi Setujui Tukar Shift
                    @else
                        Konfirmasi Penolakan Tukar Shift
                    @endif
                </h3>

                <p class="text-sm text-slate-600">
                    @if($confirmAction === 'setuju_dokter')
                        Apakah Anda yakin ingin menyetujui penukaran shift ini? Pengajuan akan langsung diteruskan ke Wakil Direktur.
                    @elseif($confirmAction === 'tolak_dokter')
                        Apakah Anda yakin ingin menolak penukaran shift ini?
                    @elseif($confirmAction === 'setuju_wadir')
                        Apakah Anda yakin ingin menyetujui penukaran shift ini secara final? Shift kedua dokter akan otomatis tertukar di jadwal kerja.
                    @elseif($confirmAction === 'tolak_wadir')
                        Apakah Anda yakin ingin menolak pengajuan penukaran shift ini?
                    @endif
                </p>

                @if(in_array($confirmAction, ['setuju_wadir', 'tolak_wadir']))
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Catatan Wadir (Opsional)</label>
                        <textarea wire:model="catatanWadir" rows="2" class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tuliskan catatan persetujuan/penolakan..."></textarea>
                    </div>
                @endif

                <div class="flex justify-end gap-2 pt-2">
                    <button wire:click="closeConfirmModal" type="button" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Batal
                    </button>
                    <button wire:click="processConfirm" type="button" class="px-4 py-2 text-sm font-medium text-white rounded-xl shadow transition {{ in_array($confirmAction, ['setuju_dokter', 'setuju_wadir']) ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700' }}">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
