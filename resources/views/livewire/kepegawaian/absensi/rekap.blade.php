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
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-6">
            <div>
                <x-ts:select.styled 
                    wire:key="filter-mode-select"
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
                        wire:key="filter-bulan-select"
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
                        wire:key="filter-tahun-select"
                        label="Tahun" 
                        wire:model.live="tahun" 
                        :options="collect(range(date('Y') + 1, 2008))->map(fn($y) => ['label' => (string)$y, 'value' => $y])->toArray()"
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
                    wire:key="filter-ruangan-select"
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
                    wire:key="filter-karyawan-select"
                    label="Karyawan" 
                    wire:model.live="karyawan_id" 
                    :options="$karyawans->map(fn($k) => ['label' => $k->full_nama, 'value' => $k->id])->toArray()"
                    select="label:label|value:value"
                    placeholder="Semua Karyawan"
                    searchable
                />
            </div>

            <div>
                <x-ts:select.styled 
                    wire:key="filter-status-select"
                    label="Status" 
                    wire:model.live="statusFilter" 
                    :options="[
                        ['label' => 'Semua Status', 'value' => ''],
                        ['label' => 'Belum Dicek / LIBUR', 'value' => 'belum_dicek'],
                        ['label' => 'Hadir', 'value' => 'hadir'],
                        ['label' => 'Terlambat', 'value' => 'terlambat'],
                        ['label' => 'Pulang Cepat', 'value' => 'pulang_cepat'],
                        ['label' => 'Tidak Hadir', 'value' => 'tidak_hadir'],
                        ['label' => 'Cuti', 'value' => 'cuti'],
                        ['label' => 'Izin', 'value' => 'izin'],
                        ['label' => 'Perlu Verifikasi', 'value' => 'perlu_verifikasi']
                    ]"
                    select="label:label|value:value"
                    placeholder="Semua Status"
                />
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
            <div wire:click="$set('statusFilter', '{{ $statusFilter === 'hadir' ? '' : 'hadir' }}')" class="p-4 text-center rounded-lg bg-green-50 border cursor-pointer hover:shadow-sm transition-all {{ $statusFilter === 'hadir' ? 'border-green-500 ring-2 ring-green-200' : 'border-green-100' }}">
                <div class="text-sm text-green-600 font-medium select-none">Hadir</div>
                <div class="text-2xl font-bold text-green-700 select-none">{{ $summary['hadir'] }}</div>
            </div>
            <div wire:click="$set('statusFilter', '{{ $statusFilter === 'terlambat' ? '' : 'terlambat' }}')" class="p-4 text-center rounded-lg bg-yellow-50 border cursor-pointer hover:shadow-sm transition-all flex flex-col justify-between {{ $statusFilter === 'terlambat' ? 'border-yellow-500 ring-2 ring-yellow-200' : 'border-yellow-100' }}">
                <div>
                    <div class="text-sm text-yellow-600 font-medium select-none">Terlambat</div>
                    <div class="text-2xl font-bold text-yellow-700 select-none">{{ $summary['terlambat'] }}</div>
                </div>
                @if($summary['menit_terlambat'] > 0)
                    <div class="text-xs text-yellow-600 font-semibold mt-1 select-none">({{ $summary['menit_terlambat'] }} mnt)</div>
                @endif
            </div>
            <div wire:click="$set('statusFilter', '{{ $statusFilter === 'pulang_cepat' ? '' : 'pulang_cepat' }}')" class="p-4 text-center rounded-lg bg-orange-50 border cursor-pointer hover:shadow-sm transition-all flex flex-col justify-between {{ $statusFilter === 'pulang_cepat' ? 'border-orange-500 ring-2 ring-orange-200' : 'border-orange-100' }}">
                <div>
                    <div class="text-sm text-orange-600 font-medium select-none">Pulang Cepat</div>
                    <div class="text-2xl font-bold text-orange-700 select-none">{{ $summary['pulang_cepat'] }}</div>
                </div>
                @if($summary['menit_pulang_cepat'] > 0)
                    <div class="text-xs text-orange-600 font-semibold mt-1 select-none">({{ $summary['menit_pulang_cepat'] }} mnt)</div>
                @endif
            </div>
            <div wire:click="$set('statusFilter', '{{ $statusFilter === 'tidak_hadir' ? '' : 'tidak_hadir' }}')" class="p-4 text-center rounded-lg bg-red-50 border cursor-pointer hover:shadow-sm transition-all {{ $statusFilter === 'tidak_hadir' ? 'border-red-500 ring-2 ring-red-200' : 'border-red-100' }}">
                <div class="text-sm text-red-600 font-medium select-none">Tidak Hadir</div>
                <div class="text-2xl font-bold text-red-700 select-none">{{ $summary['tidak_hadir'] }}</div>
            </div>
            <div wire:click="$set('statusFilter', '{{ $statusFilter === 'cuti' ? '' : 'cuti' }}')" class="p-4 text-center rounded-lg bg-blue-50 border cursor-pointer hover:shadow-sm transition-all {{ $statusFilter === 'cuti' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-blue-100' }}">
                <div class="text-sm text-blue-600 font-medium select-none">Cuti</div>
                <div class="text-2xl font-bold text-blue-700 select-none">{{ $summary['cuti'] }}</div>
            </div>
            <div wire:click="$set('statusFilter', '{{ $statusFilter === 'izin' ? '' : 'izin' }}')" class="p-4 text-center rounded-lg bg-cyan-50 border cursor-pointer hover:shadow-sm transition-all {{ $statusFilter === 'izin' ? 'border-cyan-500 ring-2 ring-cyan-200' : 'border-cyan-100' }}">
                <div class="text-sm text-cyan-600 font-medium select-none">Izin</div>
                <div class="text-2xl font-bold text-cyan-700 select-none">{{ $summary['izin'] }}</div>
            </div>
            <div wire:click="$set('statusFilter', '{{ $statusFilter === 'perlu_verifikasi' ? '' : 'perlu_verifikasi' }}')" class="p-4 text-center rounded-lg bg-gray-50 border cursor-pointer hover:shadow-sm transition-all {{ $statusFilter === 'perlu_verifikasi' ? 'border-gray-500 ring-2 ring-gray-300' : 'border-gray-200' }}">
                <div class="text-sm text-gray-600 font-medium select-none">Perlu Verifikasi</div>
                <div class="text-2xl font-bold text-gray-700 select-none">{{ $summary['perlu_verifikasi'] }}</div>
            </div>
        </div>

        <!-- Data Table (Summary per Karyawan or Detailed List) -->
        <div x-data="{ showSummary: true }" class="mt-8">
            <div class="flex items-center gap-2 mb-4">
                <button type="button" @click="showSummary = !showSummary" class="flex items-center gap-2 text-left hover:opacity-80 transition-opacity focus:outline-none group">
                     <h4 class="text-md font-semibold text-gray-700 select-none">Rincian per Karyawan</h4>
                     <!-- Dynamic Chevron -->
                     <svg class="w-4 h-4 text-gray-400 transition-transform duration-300 transform group-hover:text-indigo-500" :class="showSummary ? 'rotate-180 text-indigo-500' : ''" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                     </svg>
                </button>
            </div>
            
            <div x-show="showSummary" x-transition x-cloak class="overflow-x-auto rounded-lg border border-gray-200 pb-36">
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
                            <th scope="col" class="px-6 py-3 text-center text-indigo-600 bg-indigo-50/50 pr-12">Kelebihan Jam (Overtime)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapKaryawan as $rk)
                            <tr wire:key="rekap-karyawan-row-{{ $rk['karyawan']->id }}" class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $rk['karyawan']->full_nama ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-center font-semibold text-green-600">{{ $rk['hadir'] }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($rk['terlambat'] > 0)
                                        <span class="font-semibold">{{ $rk['terlambat'] }}</span>
                                        @if($rk['menit_terlambat'] > 0)
                                            <div class="text-xs text-gray-500 font-normal">({{ $rk['menit_terlambat'] }} mnt)</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($rk['pulang_cepat'] > 0)
                                        <span class="font-semibold">{{ $rk['pulang_cepat'] }}</span>
                                        @if($rk['menit_pulang_cepat'] > 0)
                                            <div class="text-xs text-gray-500 font-normal">({{ $rk['menit_pulang_cepat'] }} mnt)</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">{{ $rk['tidak_hadir'] > 0 ? $rk['tidak_hadir'] : '-' }}</td>
                                <td class="px-6 py-4 text-center">{{ $rk['cuti'] > 0 ? $rk['cuti'] : '-' }}</td>
                                <td class="px-6 py-4 text-center">{{ $rk['izin'] > 0 ? $rk['izin'] : '-' }}</td>
                                <td class="px-6 py-4 text-center">{{ $rk['perlu_verifikasi'] > 0 ? $rk['perlu_verifikasi'] : '-' }}</td>
                                <td class="px-6 py-4 text-center pr-12">
                                    @if(($rk['total_overtime_menit'] ?? 0) > 0)
                                        @php
                                            $h = floor($rk['total_overtime_menit'] / 60);
                                            $m = $rk['total_overtime_menit'] % 60;
                                            $formatted = $h > 0 ? "{$h}j {$m}m" : "{$m}m";
                                        @endphp
                                        <div class="inline-flex items-center gap-1.5 justify-center">
                                            <span class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded text-xs select-none">{{ $formatted }}</span>
                                            <x-ts:dropdown wire:key="rekap-karyawan-ot-dropdown-{{ $rk['karyawan']->id }}" position="bottom-end" width="md">
                                                <x-slot:action>
                                                    <button type="button" x-on:click="show = !show" class="text-gray-400 hover:text-indigo-600 transition-colors focus:outline-none">
                                                        <x-ts:icon name="tabler.info-circle" class="w-4 h-4" />
                                                    </button>
                                                </x-slot:action>
                                                <div class="p-3 text-xs max-h-60 overflow-y-auto select-none text-left w-full">
                                                    <div class="font-bold text-gray-700 border-b border-gray-100 pb-1.5 mb-2 flex items-center justify-between">
                                                        <span>Rincian Kelebihan Jam</span>
                                                        <span class="text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded">{{ $formatted }}</span>
                                                    </div>
                                                    <ul class="space-y-2">
                                                        @foreach($rk['overtime_details'] as $det)
                                                            <li class="border-b border-slate-100 pb-1.5 last:border-b-0 last:pb-0">
                                                                <div class="font-semibold text-gray-800 flex items-center gap-1.5">
                                                                    <span>{{ $det['tanggal'] }}</span>
                                                                    <span class="text-indigo-600 text-[11px] font-bold">
                                                                        @php
                                                                            $dh = floor($det['menit'] / 60);
                                                                            $dm = $det['menit'] % 60;
                                                                            echo $dh > 0 ? "({$dh}j {$dm}m)" : "({$dm}m)";
                                                                        @endphp
                                                                    </span>
                                                                </div>
                                                                @if($det['keterangan'] && $det['keterangan'] !== 'Pulang terlambat')
                                                                    <div class="text-[10px] text-amber-600 mt-0.5 leading-tight font-medium">{{ $det['keterangan'] }}</div>
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </x-ts:dropdown>
                                        </div>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
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
            @if($paginatedKaryawans && $paginatedKaryawans->hasPages())
                <div class="flex flex-col items-center justify-center gap-2 mt-6 pt-4 border-t border-slate-100 bg-white w-full">
                    <nav class="inline-flex items-center gap-1.5" aria-label="Pagination">
                        {{-- Previous --}}
                        @if($paginatedKaryawans->onFirstPage())
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-300 border border-slate-200 select-none cursor-not-allowed">
                                <x-ts:icon name="tabler.chevron-left" class="w-4 h-4" />
                            </span>
                        @else
                            <button type="button" wire:click="previousPage('rekapKaryawanPage')" class="flex items-center justify-center w-8 h-8 rounded-lg bg-white text-slate-500 border border-slate-200 hover:bg-slate-50 transition focus:outline-none">
                                <x-ts:icon name="tabler.chevron-left" class="w-4 h-4" />
                            </button>
                        @endif

                        @php
                            $currentPage = $paginatedKaryawans->currentPage();
                            $lastPage = $paginatedKaryawans->lastPage();
                            $start = max(2, $currentPage - 1);
                            $end = min($lastPage - 1, $currentPage + 1);
                        @endphp

                        {{-- Page 1 --}}
                        @if($currentPage == 1)
                            <span aria-current="page" class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600 text-white font-bold text-xs border border-indigo-600 select-none">{{ 1 }}</span>
                        @else
                            <button type="button" wire:click="gotoPage(1, 'rekapKaryawanPage')" class="flex items-center justify-center w-8 h-8 rounded-lg bg-white text-slate-600 font-semibold text-xs border border-slate-200 hover:bg-slate-50 transition focus:outline-none">{{ 1 }}</button>
                        @endif

                        {{-- Ellipsis --}}
                        @if($start > 2)
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-400 font-semibold text-xs border border-slate-200 select-none">...</span>
                        @endif

                        {{-- Sliding Range --}}
                        @for($page = $start; $page <= $end; $page++)
                            @if($page == $currentPage)
                                <span aria-current="page" class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600 text-white font-bold text-xs border border-indigo-600 select-none">{{ $page }}</span>
                            @else
                                <button type="button" wire:click="gotoPage({{ $page }}, 'rekapKaryawanPage')" class="flex items-center justify-center w-8 h-8 rounded-lg bg-white text-slate-600 font-semibold text-xs border border-slate-200 hover:bg-slate-50 transition focus:outline-none">{{ $page }}</button>
                            @endif
                        @endfor

                        {{-- Ellipsis --}}
                        @if($end < $lastPage - 1)
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-400 font-semibold text-xs border border-slate-200 select-none">...</span>
                        @endif

                        {{-- Last Page --}}
                        @if($lastPage > 1)
                            @if($currentPage == $lastPage)
                                <span aria-current="page" class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600 text-white font-bold text-xs border border-indigo-600 select-none">{{ $lastPage }}</span>
                            @else
                                <button type="button" wire:click="gotoPage({{ $lastPage }}, 'rekapKaryawanPage')" class="flex items-center justify-center w-8 h-8 rounded-lg bg-white text-slate-600 font-semibold text-xs border border-slate-200 hover:bg-slate-50 transition focus:outline-none">{{ $lastPage }}</button>
                            @endif
                        @endif

                        {{-- Next --}}
                        @if($paginatedKaryawans->hasMorePages())
                            <button type="button" wire:click="nextPage('rekapKaryawanPage')" class="flex items-center justify-center w-8 h-8 rounded-lg bg-white text-slate-500 border border-slate-200 hover:bg-slate-50 transition focus:outline-none">
                                <x-ts:icon name="tabler.chevron-right" class="w-4 h-4" />
                            </button>
                        @else
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-300 border border-slate-200 select-none cursor-not-allowed">
                                <x-ts:icon name="tabler.chevron-right" class="w-4 h-4" />
                            </span>
                        @endif
                    </nav>
                    <p class="text-[11px] text-slate-400 font-medium select-none">
                        Menampilkan <span class="font-bold text-slate-600">{{ $paginatedKaryawans->firstItem() ?? 0 }}</span> sampai <span class="font-bold text-slate-600">{{ $paginatedKaryawans->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-600">{{ $paginatedKaryawans->total() }}</span> Karyawan
                    </p>
                </div>
            @endif
        </div>

        <!-- Daily Attendance Log Details with Correction Buttons (Collapsible) -->
        <div x-data="{ showLogs: false }" class="mt-8 border-t border-gray-100 pt-6">
            <div class="flex items-center justify-between mb-4">
                <button type="button" @click="showLogs = !showLogs" class="flex items-center gap-2 text-left hover:opacity-80 transition-opacity focus:outline-none group">
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
                                <th scope="col" class="px-6 py-3 text-center text-indigo-600 bg-indigo-50/20">Overtime</th>
                                <th scope="col" class="px-6 py-3 text-center">
                                    <div class="flex items-center justify-center">
                                        <x-ts:dropdown position="bottom-end">
                                            <x-slot:action>
                                                <button type="button" x-on:click="show = !show" class="flex items-center justify-center gap-1 text-xs font-semibold uppercase text-gray-700 hover:text-indigo-600 transition-colors focus:outline-none select-none">
                                                    <span>STATUS</span>
                                                    @if($statusFilter)
                                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                                    @endif
                                                    <x-ts:icon name="tabler.filter" class="w-3.5 h-3.5 text-gray-400 hover:text-indigo-600" />
                                                </button>
                                            </x-slot:action>
                                            <x-ts:dropdown.items text="Semua Status" wire:click="$set('statusFilter', '')" class="{{ $statusFilter === '' ? 'font-bold text-indigo-600 bg-indigo-50' : '' }}" />
                                            <x-ts:dropdown.items text="Belum Dicek / LIBUR" wire:click="$set('statusFilter', 'belum_dicek')" class="{{ $statusFilter === 'belum_dicek' ? 'font-bold text-indigo-600 bg-indigo-50' : '' }}" />
                                            <x-ts:dropdown.items text="Hadir" wire:click="$set('statusFilter', 'hadir')" class="{{ $statusFilter === 'hadir' ? 'font-bold text-indigo-600 bg-indigo-50' : '' }}" />
                                            <x-ts:dropdown.items text="Terlambat" wire:click="$set('statusFilter', 'terlambat')" class="{{ $statusFilter === 'terlambat' ? 'font-bold text-indigo-600 bg-indigo-50' : '' }}" />
                                            <x-ts:dropdown.items text="Pulang Cepat" wire:click="$set('statusFilter', 'pulang_cepat')" class="{{ $statusFilter === 'pulang_cepat' ? 'font-bold text-indigo-600 bg-indigo-50' : '' }}" />
                                            <x-ts:dropdown.items text="Tidak Hadir" wire:click="$set('statusFilter', 'tidak_hadir')" class="{{ $statusFilter === 'tidak_hadir' ? 'font-bold text-indigo-600 bg-indigo-50' : '' }}" />
                                            <x-ts:dropdown.items text="Cuti" wire:click="$set('statusFilter', 'cuti')" class="{{ $statusFilter === 'cuti' ? 'font-bold text-indigo-600 bg-indigo-50' : '' }}" />
                                            <x-ts:dropdown.items text="Izin" wire:click="$set('statusFilter', 'izin')" class="{{ $statusFilter === 'izin' ? 'font-bold text-indigo-600 bg-indigo-50' : '' }}" />
                                            <x-ts:dropdown.items text="Perlu Verifikasi" wire:click="$set('statusFilter', 'perlu_verifikasi')" class="{{ $statusFilter === 'perlu_verifikasi' ? 'font-bold text-indigo-600 bg-indigo-50' : '' }}" />
                                        </x-ts:dropdown>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">Catatan / Alasan</th>
                                <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                             @forelse($records as $r)
                                 <tr wire:key="daily-record-row-{{ $r->id }}" class="bg-white border-b hover:bg-gray-50">
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
                                     <td class="px-6 py-4 text-center text-xs">
                                         @if($r->absen_masuk_at && $r->absen_keluar_at)
                                             @php
                                                 $dailyOvertime = 0;
                                                 $isAbsentType = in_array($r->status_kehadiran, [
                                                     \App\Enums\StatusKehadiran::CUTI,
                                                     \App\Enums\StatusKehadiran::IZIN,
                                                     \App\Enums\StatusKehadiran::TIDAK_HADIR
                                                 ]);
                                                 if (!$isAbsentType) {
                                                     $masuk = \Carbon\Carbon::parse($r->absen_masuk_at);
                                                     $keluar = \Carbon\Carbon::parse($r->absen_keluar_at);
                                                     if ($r->shift) {
                                                         $jamKeluar = \Carbon\Carbon::parse($r->shift->jam_keluar);
                                                         $targetCheckout = \Carbon\Carbon::parse(\Carbon\Carbon::parse($r->tanggal)->format('Y-m-d') . ' ' . $jamKeluar->format('H:i:s'));
                                                         if ($r->shift->lintas_hari || $jamKeluar->lt(\Carbon\Carbon::parse($r->shift->jam_masuk))) {
                                                             $targetCheckout->addDay();
                                                         }
                                                         if ($keluar->gt($targetCheckout)) {
                                                             $dailyOvertime = abs($keluar->diffInMinutes($targetCheckout));
                                                         }
                                                     } else {
                                                         $dailyOvertime = abs($keluar->diffInMinutes($masuk));
                                                     }
                                                 }
                                             @endphp
                                             @if($dailyOvertime > 0)
                                                 @php
                                                     $doh = floor($dailyOvertime / 60);
                                                     $dom = $dailyOvertime % 60;
                                                     $dailyOvertimeFormatted = $doh > 0 ? "{$doh}j {$dom}m" : "{$dom}m";
                                                 @endphp
                                                 <div class="inline-flex items-center gap-1.5 justify-center">
                                                     <span class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded text-xs select-none">{{ $dailyOvertimeFormatted }}</span>
                                                     <x-ts:dropdown wire:key="daily-record-ot-dropdown-{{ $r->id }}" position="bottom-end" width="md">
                                                         <x-slot:action>
                                                             <button type="button" x-on:click="show = !show" class="text-gray-400 hover:text-indigo-600 transition-colors focus:outline-none">
                                                                 <x-ts:icon name="tabler.info-circle" class="w-4 h-4" />
                                                             </button>
                                                         </x-slot:action>
                                                         <div class="p-3 text-xs select-none text-left w-full">
                                                             <div class="font-bold text-gray-700 border-b border-gray-100 pb-1 mb-2">Rincian Kelebihan Jam</div>
                                                             <div class="space-y-1 text-gray-600">
                                                                 <div class="flex justify-between">
                                                                     <span>Durasi Lembur:</span>
                                                                     <span class="font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded text-[10px]">{{ $dailyOvertime }} menit</span>
                                                                 </div>
                                                                 @if($r->shift)
                                                                     <div class="flex justify-between border-t border-slate-50 pt-1 mt-1">
                                                                         <span>Shift:</span>
                                                                         <span class="font-semibold text-gray-800">{{ $r->shift->kode }} ({{ substr($r->shift->jam_masuk, 0, 5) }}-{{ substr($r->shift->jam_keluar, 0, 5) }})</span>
                                                                     </div>
                                                                     <div class="flex justify-between">
                                                                         <span>Target Keluar:</span>
                                                                         <span class="font-semibold text-gray-800">
                                                                             @php
                                                                                 $jamKeluar = \Carbon\Carbon::parse($r->shift->jam_keluar);
                                                                                 $targetCheckout = \Carbon\Carbon::parse(\Carbon\Carbon::parse($r->tanggal)->format('Y-m-d') . ' ' . $jamKeluar->format('H:i:s'));
                                                                                 if ($r->shift->lintas_hari || $jamKeluar->lt(\Carbon\Carbon::parse($r->shift->jam_masuk))) {
                                                                                     $targetCheckout->addDay();
                                                                                 }
                                                                                 echo $targetCheckout->format('d M H:i');
                                                                             @endphp
                                                                         </span>
                                                                     </div>
                                                                     <div class="flex justify-between">
                                                                         <span>Aktual Keluar:</span>
                                                                         <span class="font-semibold text-indigo-600">{{ $r->absen_keluar_at ? \Carbon\Carbon::parse($r->absen_keluar_at)->format('d M H:i') : '-' }}</span>
                                                                     </div>
                                                                 @else
                                                                     <div class="text-amber-600 font-semibold border-t border-slate-50 pt-1 mt-1 text-center">Tugas di hari Libur/OFF</div>
                                                                 @endif
                                                             </div>
                                                         </div>
                                                     </x-ts:dropdown>
                                                 </div>
                                             @else
                                                 <span class="text-gray-300">-</span>
                                             @endif
                                         @else
                                             <span class="text-gray-300">-</span>
                                         @endif
                                     </td>
                                     <td class="px-6 py-4 text-center">
                                         @if($r->status_kehadiran)
                                            <button type="button" wire:click="$set('statusFilter', '{{ $r->status_kehadiran->value }}')" class="hover:opacity-80 transition-opacity focus:outline-none select-none">
                                                <x-ts:badge :color="$r->status_kehadiran->color()" text="{{ $r->status_kehadiran->nama() }}" xs />
                                            </button>
                                        @else
                                            <button type="button" wire:click="$set('statusFilter', 'belum_dicek')" class="hover:opacity-80 transition-opacity focus:outline-none select-none">
                                                <x-ts:badge color="gray" text="Belum Dicek" xs />
                                            </button>
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
                
            @if($records && $records->hasPages())
                <div class="flex flex-col items-center justify-center gap-2 mt-6 pt-4 border-t border-slate-100 bg-white w-full">
                    <nav class="inline-flex items-center gap-1.5" aria-label="Pagination">
                        {{-- Previous --}}
                        @if($records->onFirstPage())
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-300 border border-slate-200 select-none cursor-not-allowed">
                                <x-ts:icon name="tabler.chevron-left" class="w-4 h-4" />
                            </span>
                        @else
                            <button type="button" wire:click="previousPage('dailyPage')" class="flex items-center justify-center w-8 h-8 rounded-lg bg-white text-slate-500 border border-slate-200 hover:bg-slate-50 transition focus:outline-none">
                                <x-ts:icon name="tabler.chevron-left" class="w-4 h-4" />
                            </button>
                        @endif

                        @php
                            $currentPageDaily = $records->currentPage();
                            $lastPageDaily = $records->lastPage();
                            $startDaily = max(2, $currentPageDaily - 1);
                            $endDaily = min($lastPageDaily - 1, $currentPageDaily + 1);
                        @endphp

                        {{-- Page 1 --}}
                        @if($currentPageDaily == 1)
                            <span aria-current="page" class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600 text-white font-bold text-xs border border-indigo-600 select-none">{{ 1 }}</span>
                        @else
                            <button type="button" wire:click="gotoPage(1, 'dailyPage')" class="flex items-center justify-center w-8 h-8 rounded-lg bg-white text-slate-600 font-semibold text-xs border border-slate-200 hover:bg-slate-50 transition focus:outline-none">{{ 1 }}</button>
                        @endif

                        {{-- Ellipsis --}}
                        @if($startDaily > 2)
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-400 font-semibold text-xs border border-slate-200 select-none">...</span>
                        @endif

                        {{-- Sliding Range --}}
                        @for($page = $startDaily; $page <= $endDaily; $page++)
                            @if($page == $currentPageDaily)
                                <span aria-current="page" class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600 text-white font-bold text-xs border border-indigo-600 select-none">{{ $page }}</span>
                            @else
                                <button type="button" wire:click="gotoPage({{ $page }}, 'dailyPage')" class="flex items-center justify-center w-8 h-8 rounded-lg bg-white text-slate-600 font-semibold text-xs border border-slate-200 hover:bg-slate-50 transition focus:outline-none">{{ $page }}</button>
                            @endif
                        @endfor

                        {{-- Ellipsis --}}
                        @if($endDaily < $lastPageDaily - 1)
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-400 font-semibold text-xs border border-slate-200 select-none">...</span>
                        @endif

                        {{-- Last Page --}}
                        @if($lastPageDaily > 1)
                            @if($currentPageDaily == $lastPageDaily)
                                <span aria-current="page" class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600 text-white font-bold text-xs border border-indigo-600 select-none">{{ $lastPageDaily }}</span>
                            @else
                                <button type="button" wire:click="gotoPage({{ $lastPageDaily }}, 'dailyPage')" class="flex items-center justify-center w-8 h-8 rounded-lg bg-white text-slate-600 font-semibold text-xs border border-slate-200 hover:bg-slate-50 transition focus:outline-none">{{ $lastPageDaily }}</button>
                            @endif
                        @endif

                        {{-- Next --}}
                        @if($records->hasMorePages())
                            <button type="button" wire:click="nextPage('dailyPage')" class="flex items-center justify-center w-8 h-8 rounded-lg bg-white text-slate-500 border border-slate-200 hover:bg-slate-50 transition focus:outline-none">
                                <x-ts:icon name="tabler.chevron-right" class="w-4 h-4" />
                            </button>
                        @else
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-300 border border-slate-200 select-none cursor-not-allowed">
                                <x-ts:icon name="tabler.chevron-right" class="w-4 h-4" />
                            </span>
                        @endif
                    </nav>
                    <p class="text-[11px] text-slate-400 font-medium select-none">
                        Menampilkan <span class="font-bold text-slate-600">{{ $records->firstItem() ?? 0 }}</span> sampai <span class="font-bold text-slate-600">{{ $records->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-600">{{ $records->total() }}</span> log harian
                    </p>
                </div>
            @endif
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
