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

    </x-ts:card>
</div>
