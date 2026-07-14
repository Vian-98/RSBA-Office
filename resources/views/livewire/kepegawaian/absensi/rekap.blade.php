<div>
    <x-ts:card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <x-ts:icon name="tabler.report-analytics" class="w-6 h-6 text-primary-500" />
                    <h3 class="text-lg font-semibold text-gray-800">Rekap Absensi</h3>
                </div>
            </div>
        </x-slot:header>

        <!-- Filters -->
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-5">
            <div>
                <x-ts:select.styled 
                    label="Mode Rekap" 
                    wire:model.live="mode" 
                    :options="[
                        ['label' => 'Bulanan', 'value' => 'bulanan'],
                        ['label' => 'Harian / Tanggal Spesifik', 'value' => 'harian'],
                    ]"
                    select="label:label|value:value"
                />
            </div>

            @if($mode === 'bulanan')
                <div>
                    <x-ts:select.styled 
                        label="Bulan" 
                        wire:model.live="bulan" 
                        :options="[
                            ['label' => 'Januari', 'value' => 1],
                            ['label' => 'Februari', 'value' => 2],
                            ['label' => 'Maret', 'value' => 3],
                            ['label' => 'April', 'value' => 4],
                            ['label' => 'Mei', 'value' => 5],
                            ['label' => 'Juni', 'value' => 6],
                            ['label' => 'Juli', 'value' => 7],
                            ['label' => 'Agustus', 'value' => 8],
                            ['label' => 'September', 'value' => 9],
                            ['label' => 'Oktober', 'value' => 10],
                            ['label' => 'November', 'value' => 11],
                            ['label' => 'Desember', 'value' => 12],
                        ]"
                        select="label:label|value:value"
                    />
                </div>
                <div>
                    <x-ts:select.styled 
                        label="Tahun" 
                        wire:model.live="tahun" 
                        :options="[
                            ['label' => '2025', 'value' => 2025],
                            ['label' => '2026', 'value' => 2026],
                            ['label' => '2027', 'value' => 2027],
                        ]"
                        select="label:label|value:value"
                    />
                </div>
            @else
                <div class="md:col-span-2">
                    <x-ts:input type="date" label="Tanggal Spesifik" wire:model.live="tanggal_spesifik" />
                </div>
            @endif

            <div>
                <x-ts:select.styled 
                    label="Ruangan (Bagian)" 
                    wire:model.live="ruangan_id" 
                    :options="$ruangans->map(fn($r) => ['label' => $r->nama, 'value' => $r->id])->toArray()"
                    select="label:label|value:value"
                    placeholder="Semua Ruangan"
                    searchable
                />
            </div>
            
            <div>
                <x-ts:select.styled 
                    label="Karyawan" 
                    wire:model.live="karyawan_id" 
                    :options="$karyawans->map(fn($k) => ['label' => $k->full_nama, 'value' => $k->id])->toArray()"
                    select="label:label|value:value"
                    placeholder="Semua Karyawan"
                    searchable
                />
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-7">
            <div class="p-4 text-center rounded-lg bg-green-50 border border-green-100">
                <div class="text-sm text-green-600 font-medium">Hadir</div>
                <div class="text-2xl font-bold text-green-700">{{ $summary['hadir'] }}</div>
            </div>
            <div class="p-4 text-center rounded-lg bg-yellow-50 border border-yellow-100">
                <div class="text-sm text-yellow-600 font-medium">Terlambat</div>
                <div class="text-2xl font-bold text-yellow-700">{{ $summary['terlambat'] }}</div>
            </div>
            <div class="p-4 text-center rounded-lg bg-orange-50 border border-orange-100">
                <div class="text-sm text-orange-600 font-medium">Pulang Cepat</div>
                <div class="text-2xl font-bold text-orange-700">{{ $summary['pulang_cepat'] }}</div>
            </div>
            <div class="p-4 text-center rounded-lg bg-red-50 border border-red-100">
                <div class="text-sm text-red-600 font-medium">Tidak Hadir</div>
                <div class="text-2xl font-bold text-red-700">{{ $summary['tidak_hadir'] }}</div>
            </div>
            <div class="p-4 text-center rounded-lg bg-blue-50 border border-blue-100">
                <div class="text-sm text-blue-600 font-medium">Cuti</div>
                <div class="text-2xl font-bold text-blue-700">{{ $summary['cuti'] }}</div>
            </div>
            <div class="p-4 text-center rounded-lg bg-cyan-50 border border-cyan-100">
                <div class="text-sm text-cyan-600 font-medium">Izin</div>
                <div class="text-2xl font-bold text-cyan-700">{{ $summary['izin'] }}</div>
            </div>
            <div class="p-4 text-center rounded-lg bg-gray-50 border border-gray-200">
                <div class="text-sm text-gray-600 font-medium">Perlu Verifikasi</div>
                <div class="text-2xl font-bold text-gray-700">{{ $summary['perlu_verifikasi'] }}</div>
            </div>
        </div>

        <!-- Data Table (Summary per Karyawan or Detailed List) -->
        <div class="mt-8">
            <h4 class="mb-4 text-md font-semibold text-gray-700">Rincian per Karyawan</h4>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-sm text-left text-gray-500 whitespace-nowrap">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama Karyawan</th>
                            <th scope="col" class="px-6 py-3 text-center text-green-600">Hadir</th>
                            <th scope="col" class="px-6 py-3 text-center text-yellow-600">Terlambat</th>
                            <th scope="col" class="px-6 py-3 text-center text-orange-600">Pulang Cepat</th>
                            <th scope="col" class="px-6 py-3 text-center text-red-600">Tidak Hadir</th>
                            <th scope="col" class="px-6 py-3 text-center text-blue-600">Cuti</th>
                            <th scope="col" class="px-6 py-3 text-center text-cyan-600">Izin</th>
                            <th scope="col" class="px-6 py-3 text-center text-gray-600">Perlu Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapKaryawan as $rk)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $rk['karyawan']->full_nama ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-center font-semibold text-green-600">{{ $rk['hadir'] }}</td>
                                <td class="px-6 py-4 text-center">{{ $rk['terlambat'] > 0 ? $rk['terlambat'] : '-' }}</td>
                                <td class="px-6 py-4 text-center">{{ $rk['pulang_cepat'] > 0 ? $rk['pulang_cepat'] : '-' }}</td>
                                <td class="px-6 py-4 text-center">{{ $rk['tidak_hadir'] > 0 ? $rk['tidak_hadir'] : '-' }}</td>
                                <td class="px-6 py-4 text-center">{{ $rk['cuti'] > 0 ? $rk['cuti'] : '-' }}</td>
                                <td class="px-6 py-4 text-center">{{ $rk['izin'] > 0 ? $rk['izin'] : '-' }}</td>
                                <td class="px-6 py-4 text-center">{{ $rk['perlu_verifikasi'] > 0 ? $rk['perlu_verifikasi'] : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-ts:icon name="tabler.folder-off" class="w-12 h-12 mb-2 text-gray-300" />
                                        <p>Tidak ada data absensi untuk filter yang dipilih.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Daily Attendance Log Details with Correction Buttons (Collapsible) -->
        <div x-data="{ showLogs: false }" class="mt-8 border-t border-gray-100 pt-6">
            <div class="flex items-center justify-between mb-4">
                <button type="button" @click="showLogs = !showLogs" class="flex items-center gap-2 text-left hover:opacity-80 transition-opacity focus:outline-none group">
                    <x-ts:icon name="tabler.list-details" class="w-5 h-5 text-indigo-500" />
                    <h4 class="text-md font-semibold text-gray-700 select-none">Rincian Log Harian & Koreksi Absensi</h4>
                    <!-- Dynamic Chevron -->
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-300 transform group-hover:text-indigo-500" :class="showLogs ? 'rotate-180 text-indigo-500' : ''" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <span class="text-xs text-gray-500 bg-slate-100 py-1 px-2.5 rounded-full font-medium">Total: {{ $records->total() }} hari log</span>
            </div>
            
            <div x-show="showLogs" x-transition x-cloak class="mt-4">
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm text-left text-gray-500 whitespace-nowrap">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">Tanggal</th>
                                <th scope="col" class="px-6 py-3">Nama Karyawan</th>
                                <th scope="col" class="px-6 py-3 text-center">Shift</th>
                                <th scope="col" class="px-6 py-3 text-center">Jam Kerja (Shift)</th>
                                <th scope="col" class="px-6 py-3 text-center">Jam Aktual (Mesin)</th>
                                <th scope="col" class="px-6 py-3 text-center">Status</th>
                                <th scope="col" class="px-6 py-3">Catatan / Alasan</th>
                                <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $r)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-semibold text-gray-900 text-xs">
                                        {{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900 text-xs">{{ $r->karyawan->nama ?? '-' }}</div>
                                        <div class="text-[10px] text-gray-400">PIN: {{ $r->karyawan->pin_absen ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($r->shift)
                                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded text-slate-800" style="background-color: {{ $r->shift->warna ?? '#e2e8f0' }}">
                                                {{ $r->shift->kode }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-gray-200 text-gray-700">
                                                LIBUR
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center text-xs">
                                        @if($r->shift)
                                            {{ substr($r->shift->jam_masuk, 0, 5) }} - {{ substr($r->shift->jam_keluar, 0, 5) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center text-xs">
                                        @if($r->absen_masuk_at || $r->absen_keluar_at)
                                            <span class="text-indigo-600 font-semibold">
                                                {{ $r->absen_masuk_at ? \Carbon\Carbon::parse($r->absen_masuk_at)->format('H:i') : '--:--' }}
                                            </span>
                                            -
                                            <span class="text-indigo-600 font-semibold">
                                                {{ $r->absen_keluar_at ? \Carbon\Carbon::parse($r->absen_keluar_at)->format('H:i') : '--:--' }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 italic">Tidak ada absen</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($r->status_kehadiran)
                                            <x-ts:badge :color="$r->status_kehadiran->color()" text="{{ $r->status_kehadiran->nama() }}" xs />
                                        @else
                                            <x-ts:badge color="gray" text="Belum Dicek" xs />
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600 max-w-xs truncate" title="{{ $r->catatan }}">
                                        {{ $r->catatan ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <x-ts:button wire:click="editRecord({{ $r->id }})" sm color="indigo" variant="outline" class="!py-1">Koreksi</x-ts:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                        Tidak ada log kehadiran harian untuk kriteria pencarian ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination for daily logs -->
                <div class="mt-4">
                    {{ $records->links(data: ['pageName' => 'dailyPage']) }}
                </div>
            </div>
        </div>

    </x-ts:card>

    <!-- Modal Koreksi Absensi Manual -->
    <x-ts:modal wire="showEditModal" title="Koreksi Absensi Manual (SDM)" size="md">
        @if($editingRecordId)
            <div class="flex flex-col gap-4 py-2">
                <x-ts:select.styled 
                    label="Status Kehadiran" 
                    wire:model="editStatus" 
                    :options="[
                        ['label' => 'Belum Dicek / LIBUR', 'value' => 'belum_dicek'],
                        ['label' => 'Hadir (Tepat Waktu)', 'value' => 'hadir'],
                        ['label' => 'Terlambat', 'value' => 'terlambat'],
                        ['label' => 'Pulang Cepat', 'value' => 'pulang_cepat'],
                        ['label' => 'Tidak Hadir (Alfa)', 'value' => 'tidak_hadir'],
                        ['label' => 'Cuti', 'value' => 'cuti'],
                        ['label' => 'Izin', 'value' => 'izin'],
                        ['label' => 'Perlu Verifikasi', 'value' => 'perlu_verifikasi']
                    ]"
                    select="label:label|value:value"
                />
                
                <x-ts:input type="datetime-local" label="Jam Masuk Aktual" wire:model="editAbsenMasuk" />
                
                <x-ts:input type="datetime-local" label="Jam Keluar Aktual" wire:model="editAbsenKeluar" />
                
                <x-ts:input label="Catatan / Alasan Koreksi" wire:model="editCatatan" placeholder="Contoh: Lupa scan mesin absensi, Hadir tugas luar" />
            </div>
            
            <x-slot:footer>
                <div class="flex justify-end gap-2">
                    <x-ts:button color="gray" variant="flat" wire:click="$set('showEditModal', false)">Batal</x-ts:button>
                    <x-ts:button color="primary" wire:click="saveCorrection">Simpan Koreksi</x-ts:button>
                </div>
            </x-slot:footer>
        @endif
    </x-ts:modal>
</div>
