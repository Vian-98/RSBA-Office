<div class="flex flex-col gap-4">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between rounded-lg bg-white p-4 shadow-sm gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('kepegawaian.surat.cuti', ['tab' => 'cuti-bersama']) }}" class="text-gray-400 hover:text-gray-600">
                    <x-ts:icon name="tabler.arrow-left" class="w-5 h-5" />
                </a>
                <h2 class="text-xl font-bold text-gray-800">{{ $cutiBersama->nama }}</h2>
                
                @if($cutiBersama->status === 'draft')
                    <x-ts:badge color="gray" light>Draft</x-ts:badge>
                @elseif($cutiBersama->status === 'disimulasikan')
                    <x-ts:badge color="amber" light>Disimulasikan</x-ts:badge>
                @elseif($cutiBersama->status === 'diterapkan')
                    <x-ts:badge color="emerald">Diterapkan</x-ts:badge>
                @elseif($cutiBersama->status === 'dibatalkan')
                    <x-ts:badge color="red" light>Dibatalkan</x-ts:badge>
                @endif
            </div>
            
            <p class="text-sm text-gray-500 mt-1">
                Sifat: 
                <strong>{{ $cutiBersama->potong_cuti_tahunan ? 'Memotong Kuota (' . ($cutiBersama->jenisCuti?->nama ?? 'Cuti Tahunan') . ')' : 'Libur Bebas (Tidak Memotong Kuota)' }}</strong>
                | Tanggal: 
                <span class="font-mono">
                    {{ implode(', ', $cutiBersama->tanggal->pluck('tanggal')->map(fn($d) => $d->format('d/m/Y'))->toArray()) }}
                </span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <x-ts:button outline icon="tabler.refresh" wire:click="loadSimulasi">
                Refresh Simulasi
            </x-ts:button>

            @if($cutiBersama->status !== 'diterapkan')
                <x-ts:button color="emerald" icon="tabler.check" wire:click="terapkan" wire:confirm="Apakah Anda yakin ingin MENERAPKAN Cuti Bersama ini? Kuota cuti pegawai reguler akan terpotong secara resmi.">
                    Terapkan Cuti Bersama
                </x-ts:button>
            @else
                <x-ts:button color="red" icon="tabler.x" wire:click="batalkan" wire:confirm="Apakah Anda yakin ingin MEMBATALKAN Cuti Bersama ini? Record surat_cuti akan dihapus dan sisa cuti pegawai akan dikembalikan.">
                    Batalkan Cuti Bersama
                </x-ts:button>
            @endif
        </div>
    </div>

    {{-- Stat Cards --}}
    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-9 gap-3">
        <div class="rounded-lg bg-white p-3 shadow-sm border-l-4 border-indigo-500">
            <div class="text-xs text-gray-500 uppercase font-semibold">Total Pegawai</div>
            <div class="text-xl font-bold text-gray-800 mt-1">{{ $simulasiData['total_pegawai'] ?? 0 }}</div>
        </div>
        <div class="rounded-lg bg-white p-3 shadow-sm border-l-4 border-red-500">
            <div class="text-xs text-gray-500 uppercase font-semibold">Kena Potong Cuti</div>
            <div class="text-xl font-bold text-red-600 mt-1">{{ $simulasiData['total_pegawai_terdampak'] ?? 0 }} pegawai</div>
        </div>
        <div class="rounded-lg bg-white p-3 shadow-sm border-l-4 border-rose-500">
            <div class="text-xs text-gray-500 uppercase font-semibold">Total Hari Terpotong</div>
            <div class="text-xl font-bold text-rose-600 mt-1">{{ $simulasiData['total_hari_dipotong'] ?? 0 }} hari</div>
        </div>
        <div class="rounded-lg bg-white p-3 shadow-sm border-l-4 border-amber-500">
            <div class="text-xs text-gray-500 uppercase font-semibold">Shift Piket (Hadir)</div>
            <div class="text-xl font-bold text-amber-600 mt-1">{{ $simulasiData['total_pegawai_piket'] ?? 0 }}</div>
        </div>
        <div class="rounded-lg bg-white p-3 shadow-sm border-l-4 border-blue-500">
            <div class="text-xs text-gray-500 uppercase font-semibold">Libur Roster</div>
            <div class="text-xl font-bold text-blue-600 mt-1">{{ $simulasiData['total_pegawai_libur_roster'] ?? 0 }}</div>
        </div>
        <div class="rounded-lg bg-white p-3 shadow-sm border-l-4 border-purple-500">
            <div class="text-xs text-purple-600 uppercase font-semibold">Dikecualikan</div>
            <div class="text-xl font-bold text-purple-600 mt-1">{{ $simulasiData['total_pegawai_dikecualikan'] ?? 0 }} pegawai</div>
        </div>
        <div class="rounded-lg bg-white p-3 shadow-sm border-l-4 border-gray-400">
            <div class="text-xs text-gray-500 uppercase font-semibold">Jadwal Belum Ada</div>
            <div class="text-xl font-bold text-gray-700 mt-1">{{ $simulasiData['total_jadwal_belum_ada'] ?? 0 }}</div>
        </div>
        {{-- DEFISIT CARDS --}}
        <div class="rounded-lg bg-white p-3 shadow-sm border-l-4 border-orange-500"
             title="Pegawai yang kuota cuti tahunannya tidak mencukupi setelah event ini diterapkan">
            <div class="text-xs text-orange-600 uppercase font-semibold">⚠ Pegawai Defisit</div>
            <div class="text-xl font-bold text-orange-600 mt-1">{{ $simulasiData['total_pegawai_defisit'] ?? 0 }} pegawai</div>
            <div class="text-xs text-gray-400 mt-0.5">Kuota tidak cukup</div>
        </div>
        <div class="rounded-lg bg-white p-3 shadow-sm border-l-4 border-orange-400"
             title="Total akumulasi hari yang melebihi saldo cuti tahunan seluruh pegawai defisit">
            <div class="text-xs text-orange-600 uppercase font-semibold">⚠ Total Hari Defisit</div>
            <div class="text-xl font-bold text-orange-600 mt-1">{{ $simulasiData['total_hari_defisit'] ?? 0 }} hari</div>
            <div class="text-xs text-gray-400 mt-0.5">Akumulasi selisih</div>
        </div>
    </div>

    {{-- Detail Simulation Table --}}
    <div x-data="{ open: true }" class="rounded-lg bg-white p-4 shadow-sm flex flex-col gap-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <button type="button" @click="open = !open" class="text-gray-400 hover:text-indigo-600 rounded p-1 transition-colors focus:outline-none" :title="open ? 'Minimize' : 'Expand'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200" :class="open ? '' : '-rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <h3 class="text-md font-semibold text-gray-800 cursor-pointer select-none" @click="open = !open">Preview Hasil Simulasi per Pegawai per Tanggal</h3>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3" x-show="open">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="w-64">
                        <x-ts:input wire:model.live.debounce.300ms="searchPegawai" placeholder="Cari nama pegawai..." icon="tabler.search" />
                    </div>

                    <div class="w-48">
                        <x-ts:select.styled wire:model.live="filterKategori" :options="[
                            ['value' => 'semua', 'label' => 'Semua Status'],
                            ['value' => 'potong', 'label' => 'Dipotong Cuti'],
                            ['value' => 'piket', 'label' => 'Dinas / Piket'],
                            ['value' => 'roster', 'label' => 'Libur Roster'],
                            ['value' => 'belum_ada', 'label' => 'Jadwal Belum Ada'],
                            ['value' => 'dikecualikan', 'label' => 'Dikecualikan'],
                        ]" select="label:label|value:value" />
                    </div>

                    <div class="w-24">
                        <x-ts:select.styled wire:model.live="perPage" :options="[
                            ['value' => 10, 'label' => '10'],
                            ['value' => 15, 'label' => '15'],
                            ['value' => 25, 'label' => '25'],
                            ['value' => 50, 'label' => '50'],
                            ['value' => 100, 'label' => '100'],
                        ]" select="label:label|value:value" />
                    </div>
                </div>

                {{-- Unified Bulk Action Dropdown --}}
                <div x-data="{ bulkOpen: false }" class="relative inline-block text-left">
                    <button @click="bulkOpen = !bulkOpen" type="button" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-xs transition">
                        <x-ts:icon name="tabler.adjustments" class="w-4 h-4 text-slate-500" />
                        <span>Aksi Massal ({{ $groupedDetails->total() }})</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="bulkOpen" @click.outside="bulkOpen = false" x-transition.origin.top.right.duration.150ms class="absolute right-0 mt-1 w-56 z-50 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden py-1 text-xs text-left">
                        <div class="px-3 py-1.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100 bg-slate-50">
                            Terapkan ke {{ $groupedDetails->total() }} Pegawai
                        </div>
                        <button type="button" wire:click="setBulkOverrideStatus('auto'); bulkOpen = false" class="w-full px-3 py-2 text-left hover:bg-slate-50 text-slate-700 flex items-center gap-2">
                            <span>Reset Otomatis (Sistem)</span>
                        </button>
                        <button type="button" wire:click="setBulkOverrideStatus('potong_cuti'); bulkOpen = false" class="w-full px-3 py-2 text-left hover:bg-rose-50 text-rose-700 flex items-center gap-2 font-medium">
                            <span>Semua Libur (Potong Cuti)</span>
                        </button>
                        <button type="button" wire:click="setBulkOverrideStatus('tetap_hadir'); bulkOpen = false" class="w-full px-3 py-2 text-left hover:bg-emerald-50 text-emerald-700 flex items-center gap-2 font-medium">
                            <span>Semua Masuk Piket</span>
                        </button>
                        <button type="button" wire:click="setBulkOverrideStatus('dikecualikan'); bulkOpen = false" class="w-full px-3 py-2 text-left hover:bg-purple-50 text-purple-700 flex items-center gap-2 font-medium border-t border-slate-100">
                            <span>Kecualikan Semua</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="open" x-collapse class="overflow-x-auto border rounded-lg"
            wire:loading.remove
            wire:target="loadSimulasi,terapkan,batalkan,searchPegawai,filterKategori,filterPartisipasi,setOverrideStatus,setBulkOverrideStatus,togglePartisipasi,selectAllIkut,selectAllTidakIkut">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-center">Opsi Partisipasi</th>
                        <th class="px-4 py-3">Nama Pegawai</th>
                        <th class="px-4 py-3">Kategori Kerja</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Shift Terjadwal</th>
                        <th class="px-4 py-3">Hasil Keputusan</th>
                        <th class="px-4 py-3">Keterangan Aturan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($groupedDetails as $group)
                        @php
                            $rowSpanCount = count($group['dates']);
                        @endphp
                        @foreach($group['dates'] as $index => $row)
                            <tr class="hover:bg-gray-50 {{ !($row['is_ikut'] ?? true) ? 'bg-purple-50/30' : '' }} {{ $index === $rowSpanCount - 1 ? 'border-b-2 border-gray-300' : '' }}">
                                @if($index === 0)
                                    <td rowspan="{{ $rowSpanCount }}" class="px-3 py-3 text-center align-middle border-r border-gray-200 bg-white">
                                        <div x-data="{ open: false }" class="relative inline-block text-left">
                                            @php
                                                $currentOverride = $row['override_status'] ?? 'auto';
                                            @endphp

                                            @if($currentOverride === 'potong_cuti')
                                                <button @click="open = !open" type="button" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition shadow-xs">
                                                    <span>Libur (Potong Cuti)</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                            @elseif($currentOverride === 'tetap_hadir')
                                                <button @click="open = !open" type="button" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition shadow-xs">
                                                    <span>Masuk Piket</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                            @elseif($currentOverride === 'dikecualikan')
                                                <button @click="open = !open" type="button" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 transition shadow-xs">
                                                    <span>Dikecualikan</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                            @else
                                                <button @click="open = !open" type="button" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 border border-slate-300 hover:bg-slate-200 transition shadow-xs">
                                                    <span>Otomatis</span>
                                                    <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                            @endif

                                            <div x-show="open" @click.outside="open = false" x-transition.origin.top.duration.150ms class="absolute left-0 mt-1 w-44 z-50 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden py-1 text-xs text-left">
                                                <button type="button" wire:click="setOverrideStatus({{ $row['karyawan_id'] }}, 'auto'); open = false" class="w-full px-3 py-2 text-left hover:bg-slate-50 flex items-center justify-between {{ $currentOverride === 'auto' ? 'font-bold text-slate-900 bg-slate-50' : 'text-slate-600' }}">
                                                    <span>Otomatis (Sistem)</span>
                                                    @if($currentOverride === 'auto') <span class="text-indigo-600">✓</span> @endif
                                                </button>
                                                <button type="button" wire:click="setOverrideStatus({{ $row['karyawan_id'] }}, 'potong_cuti'); open = false" class="w-full px-3 py-2 text-left hover:bg-rose-50 flex items-center justify-between {{ $currentOverride === 'potong_cuti' ? 'font-bold text-rose-700 bg-rose-50' : 'text-rose-600' }}">
                                                    <span>Libur (Potong Cuti)</span>
                                                    @if($currentOverride === 'potong_cuti') <span class="text-rose-600">✓</span> @endif
                                                </button>
                                                <button type="button" wire:click="setOverrideStatus({{ $row['karyawan_id'] }}, 'tetap_hadir'); open = false" class="w-full px-3 py-2 text-left hover:bg-emerald-50 flex items-center justify-between {{ $currentOverride === 'tetap_hadir' ? 'font-bold text-emerald-700 bg-emerald-50' : 'text-emerald-600' }}">
                                                    <span>Masuk Piket (Tidak Potong)</span>
                                                    @if($currentOverride === 'tetap_hadir') <span class="text-emerald-600">✓</span> @endif
                                                </button>
                                                <button type="button" wire:click="setOverrideStatus({{ $row['karyawan_id'] }}, 'dikecualikan'); open = false" class="w-full px-3 py-2 text-left hover:bg-purple-50 flex items-center justify-between {{ $currentOverride === 'dikecualikan' ? 'font-bold text-purple-700 bg-purple-50' : 'text-purple-600' }}">
                                                    <span>Dikecualikan</span>
                                                    @if($currentOverride === 'dikecualikan') <span class="text-purple-600">✓</span> @endif
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td rowspan="{{ $rowSpanCount }}" class="px-4 py-3 font-semibold text-gray-900 align-middle border-r border-gray-200 bg-white">
                                        {{ $row['karyawan_nama'] }}
                                    </td>
                                    <td rowspan="{{ $rowSpanCount }}" class="px-4 py-3 text-xs align-middle border-r border-gray-200 bg-white">
                                        {{ $row['kategori_kerja'] }}
                                    </td>
                                @endif

                                <td class="px-4 py-3 text-xs font-mono font-medium text-slate-800">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-xs">
                                    <span class="px-2 py-0.5 rounded bg-gray-100 font-mono">{{ $row['shift_nama'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs font-semibold">
                                    @if($row['status_aksi'] === 'DIKECUALIKAN')
                                        <x-ts:badge color="purple" light>Dikecualikan</x-ts:badge>
                                    @elseif($row['status_aksi'] === 'DIPOTONG_CUTI')
                                        <x-ts:badge color="red">Dipotong Cuti (1 Hari)</x-ts:badge>
                                    @elseif($row['status_aksi'] === 'TERJADWAL_PIKET')
                                        <x-ts:badge color="amber">Terjadwal Dinas (Piket)</x-ts:badge>
                                    @elseif($row['status_aksi'] === 'HADIR_PIKET' || $row['status_aksi'] === 'TETAP_HADIR')
                                        <x-ts:badge color="emerald">Hadir Dinas Shift</x-ts:badge>
                                    @elseif($row['status_aksi'] === 'LIBUR_ROSTER')
                                        <x-ts:badge color="blue">Libur Roster</x-ts:badge>
                                    @elseif($row['status_aksi'] === 'LIBUR_WEEKEND')
                                        <x-ts:badge color="gray">Libur Weekend</x-ts:badge>
                                    @elseif($row['status_aksi'] === 'CUTI_BERSAMA_BEBAS')
                                        <x-ts:badge color="emerald" light>Libur Bebas</x-ts:badge>
                                    @elseif($row['status_aksi'] === 'JADWAL_BELUM_ADA')
                                        <x-ts:badge color="rose" light>Jadwal Belum Tersedia</x-ts:badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $row['keterangan'] }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data hasil simulasi yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($groupedDetails->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500">
                    Menampilkan <span class="font-bold text-slate-700">{{ $groupedDetails->firstItem() }}</span> sampai <span class="font-bold text-slate-700">{{ $groupedDetails->lastItem() }}</span> dari <span class="font-bold text-slate-700">{{ $groupedDetails->total() }}</span> pegawai
                </div>
                <div>
                    {{ $groupedDetails->links('partials.pagination', ['paginatorLabel' => 'Pegawai']) }}
                </div>
            </div>
        @endif

        <div x-show="open"
             x-collapse
             class="border rounded-lg p-4 bg-gray-50"
             wire:loading
             wire:target="loadSimulasi,terapkan,batalkan,searchPegawai,filterKategori,perPage">
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-3">
                <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                Memuat hasil simulasi...
            </div>

            <div class="space-y-2 animate-pulse">
                <div class="h-10 bg-gray-200 rounded"></div>
                <div class="h-10 bg-gray-200 rounded"></div>
                <div class="h-10 bg-gray-200 rounded"></div>
                <div class="h-10 bg-gray-200 rounded"></div>
                <div class="h-10 bg-gray-200 rounded"></div>
            </div>
        </div>
    </div>

    {{-- Quota Summary Table --}}
    @if($cutiBersama->potong_cuti_tahunan)
        <div x-data="{ open: true }" class="rounded-lg bg-white p-4 shadow-sm flex flex-col gap-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <button type="button" @click="open = !open" class="text-gray-400 hover:text-indigo-600 rounded p-1 transition-colors focus:outline-none" :title="open ? 'Minimize' : 'Expand'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200" :class="open ? '' : '-rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <h3 class="text-md font-semibold text-gray-800 cursor-pointer select-none" @click="open = !open">Proyeksi Sisa Kuota Cuti Tahunan Pegawai</h3>
                </div>

                <div class="flex flex-wrap items-center gap-2" x-show="open">
                    <div class="w-64">
                        <x-ts:input wire:model.live.debounce.300ms="searchProyeksi" placeholder="Cari nama pegawai..." icon="tabler.search" />
                    </div>

                    <div class="w-48">
                        <x-ts:select.styled wire:model.live="filterProyeksi" :options="[
                            ['value' => 'semua', 'label' => 'Semua Dampak'],
                            ['value' => 'defisit', 'label' => 'Defisit Kuota'],
                            ['value' => 'aman', 'label' => 'Sisa Kuota Aman'],
                        ]" select="label:label|value:value" />
                    </div>

                    <div class="w-24">
                        <x-ts:select.styled wire:model.live="perPageProyeksi" :options="[
                            ['value' => 10, 'label' => '10'],
                            ['value' => 15, 'label' => '15'],
                            ['value' => 25, 'label' => '25'],
                            ['value' => 50, 'label' => '50'],
                            ['value' => 100, 'label' => '100'],
                        ]" select="label:label|value:value" />
                    </div>
                </div>
            </div>

            <div x-show="open" x-collapse class="overflow-x-auto border rounded-lg"
                wire:loading.remove
                wire:target="loadSimulasi,terapkan,batalkan,searchPegawai,filterKategori,filterPartisipasi,togglePartisipasi,selectAllIkut,selectAllTidakIkut,searchProyeksi,filterProyeksi,perPageProyeksi">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                        <tr>
                            <th class="px-4 py-3">Nama Pegawai</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3 text-center">Sisa Cuti Saat Ini</th>
                            <th class="px-4 py-3 text-center">Hari Terpotong</th>
                            <th class="px-4 py-3 text-center">Proyeksi Sisa Cuti</th>
                            <th class="px-4 py-3 text-center text-orange-600">Defisit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $totalDefisit = $simulasiData['total_pegawai_defisit'] ?? 0;
                            $totalHariDef = $simulasiData['total_hari_defisit'] ?? 0;
                        @endphp

                        @if($totalDefisit > 0)
                            <tr class="bg-orange-50">
                                <td colspan="6" class="px-4 py-2 text-xs text-orange-700 font-semibold">
                                    ⚠ Proyeksi Bisnis: <strong>{{ $totalDefisit }} pegawai</strong> akan defisit kuota dengan total akumulasi <strong>{{ $totalHariDef }} hari</strong> melebihi saldo — perlu dipertimbangkan dalam perencanaan SDM.
                                </td>
                            </tr>
                        @endif

                        @forelse($proyeksiSummary as $ksum)
                            <tr class="hover:bg-gray-50 {{ $ksum['is_minus'] ? 'bg-orange-50/40' : '' }}">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $ksum['nama'] }}</td>
                                <td class="px-4 py-3 text-xs">{{ $ksum['kategori'] }}</td>
                                <td class="px-4 py-3 text-center font-mono font-semibold">{{ $ksum['sisa_cuti_saat_ini'] }} hari</td>
                                <td class="px-4 py-3 text-center font-mono font-semibold text-red-600">-{{ $ksum['hari_terpotong'] }} hari</td>
                                <td class="px-4 py-3 text-center font-mono font-bold">
                                    @if($ksum['is_minus'])
                                        <span class="text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-200">
                                            {{ $ksum['sisa_cuti_setelah_event'] }} hari (Defisit)
                                        </span>
                                    @else
                                        <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                                            {{ $ksum['sisa_cuti_setelah_event'] }} hari
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($ksum['is_minus'])
                                        <span class="text-xs font-bold text-orange-600 bg-orange-50 border border-orange-200 px-2 py-0.5 rounded">
                                            -{{ $ksum['hari_defisit'] }} hari
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    Tidak ada data pegawai yang cocok dengan filter proyeksi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($proyeksiSummary->hasPages())
                <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-xs text-slate-500">
                        Menampilkan <span class="font-bold text-slate-700">{{ $proyeksiSummary->firstItem() }}</span> sampai <span class="font-bold text-slate-700">{{ $proyeksiSummary->lastItem() }}</span> dari <span class="font-bold text-slate-700">{{ $proyeksiSummary->total() }}</span> pegawai
                    </div>
                    <div>
                        {{ $proyeksiSummary->links('partials.pagination', ['paginatorLabel' => 'Pegawai']) }}
                    </div>
                </div>
            @endif

            <div x-show="open"
                 x-collapse
                 class="border rounded-lg p-4 bg-orange-50/30"
                 wire:loading
                 wire:target="loadSimulasi,terapkan,batalkan,searchPegawai,filterKategori,searchProyeksi,filterProyeksi,perPageProyeksi">
                <div class="flex items-center gap-2 text-sm text-orange-700 mb-3">
                    <svg class="animate-spin h-4 w-4 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    Memuat proyeksi kuota cuti...
                </div>

                <div class="space-y-2 animate-pulse">
                    <div class="h-9 bg-orange-100 rounded"></div>
                    <div class="h-9 bg-orange-100 rounded"></div>
                    <div class="h-9 bg-orange-100 rounded"></div>
                    <div class="h-9 bg-orange-100 rounded"></div>
                </div>
            </div>
        </div>
    @endif
</div>
