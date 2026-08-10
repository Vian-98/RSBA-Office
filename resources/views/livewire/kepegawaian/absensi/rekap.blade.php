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
            <div class="flex items-center justify-between mb-4">
                <button type="button" @click="showSummary = !showSummary" class="flex items-center gap-2 text-left hover:opacity-80 transition-opacity focus:outline-none group">
                     <h4 class="text-base font-semibold text-slate-800 select-none flex items-center gap-2">
                         <span>Rincian per Karyawan</span>
                     </h4>
                     <!-- Dynamic Chevron -->
                     <svg class="w-4 h-4 text-slate-400 transition-transform duration-300 transform group-hover:text-indigo-600" :class="showSummary ? 'rotate-180 text-indigo-600' : ''" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                     </svg>
                </button>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-medium select-none">Tampilkan per halaman:</span>
                    <div class="w-24">
                        <x-ts:select.styled 
                            wire:key="filter-per-page-select"
                            wire:model.live="perPage" 
                            :options="[
                                ['label' => '15', 'value' => 15],
                                ['label' => '25', 'value' => 25],
                                ['label' => '50', 'value' => 50],
                                ['label' => '75', 'value' => 75],
                                ['label' => '100', 'value' => 100],
                            ]"
                            select="label:label|value:value"
                        />
                    </div>
                </div>
            </div>
            
            <div x-show="showSummary" x-transition x-cloak class="overflow-hidden rounded-xl border border-slate-200 shadow-sm bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600 whitespace-nowrap">
                        <thead class="text-xs font-semibold text-slate-600 uppercase bg-slate-50/80 border-b border-slate-200">
                            <tr>
                                <th scope="col" class="px-3 py-3.5 text-center text-slate-500 w-12">No</th>
                                <th scope="col" class="px-5 py-3.5 text-slate-700">Nama Karyawan</th>
                                <th scope="col" class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1.5 justify-center">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Hadir
                                    </span>
                                </th>
                                <th scope="col" class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1.5 justify-center">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        Terlambat
                                    </span>
                                </th>
                                <th scope="col" class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1.5 justify-center">
                                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                        Pulang Cepat
                                    </span>
                                </th>
                                <th scope="col" class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1.5 justify-center">
                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                        Tidak Hadir
                                    </span>
                                </th>
                                <th scope="col" class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1.5 justify-center">
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        Cuti
                                    </span>
                                </th>
                                <th scope="col" class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1.5 justify-center">
                                        <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                                        Izin
                                    </span>
                                </th>
                                <th scope="col" class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1.5 justify-center">
                                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                        Perlu Verifikasi
                                    </span>
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1.5 justify-center">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                        Overtime
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($rekapKaryawan as $rk)
                                <tr wire:key="rekap-karyawan-row-{{ $rk['karyawan']->id }}" class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-3 py-3 text-center text-xs font-semibold text-slate-400 align-middle">
                                        {{ ($paginatedKaryawans ? ($paginatedKaryawans->currentPage() - 1) * $paginatedKaryawans->perPage() : 0) + $loop->iteration }}
                                    </td>
                                    <td class="px-5 py-3 font-medium text-slate-800 align-middle">
                                        <span class="truncate max-w-[220px]" title="{{ ucwords(strtolower($rk['karyawan']->full_nama ?? '-')) }}">
                                            {{ ucwords(strtolower($rk['karyawan']->full_nama ?? '-')) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center align-middle">
                                        @if($rk['hadir'] > 0)
                                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 min-w-[28px]">
                                                {{ $rk['hadir'] }}
                                            </span>
                                        @else
                                            <span class="text-slate-300 font-normal">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center align-middle">
                                        @if($rk['terlambat'] > 0)
                                            <div class="inline-flex flex-col items-center">
                                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/60 min-w-[28px]">
                                                    {{ $rk['terlambat'] }}
                                                </span>
                                                @if($rk['menit_terlambat'] > 0)
                                                    <span class="text-[10px] text-slate-400 mt-0.5 font-normal">({{ $rk['menit_terlambat'] }} mnt)</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-300 font-normal">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center align-middle">
                                        @if($rk['pulang_cepat'] > 0)
                                            <div class="inline-flex flex-col items-center">
                                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-200/60 min-w-[28px]">
                                                    {{ $rk['pulang_cepat'] }}
                                                </span>
                                                @if($rk['menit_pulang_cepat'] > 0)
                                                    <span class="text-[10px] text-slate-400 mt-0.5 font-normal">({{ $rk['menit_pulang_cepat'] }} mnt)</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-300 font-normal">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center align-middle">
                                        @if($rk['tidak_hadir'] > 0)
                                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/60 min-w-[28px]">
                                                {{ $rk['tidak_hadir'] }}
                                            </span>
                                        @else
                                            <span class="text-slate-300 font-normal">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center align-middle">
                                        @if($rk['cuti'] > 0)
                                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 min-w-[28px]">
                                                {{ $rk['cuti'] }}
                                            </span>
                                        @else
                                            <span class="text-slate-300 font-normal">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center align-middle">
                                        @if($rk['izin'] > 0)
                                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-cyan-50 text-cyan-700 border border-cyan-200/60 min-w-[28px]">
                                                {{ $rk['izin'] }}
                                            </span>
                                        @else
                                            <span class="text-slate-300 font-normal">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center align-middle">
                                        @if($rk['perlu_verifikasi'] > 0)
                                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 min-w-[28px]">
                                                {{ $rk['perlu_verifikasi'] }}
                                            </span>
                                        @else
                                            <span class="text-slate-300 font-normal">-</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-center align-middle">
                                        @if(($rk['total_overtime_menit'] ?? 0) > 0)
                                            @php
                                                $h = floor($rk['total_overtime_menit'] / 60);
                                                $m = $rk['total_overtime_menit'] % 60;
                                                $formatted = $h > 0 ? "{$h}j {$m}m" : "{$m}m";
                                            @endphp
                                            <button type="button" wire:click="openOtModal({{ $rk['karyawan']->id }})" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200/60 hover:bg-indigo-100 transition-colors focus:outline-none select-none">
                                                <span>{{ $formatted }}</span>
                                                <x-ts:icon name="tabler.info-circle" class="w-3.5 h-3.5 text-indigo-500" />
                                            </button>
                                        @else
                                            <span class="text-slate-300 font-normal">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-10 text-center text-slate-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <x-ts:icon name="tabler.folder-off" class="w-10 h-10 mb-2 text-slate-300" />
                                            <p class="text-sm font-medium">Tidak ada data absensi untuk filter yang dipilih.</p>
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
                
                <div class="flex items-center gap-3">
                    <x-ts:button wire:click="openGlobalHistoryModal" sm color="indigo" variant="outline" class="!py-1 font-semibold flex items-center gap-1.5 shadow-xs">
                        <x-ts:icon name="tabler.history" class="w-4 h-4 text-indigo-600" />
                        <span>Riwayat Log Koreksi</span>
                    </x-ts:button>
                    <span class="text-xs text-gray-500 bg-slate-100 py-1 px-2.5 rounded-full font-medium">Total: {{ number_format($records->total()) }} hari log</span>
                </div>
            </div>
            
            <div x-show="showLogs" x-transition x-cloak class="mt-4">
                <div class="overflow-hidden rounded-xl border border-slate-200 shadow-sm bg-white">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-600 whitespace-nowrap">
                            <thead class="text-xs font-semibold text-slate-600 uppercase bg-slate-50/80 border-b border-slate-200">
                                <tr>
                                    <th scope="col" class="px-3 py-3.5 text-center text-slate-500 w-12">No</th>
                                    <th scope="col" class="px-5 py-3.5">Tanggal</th>
                                    <th scope="col" class="px-5 py-3.5">Nama Karyawan</th>
                                    <th scope="col" class="px-4 py-3.5 text-center">Shift</th>
                                    <th scope="col" class="px-4 py-3.5 text-center">Jam Kerja (Shift)</th>
                                    <th scope="col" class="px-4 py-3.5 text-center">Jam Aktual (Mesin)</th>
                                    <th scope="col" class="px-4 py-3.5 text-center">Overtime</th>
                                    <th scope="col" class="px-4 py-3.5 text-center">
                                        <div class="flex items-center justify-center">
                                            <x-ts:dropdown position="bottom-end">
                                                <x-slot:action>
                                                    <button type="button" x-on:click="show = !show" class="flex items-center justify-center gap-1 text-xs font-semibold uppercase text-slate-600 hover:text-indigo-600 transition-colors focus:outline-none select-none">
                                                        <span>STATUS</span>
                                                        @if($statusFilter)
                                                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                                        @endif
                                                        <x-ts:icon name="tabler.filter" class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-600" />
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
                                    <th scope="col" class="px-5 py-3.5">Catatan / Alasan</th>
                                    <th scope="col" class="px-5 py-3.5 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                 @forelse($records as $r)
                                     <tr wire:key="daily-record-row-{{ $r->id }}" class="hover:bg-slate-50/80 transition-colors">
                                         <td class="px-3 py-3.5 text-center text-xs font-semibold text-slate-400 align-middle">
                                             {{ ($records ? ($records->currentPage() - 1) * $records->perPage() : 0) + $loop->iteration }}
                                         </td>
                                         <td class="px-5 py-3.5 font-semibold text-slate-800 text-xs">
                                             {{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('d M Y') }}
                                         </td>
                                         <td class="px-5 py-3.5">
                                             <div class="font-semibold text-slate-800 text-xs capitalize">{{ ucwords(strtolower($r->karyawan->nama ?? '-')) }}</div>
                                             <div class="text-[10px] text-slate-400">PIN: {{ $r->karyawan->pin_absen ?? '-' }}</div>
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
                                    <td colspan="10" class="px-6 py-8 text-center text-slate-500">
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

    <!-- Modal Rincian Kelebihan Jam (Overtime) -->
    <x-ts:modal wire="showOtModal" title="Rincian Kelebihan Jam (Overtime)" size="md">
        <div class="py-2">
            <div class="font-bold text-slate-800 border-b border-slate-100 pb-2 mb-3 flex items-center justify-between text-sm">
                <span>{{ $selectedOtKaryawan }}</span>
                <span class="text-indigo-600 font-bold bg-indigo-50 border border-indigo-100 px-2.5 py-0.5 rounded text-xs">{{ $selectedOtFormatted }}</span>
            </div>
            
            @if(count($selectedOtDetails) > 0)
                <ul class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                    @foreach($selectedOtDetails as $det)
                        <li class="border-b border-slate-100 pb-2 last:border-b-0 last:pb-0">
                            <div class="font-semibold text-slate-800 flex items-center justify-between text-xs">
                                <span>{{ $det['tanggal'] }}</span>
                                <span class="text-indigo-600 font-bold bg-indigo-50/80 px-2 py-0.5 rounded text-[11px]">
                                    @php
                                        $dh = floor($det['menit'] / 60);
                                        $dm = $det['menit'] % 60;
                                        echo $dh > 0 ? "{$dh}j {$dm}m" : "{$dm}m";
                                    @endphp
                                </span>
                            </div>
                            @if($det['keterangan'] && $det['keterangan'] !== 'Pulang terlambat')
                                <div class="text-[11px] text-amber-600 mt-1 font-medium">{{ $det['keterangan'] }}</div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-xs text-slate-400 italic text-center py-4">Tidak ada rincian lembur.</p>
            @endif
        </div>
        
        <x-slot:footer>
            <div class="flex justify-end">
                <x-ts:button color="gray" variant="flat" wire:click="$set('showOtModal', false)">Tutup</x-ts:button>
            </div>
        </x-slot:footer>
    </x-ts:modal>

    <!-- Modal Audit Log Riwayat Koreksi (Global) -->
    <x-ts:modal wire="showGlobalHistoryModal" title="Riwayat Audit Log Koreksi Absensi" size="5xl">
        <div class="py-2">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 border-b border-slate-100 pb-3">
                <div>
                    <h5 class="font-bold text-slate-800 text-sm">Audit Trail Perubahan Data Absensi</h5>
                    <p class="text-xs text-slate-400">Pencatatan riwayat koreksi manual absensi oleh staf/pengguna</p>
                </div>
                
                <div class="w-full sm:w-72">
                    <x-ts:input 
                        wire:model.live.debounce.300ms="historySearch" 
                        placeholder="Cari karyawan, pengedit, status..." 
                        icon="tabler.search"
                        class="!py-1.5 text-xs"
                    />
                </div>
            </div>

            @if($globalHistoryLogs && $globalHistoryLogs->count() > 0)
                <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-xs">
                    <table class="w-full text-xs text-left text-slate-600 whitespace-nowrap">
                        <thead class="text-[11px] font-bold text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th scope="col" class="px-3.5 py-3">Waktu Edit</th>
                                <th scope="col" class="px-4 py-3">Nama Karyawan</th>
                                <th scope="col" class="px-3.5 py-3">Tgl Absen</th>
                                <th scope="col" class="px-4 py-3">Perubahan Status</th>
                                <th scope="col" class="px-4 py-3">Perubahan Jam (Masuk / Keluar)</th>
                                <th scope="col" class="px-3.5 py-3">Diubah Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($globalHistoryLogs as $log)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-3.5 py-3 whitespace-nowrap text-slate-500 font-medium">
                                        {{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-800">
                                        {{ ucwords(strtolower($log->karyawan->full_nama ?? $log->karyawan->nama ?? '-')) }}
                                    </td>
                                    <td class="px-3.5 py-3 whitespace-nowrap text-slate-600">
                                        {{ \Carbon\Carbon::parse($log->tanggal)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1.5 font-medium">
                                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[11px] border border-slate-200">{{ $log->status_lama ?? 'belum_dicek' }}</span>
                                            <x-ts:icon name="tabler.arrow-right" class="w-3.5 h-3.5 text-slate-400" />
                                            <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold text-[11px] border border-indigo-200/60">{{ $log->status_baru }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-[11px]">
                                        <div>
                                            <span class="text-slate-400">Masuk:</span> 
                                            {{ $log->absen_masuk_lama ? \Carbon\Carbon::parse($log->absen_masuk_lama)->format('H:i') : '-' }} 
                                            <span class="text-indigo-500 font-bold">➔</span> 
                                            <strong class="text-indigo-700">{{ $log->absen_masuk_baru ? \Carbon\Carbon::parse($log->absen_masuk_baru)->format('H:i') : '-' }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-slate-400">Keluar:</span> 
                                            {{ $log->absen_keluar_lama ? \Carbon\Carbon::parse($log->absen_keluar_lama)->format('H:i') : '-' }} 
                                            <span class="text-indigo-500 font-bold">➔</span> 
                                            <strong class="text-indigo-700">{{ $log->absen_keluar_baru ? \Carbon\Carbon::parse($log->absen_keluar_baru)->format('H:i') : '-' }}</strong>
                                        </div>
                                    </td>
                                    <td class="px-3.5 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">
                                            <x-ts:icon name="tabler.user" class="w-3 h-3 text-indigo-500" />
                                            <span>{{ ucwords(strtolower($log->user?->karyawan?->full_nama ?? $log->user?->karyawan?->nama ?? $log->user?->email ?? 'Sistem')) }}</span>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($globalHistoryLogs->hasPages())
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                        <span class="text-xs text-slate-400">
                            Menampilkan {{ $globalHistoryLogs->firstItem() }}-{{ $globalHistoryLogs->lastItem() }} dari {{ number_format($globalHistoryLogs->total()) }} log
                        </span>
                        <div class="flex items-center gap-1">
                            @if($globalHistoryLogs->onFirstPage())
                                <span class="px-2.5 py-1 text-xs rounded bg-slate-50 text-slate-300 border border-slate-200 select-none">Sebelumnya</span>
                            @else
                                <button type="button" wire:click="previousPage('historyLogPage')" class="px-2.5 py-1 text-xs rounded bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 transition">Sebelumnya</button>
                            @endif

                            @if($globalHistoryLogs->hasMorePages())
                                <button type="button" wire:click="nextPage('historyLogPage')" class="px-2.5 py-1 text-xs rounded bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 transition">Selanjutnya</button>
                            @else
                                <span class="px-2.5 py-1 text-xs rounded bg-slate-50 text-slate-300 border border-slate-200 select-none">Selanjutnya</span>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div class="flex flex-col items-center justify-center py-10 text-slate-400 text-xs">
                    <x-ts:icon name="tabler.history-off" class="w-10 h-10 mb-2 text-slate-300" />
                    <p class="font-medium">Tidak ada log koreksi yang ditemukan.</p>
                </div>
            @endif
        </div>

        <x-slot:footer>
            <div class="flex justify-end">
                <x-ts:button color="gray" variant="flat" wire:click="$set('showGlobalHistoryModal', false)">Tutup</x-ts:button>
            </div>
        </x-slot:footer>
    </x-ts:modal>
</div>
