<div class="space-y-6">
    <!-- Welcome Header Section -->
    <div class="relative overflow-hidden rounded-2xl p-8 shadow-lg text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);">
        <div class="absolute right-0 top-0 -mr-20 -mt-20 h-80 w-80 rounded-full bg-indigo-500/10 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 h-80 w-80 rounded-full bg-purple-500/10 blur-3xl"></div>

        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/20 px-3 py-1 text-xs font-semibold text-indigo-300 backdrop-blur-md">
                    <span class="h-1.5 w-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                    {{ $userRoleName }}
                </span>
                <h1 class="mt-3 text-3xl font-bold tracking-tight md:text-4xl">
                    Selamat datang kembali, <span class="bg-gradient-to-r from-indigo-200 via-purple-200 to-pink-200 bg-clip-text text-transparent">{{ $userName }}</span>
                </h1>
                <p class="mt-2 text-sm text-slate-300">
                    Berikut adalah ringkasan performa dan aktivitas sistem hari ini.
                </p>
            </div>
            <div class="flex items-center gap-2 rounded-xl bg-white/5 p-4 backdrop-blur-md border border-white/10">
                <svg class="h-5 w-5 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                </svg>
                <div class="text-xs">
                    <p class="font-medium text-white">{{ now()->translatedFormat('l, d F Y') }}</p>
                    <p class="text-slate-400">Jam Kerja Aktif</p>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($jadwalHariIni))
        <!-- Today's Schedule Card -->
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 shrink-0">
                        <x-tabler-calendar-event class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Jadwal Tugas Hari Ini</h2>
                        <p class="text-xs text-slate-400">{{ $jadwalHariIni['tanggal'] }}</p>
                    </div>
                </div>
                <a href="/profile/jadwal-tugas-saya" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">
                    <span>Lihat Jadwal Saya</span>
                    <x-tabler-arrow-right class="h-3.5 w-3.5" />
                </a>
            </div>

            @if(isset($jadwalHariIni['no_schedule']) && $jadwalHariIni['no_schedule'])
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-4 border border-slate-100 text-slate-500">
                    <x-tabler-calendar-x class="h-6 w-6 text-slate-400 shrink-0" />
                    <span class="text-xs font-medium">Tidak ada jadwal kerja yang terdaftar untuk Anda pada hari ini.</span>
                </div>
            @elseif($jadwalHariIni['is_libur'])
                <div class="flex items-center gap-3 rounded-xl bg-rose-50/60 p-4 border border-rose-100 text-rose-700">
                    <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-rose-200 text-rose-800 shrink-0">LIBUR</span>
                    <div class="text-xs font-medium">
                        <span class="font-bold">Hari Libur Terjadwal</span> — Anda tidak memiliki shift kerja pada hari ini.
                    </div>
                </div>
            @else
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 rounded-xl bg-slate-50/60 p-4 border border-slate-100">
                    <!-- Shift & Jam -->
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-md shadow-2xs border border-black/5 shrink-0" style="background-color: {{ $jadwalHariIni['shift_warna'] }}; color: #1e293b;">
                            {{ $jadwalHariIni['shift_kode'] }}
                        </span>
                        <span class="text-sm font-bold text-slate-800">
                            {{ $jadwalHariIni['shift_nama'] }}
                        </span>
                        <span class="text-slate-300 hidden sm:inline">•</span>
                        <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 bg-white px-3 py-1 rounded-lg border border-slate-200/80 shadow-2xs">
                            <x-tabler-clock class="h-4 w-4 text-indigo-500 shrink-0" />
                            <span>{{ $jadwalHariIni['jam_masuk'] }}</span>
                            <span class="text-slate-300">—</span>
                            <span>{{ $jadwalHariIni['jam_keluar'] }}</span>
                            @if($jadwalHariIni['lintas_hari'])
                                <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-1 py-0.5 rounded">(H+1)</span>
                            @endif
                        </div>
                    </div>

                    <!-- Details: Ruangan & Status -->
                    <div class="flex flex-wrap items-center gap-4 text-xs">
                        <div class="flex items-center gap-1.5 text-slate-600">
                            <x-tabler-building-hospital class="h-4 w-4 text-slate-400 shrink-0" />
                            <span class="font-medium text-slate-500">Ruangan:</span>
                            <span class="font-bold text-slate-800">{{ $jadwalHariIni['ruangan'] }}</span>
                        </div>
                        @if($jadwalHariIni['status'])
                            <div class="flex items-center gap-1.5">
                                <span class="font-medium text-slate-500">Status:</span>
                                <x-ts:badge :color="$jadwalHariIni['status']->color()" text="{{ $jadwalHariIni['status']->nama() }}" sm />
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if(!empty($rekapAbsen))
        <!-- Personal Attendance Recap Card -->
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

    <!-- Stats Grid Section -->
    @php
        $statsCount = 0;
        if (isset($stats['karyawan_count'])) $statsCount++;
        if (isset($stats['barang_count'])) $statsCount++;
        if (isset($stats['total_hutang'])) $statsCount++;
        if (isset($stats['pending_cuti'])) $statsCount++;
        if (isset($stats['supplier_count']) && !isset($stats['barang_count'])) $statsCount++;
        if (isset($stats['total_pembayaran'])) $statsCount++;
        
        $gridColsClass = 'lg:grid-cols-4';
        if ($statsCount === 1) $gridColsClass = 'lg:grid-cols-1';
        elseif ($statsCount === 2) $gridColsClass = 'lg:grid-cols-2';
        elseif ($statsCount === 3) $gridColsClass = 'lg:grid-cols-3';
    @endphp

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 {{ $gridColsClass }}">
        @if(isset($stats['karyawan_count']))
            <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {{ isset($stats['ruangan_nama']) ? 'Karyawan ' . $stats['ruangan_nama'] : 'Total Karyawan' }}
                        </p>
                        <h3 class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['karyawan_count'] }}</h3>
                    </div>
                    <div class="rounded-xl bg-indigo-50 p-3 text-indigo-500 transition-colors duration-300 group-hover:bg-indigo-500 group-hover:text-white">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A11.386 11.386 0 0 1 10.089 20.8M4.12 18.046a8.81 8.81 0 0 1 4.12-.953c2.25 0 4.307.837 5.89 2.22M4.12 18.046a9.01 9.01 0 0 0-2.626.372c-.521.144-.915.544-1.019 1.077-.042.219-.071.44-.085.664a8.81 8.81 0 0 0 7.848 8.04M4.12 18.046a9.39 9.39 0 0 1 5.97-2.176M9 11.25a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM18 10.5a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1 text-xs text-indigo-600">
                    <span class="font-medium">SDM & Kepegawaian</span>
                </div>
            </div>
        @endif

        @if(isset($stats['barang_count']))
            <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Barang Master</p>
                        <h3 class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['barang_count'] }}</h3>
                    </div>
                    <div class="rounded-xl bg-teal-50 p-3 text-teal-500 transition-colors duration-300 group-hover:bg-teal-500 group-hover:text-white">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1 text-xs text-teal-600">
                    <span class="font-medium">Stok & Gudang</span>
                </div>
            </div>
        @endif

        @if(isset($stats['total_hutang']))
            <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tagihan Belum Bayar</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-800">{{ formatRupiah($stats['total_hutang'], true, false) }}</h3>
                    </div>
                    <div class="rounded-xl bg-rose-50 p-3 text-rose-500 transition-colors duration-300 group-hover:bg-rose-500 group-hover:text-white">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h.007v.008H3.75V4.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3 19.5h10.5m-10.5-6h9.75M3 9h10.5M19 5.25L16.5 7.75M16.5 5.25l2.5 2.5" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1 text-xs text-rose-600">
                    <span class="font-medium">Keuangan & Hutang</span>
                </div>
            </div>
        @endif

        @if(isset($stats['pending_cuti']))
            <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cuti Pending</p>
                        <h3 class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['pending_cuti'] }}</h3>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-3 text-amber-500 transition-colors duration-300 group-hover:bg-amber-500 group-hover:text-white">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75M9 11.25h.008v.008H9v-.008Zm3 0h.008v.008H12v-.008Zm3 0h.008v.008H15v-.008Zm-6 3h.008v.008H9v-.008Zm3 0h.008v.008H12v-.008Zm3 0h.008v.008H15v-.008Zm-6 3h.008v.008H9v-.008Zm3 0h.008v.008H12v-.008Zm3 0h.008v.008H15v-.008Z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1 text-xs text-amber-600">
                    <span class="font-medium">Menunggu Verifikasi</span>
                </div>
            </div>
        @endif

        @if(isset($stats['supplier_count']) && !isset($stats['barang_count']))
            <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mitra Supplier</p>
                        <h3 class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['supplier_count'] }}</h3>
                    </div>
                    <div class="rounded-xl bg-purple-50 p-3 text-purple-500 transition-colors duration-300 group-hover:bg-purple-500 group-hover:text-white">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.119c0-.451-.088-.892-.258-1.307a14.248 14.248 0 0 0-3.29-4.884 1.5 1.5 0 0 0-2.22 0 14.25 14.25 0 0 0-3.29 4.884c-.17.415-.258.856-.258 1.307v.119M16.5 18.75h-2.25" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1 text-xs text-purple-600">
                    <span class="font-medium">Relasi Umum</span>
                </div>
            </div>
        @endif

        @if(isset($stats['total_pembayaran']))
            <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Hutang Terbayar</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-800">{{ formatRupiah($stats['total_pembayaran'], true, false) }}</h3>
                    </div>
                    <div class="rounded-xl bg-emerald-50 p-3 text-emerald-500 transition-colors duration-300 group-hover:bg-emerald-500 group-hover:text-white">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1 text-xs text-emerald-600">
                    <span class="font-medium">Periode Transaksi</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Dynamic Feed / Lists -->
    @php
        $activeCardsList = [];
        if (!empty($recentCuti)) $activeCardsList[] = 'cuti';
        if (!empty($recentPurchases)) $activeCardsList[] = 'purchases';
        if (!empty($recentMaintenance)) $activeCardsList[] = 'maintenance';
        if (!empty($recentSp3)) $activeCardsList[] = 'sp3';
        
        $totalActive = count($activeCardsList);
    @endphp

    @if($totalActive > 0)
        <div class="grid grid-cols-1 gap-6 {{ $totalActive > 1 ? 'lg:grid-cols-2' : '' }}">
            @foreach($activeCardsList as $index => $cardType)
                @php
                    $isSingleOrLastOdd = ($totalActive === 1) || (($totalActive % 2 !== 0) && ($index === $totalActive - 1));
                    $colSpanClass = $isSingleOrLastOdd ? 'lg:col-span-2' : '';
                @endphp

                @if($cardType === 'cuti')
                    <!-- Cuti Card -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm {{ $colSpanClass }}">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                            <h2 class="text-lg font-bold text-slate-800">Pengajuan Cuti Terbaru</h2>
                            <a href="{{ route('kepegawaian.surat.cuti') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Lihat Semua &rarr;</a>
                        </div>
                        <div class="mt-4 divide-y divide-gray-100">
                            @foreach($recentCuti as $cuti)
                                <div class="flex items-center justify-between py-3.5">
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-700">{{ $cuti['karyawan']['nama'] ?? 'Karyawan' }}</h4>
                                        <p class="text-xs text-slate-400">Periode: {{ \Carbon\Carbon::parse($cuti['tgl_mulai'])->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($cuti['tgl_akhir'])->translatedFormat('d M Y') }}</p>
                                    </div>
                                    <div>
                                        @php
                                            $statusName = $cuti['status'] instanceof \App\Enums\StatusApproval ? $cuti['status']->nama() : $cuti['status'];
                                            $statusColor = $cuti['status'] instanceof \App\Enums\StatusApproval ? $cuti['status']->color() : 'gray';
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700 border border-{{ $statusColor }}-100">
                                            {{ $statusName }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($cardType === 'purchases')
                    <!-- Purchases Card -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm {{ $colSpanClass }}">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                            <h2 class="text-lg font-bold text-slate-800">Transaksi Pembelian Terbaru</h2>
                            <a href="{{ route('umum.pembelian.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Lihat Semua &rarr;</a>
                        </div>
                        <div class="mt-4 divide-y divide-gray-100">
                            @foreach($recentPurchases as $purchase)
                                <div class="flex items-center justify-between py-3.5">
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-700">{{ $purchase['no_po'] ?? 'Pembelian PO' }}</h4>
                                        <p class="text-xs text-slate-400">Supplier: {{ $purchase['supplier']['nama'] ?? '-' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-slate-800">{{ formatRupiah($purchase['total'], true, false) }}</p>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold tracking-wider uppercase {{ $purchase['status_pembayaran'] === 'lunas' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                                            {{ $purchase['status_pembayaran'] ?? 'tempo' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($cardType === 'maintenance')
                    <!-- Maintenance Card -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm {{ $colSpanClass }}">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                            <h2 class="text-lg font-bold text-slate-800">Jadwal Maintenance Asset</h2>
                            <a href="{{ route('umum.maintenance.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Lihat Semua &rarr;</a>
                        </div>
                        <div class="mt-4 divide-y divide-gray-100">
                            @foreach($recentMaintenance as $maint)
                                <div class="flex items-center justify-between py-3.5">
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-700">{{ $maint['asset']['nama'] ?? 'Asset' }}</h4>
                                        <p class="text-xs text-slate-400">Teknisi ditugaskan: {{ count($maint['teknisi'] ?? []) }} Orang</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-medium text-slate-500">Tgl Jadwal:</p>
                                        <p class="text-xs font-semibold text-indigo-600">{{ \Carbon\Carbon::parse($maint['tanggal'])->translatedFormat('d M Y') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($cardType === 'sp3')
                    <!-- SP3 Card -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm {{ $colSpanClass }}">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                            <h2 class="text-lg font-bold text-slate-800">SP3 Terbaru</h2>
                            <a href="{{ route('kepegawaian.surat.sp3') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Lihat Semua &rarr;</a>
                        </div>
                        <div class="mt-4 divide-y divide-gray-100">
                            @foreach($recentSp3 as $sp3)
                                <div class="flex items-center justify-between py-3.5">
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-700">SP3 No. {{ $sp3['no'] }} ({{ $sp3['tahun'] }})</h4>
                                        <p class="text-xs text-slate-400">Rekanan: {{ $sp3['rekanan'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-medium text-slate-500">Persetujuan:</p>
                                        <p class="text-xs font-semibold text-emerald-600">{{ $sp3['penyetuju']['nama'] ?? 'Disetujui' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <!-- Quick Navigation / Module Info -->
    <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
        <h3 class="text-lg font-bold text-slate-800">Modul Kerja Anda</h3>
        <p class="text-sm text-slate-500 mt-1">Gunakan tautan cepat di bawah ini untuk mengakses fitur utama sesuai tugas Anda.</p>
        
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mt-6">
            @if($userRoleName === 'Super-Admin' || $userRoleName === 'Staff-SDM')
                <a href="{{ route('kepegawaian.karyawan.index') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 hover:border-indigo-100 hover:bg-indigo-50/30 transition-all text-center">
                    <span class="rounded-lg bg-indigo-50 p-2.5 text-indigo-600 mb-2">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </span>
                    <span class="text-xs font-semibold text-slate-700">Data Karyawan</span>
                </a>
            @endif

            @if($userRoleName === 'Super-Admin' || $userRoleName === 'Bagian-Umum')
                <a href="{{ route('umum.master.barang') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 hover:border-teal-100 hover:bg-teal-50/30 transition-all text-center">
                    <span class="rounded-lg bg-teal-50 p-2.5 text-teal-600 mb-2">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                        </svg>
                    </span>
                    <span class="text-xs font-semibold text-slate-700">Manajemen Stok</span>
                </a>
            @endif

            @if($userRoleName === 'Super-Admin' || $userRoleName === 'Keuangan')
                <a href="{{ route('keuangan.hutang.index') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 hover:border-rose-100 hover:bg-rose-50/30 transition-all text-center">
                    <span class="rounded-lg bg-rose-50 p-2.5 text-rose-600 mb-2">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-1.971-.659-1.171-.88-1.171-2.303 0-3.182 1.171-.879 3.07-.879 4.242 0M9 6.241h.008v.008H9V6.241Zm0 11.518h.008v.008H9v-.008Z" />
                        </svg>
                    </span>
                    <span class="text-xs font-semibold text-slate-700">Buku Hutang</span>
                </a>
            @endif

            <a href="{{ route('dashboard.display-monitor.admin') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 hover:border-amber-100 hover:bg-amber-50/30 transition-all text-center">
                <span class="rounded-lg bg-amber-50 p-2.5 text-amber-600 mb-2">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                </span>
                <span class="text-xs font-semibold text-slate-700">Display Monitor Admin</span>
            </a>

            <a href="{{ route('dashboard.poli.admin') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 hover:border-violet-100 hover:bg-violet-50/30 transition-all text-center">
                <span class="rounded-lg bg-violet-50 p-2.5 text-violet-600 mb-2">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
                <span class="text-xs font-semibold text-slate-700">Poli Admin</span>
            </a>
        </div>
    </div>
</div>
