<!-- Personal Attendance Recap Card -->
@if(!empty($rekapAbsen))
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col 2xl:flex-row items-center justify-between gap-6">
            <!-- Left: Title and Circle Gauge -->
            <div class="flex flex-col sm:flex-row items-center gap-6">
                <!-- Gauge Circle -->
                <div class="relative flex items-center justify-center h-24 w-24 flex-shrink-0">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-slate-100" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-indigo-600 transition-all duration-500 ease-out" stroke-width="3" stroke-dasharray="{{ $rekapAbsen['persen_kehadiran'] }}, 100" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute text-center">
                        <span class="text-xl font-bold text-slate-800">{{ $rekapAbsen['persen_kehadiran'] }}%</span>
                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">Hadir</p>
                    </div>
                </div>
                <div class="text-center sm:text-left">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3">
                        <h2 class="text-lg font-bold text-slate-800">Rekap Absensi Saya</h2>
                        
                        <!-- Month & Year Selectors -->
                        <div class="flex items-center gap-1.5" x-data="{ openBulan: false, openTahun: false }">
                            <!-- Month Selector Dropdown -->
                            <div class="relative">
                                <button type="button" @click="openBulan = !openBulan" @click.away="openBulan = false"
                                    class="flex items-center gap-1.5 text-xs rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-slate-700 font-semibold shadow-2xs hover:bg-slate-100 transition-colors duration-200">
                                    <span>{{ \Carbon\Carbon::create(2026, $selectedBulan, 1)->translatedFormat('F') }}</span>
                                    <x-tabler-chevron-down class="h-3.5 w-3.5 text-slate-400" />
                                </button>
                                <div x-show="openBulan" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute left-0 top-full z-50 mt-1 max-h-48 w-32 overflow-y-auto rounded-lg border border-slate-100 bg-white py-1.5 shadow-lg" style="display: none;">
                                    @for($m = 1; $m <= 12; $m++)
                                        <button type="button" wire:click="$set('selectedBulan', {{ $m }})" @click="openBulan = false"
                                            class="w-full px-3 py-1.5 text-left text-xs text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 font-medium transition-colors {{ $selectedBulan == $m ? 'bg-indigo-50 text-indigo-600 font-bold' : '' }}">
                                            {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                                        </button>
                                    @endfor
                                </div>
                            </div>

                            <!-- Year Selector Dropdown -->
                            <div class="relative">
                                <button type="button" @click="openTahun = !openTahun" @click.away="openTahun = false"
                                    class="flex items-center gap-1.5 text-xs rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-slate-700 font-semibold shadow-2xs hover:bg-slate-100 transition-colors duration-200">
                                    <span>{{ $selectedTahun }}</span>
                                    <x-tabler-chevron-down class="h-3.5 w-3.5 text-slate-400" />
                                </button>
                                <div x-show="openTahun" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute left-0 top-full z-50 mt-1 max-h-48 w-24 overflow-y-auto rounded-lg border border-slate-100 bg-white py-1.5 shadow-lg" style="display: none;">
                                    @for($y = date('Y') + 1; $y >= 2008; $y--)
                                        <button type="button" wire:click="$set('selectedTahun', {{ $y }})" @click="openTahun = false"
                                            class="w-full px-3 py-1.5 text-left text-xs text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 font-medium transition-colors {{ $selectedTahun == $y ? 'bg-indigo-50 text-indigo-600 font-bold' : '' }}">
                                            {{ $y }}
                                        </button>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Periode: <span class="font-semibold text-slate-700">{{ $rekapAbsen['bulan_nama'] }}</span></p>
                    <p class="text-[10px] text-slate-400 mt-1">Dihitung berdasarkan jadwal kerja aktif Anda.</p>
                </div>
            </div>
            
            <!-- Right: Stats Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 w-full 2xl:w-auto flex-grow max-w-5xl">
                <!-- Hadir (Tepat Waktu) -->
                <div class="rounded-xl bg-emerald-50/50 border border-emerald-100/50 p-4 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Tepat Waktu</span>
                    <h4 class="text-2xl font-bold text-emerald-800 mt-1">{{ $rekapAbsen['hadir'] }}</h4>
                    <p class="text-[10px] text-emerald-600 font-semibold">Hari</p>
                </div>

                <!-- Terlambat -->
                <div class="rounded-xl bg-amber-50/50 border border-amber-100/50 p-4 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Terlambat</span>
                    <h4 class="text-2xl font-bold text-amber-800 mt-1">{{ $rekapAbsen['terlambat'] }}</h4>
                    <p class="text-[10px] text-amber-600 font-semibold">{{ $rekapAbsen['menit_terlambat'] }} Menit</p>
                </div>

                <!-- Pulang Cepat -->
                <div class="rounded-xl bg-orange-50/50 border border-orange-100/50 p-4 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-orange-700">Pulang Cepat</span>
                    <h4 class="text-2xl font-bold text-orange-800 mt-1">{{ $rekapAbsen['pulang_cepat'] }}</h4>
                    <p class="text-[10px] text-orange-600 font-semibold">{{ $rekapAbsen['menit_pulang_cepat'] }} Menit</p>
                </div>

                <!-- Cuti / Izin -->
                <div class="rounded-xl bg-sky-50/50 border border-sky-100/50 p-4 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-sky-700">Cuti & Izin</span>
                    <h4 class="text-2xl font-bold text-sky-800 mt-1">{{ $rekapAbsen['cuti_izin'] }}</h4>
                    <p class="text-[10px] text-sky-600 font-semibold">Hari</p>
                </div>

                <!-- Alpa / Tidak Hadir -->
                <div class="rounded-xl bg-rose-50/50 border border-rose-100/50 p-4 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700">Tidak Hadir</span>
                    <h4 class="text-2xl font-bold text-rose-800 mt-1">{{ $rekapAbsen['tidak_hadir'] }}</h4>
                    <p class="text-[10px] text-rose-600 font-semibold">Hari</p>
                </div>
            </div>
        </div>
    </div>
@endif
