<div class="flex flex-col gap-4">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between rounded-lg bg-white p-4 shadow-sm gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('kepegawaian.cuti-bersama.index') }}" class="text-gray-400 hover:text-gray-600">
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
                Sifat Event: 
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
                    Batalkan Event
                </x-ts:button>
            @endif
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
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
        <div class="rounded-lg bg-white p-3 shadow-sm border-l-4 border-gray-400">
            <div class="text-xs text-gray-500 uppercase font-semibold">Jadwal Belum Ada</div>
            <div class="text-xl font-bold text-gray-700 mt-1">{{ $simulasiData['total_jadwal_belum_ada'] ?? 0 }}</div>
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
            
            <div class="flex items-center gap-2" x-show="open">
                <div class="w-64">
                    <x-ts:input wire:model.live.debounce.300ms="searchPegawai" placeholder="Cari nama pegawai..." icon="tabler.search" />
                </div>
                
                <x-ts:select.styled wire:model.live="filterKategori" :options="[
                    ['value' => 'semua', 'label' => 'Semua Hasil'],
                    ['value' => 'potong', 'label' => 'Dipotong Cuti'],
                    ['value' => 'piket', 'label' => 'Tetap Piket'],
                    ['value' => 'roster', 'label' => 'Libur Roster'],
                    ['value' => 'belum_ada', 'label' => 'Jadwal Belum Ada'],
                ]" select="label:label|value:value" />
            </div>
        </div>

        <div x-show="open" x-collapse class="overflow-x-auto border rounded-lg">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                    <tr>
                        <th class="px-4 py-3">Nama Pegawai</th>
                        <th class="px-4 py-3">Kategori Kerja</th>
                        <th class="px-4 py-3">Tanggal Event</th>
                        <th class="px-4 py-3">Shift Terjadwal</th>
                        <th class="px-4 py-3">Hasil Keputusan</th>
                        <th class="px-4 py-3">Keterangan Aturan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($details as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $row['karyawan_nama'] }}</td>
                            <td class="px-4 py-3 text-xs">{{ $row['kategori_kerja'] }}</td>
                            <td class="px-4 py-3 text-xs font-mono">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-xs">
                                <span class="px-2 py-0.5 rounded bg-gray-100 font-mono">{{ $row['shift_nama'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold">
                                @if($row['status_aksi'] === 'DIPOTONG_CUTI')
                                    <x-ts:badge color="red">Dipotong Cuti (1 Hari)</x-ts:badge>
                                @elseif($row['status_aksi'] === 'TETAP_HADIR')
                                    <x-ts:badge color="amber">Tetap Hadir / Piket</x-ts:badge>
                                @elseif($row['status_aksi'] === 'LIBUR_ROSTER')
                                    <x-ts:badge color="blue">Libur Roster</x-ts:badge>
                                @elseif($row['status_aksi'] === 'LIBUR_WEEKEND')
                                    <x-ts:badge color="gray">Libur Weekend</x-ts:badge>
                                @elseif($row['status_aksi'] === 'CUTI_BERSAMA_BEBAS')
                                    <x-ts:badge color="emerald">Libur Bebas</x-ts:badge>
                                @elseif($row['status_aksi'] === 'JADWAL_BELUM_ADA')
                                    <x-ts:badge color="rose" light>Jadwal Belum Tersedia</x-ts:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $row['keterangan'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data hasil simulasi yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quota Summary Table --}}
    @if($cutiBersama->potong_cuti_tahunan)
        <div x-data="{ open: true }" class="rounded-lg bg-white p-4 shadow-sm flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button type="button" @click="open = !open" class="text-gray-400 hover:text-indigo-600 rounded p-1 transition-colors focus:outline-none" :title="open ? 'Minimize' : 'Expand'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200" :class="open ? '' : '-rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <h3 class="text-md font-semibold text-gray-800 cursor-pointer select-none" @click="open = !open">Proyeksi Sisa Kuota Cuti Tahunan Pegawai</h3>
                </div>
            </div>

            <div x-show="open" x-collapse class="overflow-x-auto border rounded-lg">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                        <tr>
                            <th class="px-4 py-3">Nama Pegawai</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3 text-center">Sisa Cuti Saat Ini</th>
                            <th class="px-4 py-3 text-center">Hari Terpotong Event Ini</th>
                            <th class="px-4 py-3 text-center">Proyeksi Sisa Cuti</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $terdampakSummary = collect($simulasiData['karyawan_summary'] ?? [])->filter(fn($k) => $k['hari_terpotong'] > 0);
                        @endphp

                        @forelse($terdampakSummary as $ksum)
                            <tr class="hover:bg-gray-50">
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    Tidak ada pegawai yang terpotong kuota cuti tahunan pada event ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
