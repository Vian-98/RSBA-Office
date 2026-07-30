<div class="space-y-6">
    <!-- Printable Header (Visible only when printing) -->
    <div class="hidden print:block mb-6 text-center border-b pb-4">
        <h1 class="text-2xl font-bold uppercase tracking-wider text-slate-900">Rumah Sakit Bagas Waras</h1>
        <h2 class="text-lg font-semibold text-slate-700 mt-1">Laporan Analisis & Statistik Kepegawaian</h2>
        <p class="text-xs text-slate-500 mt-1">Dicetak pada: {{ date('d F Y, H:i') }} WIB</p>
    </div>

    <!-- Header & Action Card (Hidden when printing) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs print:hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-800">Laporan Statistik Kepegawaian</h1>
                <p class="text-xs text-slate-500 mt-1">Analisis demografi karyawan, status kepegawaian, tingkat pendidikan, distribusi unit/bagian, dan absensi.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2.5 shrink-0">
                <!-- Print Button -->
                <button onclick="window.print()" 
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-slate-50 hover:bg-slate-100 px-3.5 py-2 text-xs font-semibold text-slate-700 transition-all duration-200">
                    <x-tabler-printer class="h-4 w-4 text-slate-500" />
                    Cetak / PDF
                </button>

                <!-- Export Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-indigo-700 transition-all duration-200">
                        <x-tabler-download class="h-4 w-4" />
                        Export Laporan
                        <x-tabler-chevron-down class="h-3.5 w-3.5 text-indigo-200" />
                    </button>

                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-2xl bg-white p-2 shadow-xl border border-slate-100 focus:outline-hidden" style="display: none;">
                        <button wire:click="exportExcel" @click="open = false" 
                                class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                            <x-tabler-file-spreadsheet class="h-4 w-4 text-emerald-600" />
                            Export Detail (.xlsx)
                        </button>
                        <button wire:click="exportCsv" @click="open = false" 
                                class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                            <x-tabler-file-text class="h-4 w-4 text-blue-600" />
                            Export Detail (.csv)
                        </button>
                        <div class="my-1 border-t border-slate-100"></div>
                        <button wire:click="exportBagianCsv" @click="open = false" 
                                class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-violet-50 hover:text-violet-700 transition-colors">
                            <x-tabler-building class="h-4 w-4 text-violet-600" />
                            Export Rekap Bagian (.csv)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Segmented Pill Navigation Tabs -->
        <div class="mt-4 pt-4 border-t border-slate-100">
            <nav class="flex flex-wrap gap-2" aria-label="Tabs">
                <button wire:click="$set('activeTab', 'overview')" 
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all duration-200 {{ $activeTab === 'overview' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80 hover:text-slate-900' }}">
                    <x-tabler-chart-pie class="h-4 w-4 {{ $activeTab === 'overview' ? 'text-white' : 'text-slate-500' }}" />
                    Overview & Demografi
                </button>

                <button wire:click="$set('activeTab', 'detail')" 
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all duration-200 {{ $activeTab === 'detail' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80 hover:text-slate-900' }}">
                    <x-tabler-users class="h-4 w-4 {{ $activeTab === 'detail' ? 'text-white' : 'text-slate-500' }}" />
                    Detail Data Karyawan
                    <span class="rounded-lg px-2 py-0.5 text-[10px] font-extrabold {{ $activeTab === 'detail' ? 'bg-indigo-700 text-indigo-100' : 'bg-slate-200 text-slate-700' }}">
                        {{ $stats['total_karyawan'] ?? 0 }}
                    </span>
                </button>

                <button wire:click="$set('activeTab', 'bagian')" 
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all duration-200 {{ $activeTab === 'bagian' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80 hover:text-slate-900' }}">
                    <x-tabler-building class="h-4 w-4 {{ $activeTab === 'bagian' ? 'text-white' : 'text-slate-500' }}" />
                    Rekap Per Bagian
                </button>

                <button wire:click="$set('activeTab', 'kehadiran')" 
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all duration-200 {{ $activeTab === 'kehadiran' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80 hover:text-slate-900' }}">
                    <x-tabler-clock-check class="h-4 w-4 {{ $activeTab === 'kehadiran' ? 'text-white' : 'text-slate-500' }}" />
                    Kehadiran Hari Ini
                </button>
            </nav>
        </div>
    </div>

    <!-- Summary KPI Stats Cards (Show on Overview) -->
    @if($activeTab === 'overview')
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1: Total Karyawan -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-5 shadow-2xs transition-all duration-300 hover:shadow-xs hover:-translate-y-0.5">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <x-tabler-users class="h-6 w-6" />
                </span>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Karyawan</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-0.5">{{ $stats['total_karyawan'] }}</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $stats['male'] }} Pria · {{ $stats['female'] }} Wanita</p>
                </div>
            </div>
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-500"></div>
        </div>

        <!-- Card 2: Pegawai Tetap -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-5 shadow-2xs transition-all duration-300 hover:shadow-xs hover:-translate-y-0.5">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <x-tabler-id-badge class="h-6 w-6" />
                </span>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pegawai Tetap</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-0.5">{{ $stats['status_tetap'] }}</h3>
                    <p class="text-[11px] text-emerald-600 font-medium mt-0.5">
                        {{ round(($stats['status_tetap'] / max(1, $stats['total_karyawan'])) * 100) }}% Dari Total Staff
                    </p>
                </div>
            </div>
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
        </div>

        <!-- Card 3: Pegawai Kontrak -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-5 shadow-2xs transition-all duration-300 hover:shadow-xs hover:-translate-y-0.5">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <x-tabler-file-certificate class="h-6 w-6" />
                </span>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pegawai Kontrak</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-0.5">{{ $stats['status_kontrak'] }}</h3>
                    <p class="text-[11px] text-amber-600 font-medium mt-0.5">
                        {{ round(($stats['status_kontrak'] / max(1, $stats['total_karyawan'])) * 100) }}% Dari Total Staff
                    </p>
                </div>
            </div>
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500"></div>
        </div>

        <!-- Card 4: Sedang Cuti -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-5 shadow-2xs transition-all duration-300 hover:shadow-xs hover:-translate-y-0.5">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                    <x-tabler-calendar-event class="h-6 w-6" />
                </span>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sedang Cuti</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-0.5">{{ $stats['active_cuti'] }}</h3>
                    <p class="text-[11px] text-rose-500 font-medium mt-0.5">Izin / Cuti Disetujui</p>
                </div>
            </div>
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-red-500"></div>
        </div>
    </div>
    @endif

    <!-- TAB 1: OVERVIEW & DEMOGRAFI -->
    @if($activeTab === 'overview')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 items-stretch">
        
        <!-- Column 1: Demografi (Gender & Status) -->
        <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-2xs flex flex-col justify-between h-full">
            <div>
                <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-5 flex items-center gap-2">
                    <x-tabler-users-group class="h-5 w-5 text-indigo-500" />
                    Demografi & Gender
                </h2>
                
                @php
                    $totalGen = max(1, $stats['male'] + $stats['female']);
                    $pctMale = round(($stats['male'] / $totalGen) * 100);
                    $pctFemale = round(($stats['female'] / $totalGen) * 100);
                @endphp
                <!-- Gender Distribution -->
                <div class="space-y-4 mb-6">
                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-400">
                        <span>Jenis Kelamin</span>
                        <span>Total: {{ $stats['male'] + $stats['female'] }}</span>
                    </div>
                    
                    <!-- Male Bar -->
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-1.5">
                            <span class="flex items-center gap-1.5"><x-tabler-gender-male class="h-4.5 w-4.5 text-blue-500" /> Laki-laki</span>
                            <span class="font-bold text-slate-900">{{ $stats['male'] }} Orang ({{ $pctMale }}%)</span>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-slate-100 overflow-hidden p-0.5">
                            <div class="h-full bg-blue-500 rounded-full transition-all duration-500" style="width: {{ $pctMale }}%"></div>
                        </div>
                    </div>

                    <!-- Female Bar -->
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-1.5">
                            <span class="flex items-center gap-1.5"><x-tabler-gender-female class="h-4.5 w-4.5 text-rose-500" /> Perempuan</span>
                            <span class="font-bold text-slate-900">{{ $stats['female'] }} Orang ({{ $pctFemale }}%)</span>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-slate-100 overflow-hidden p-0.5">
                            <div class="h-full bg-rose-500 rounded-full transition-all duration-500" style="width: {{ $pctFemale }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Kepegawaian Status -->
                <div class="space-y-3.5 border-t border-slate-100 pt-5">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Kepegawaian</h3>
                    
                    @php
                        $statuses = [
                            ['label' => 'Pegawai Tetap', 'count' => $stats['status_tetap'], 'color' => 'bg-emerald-500'],
                            ['label' => 'Pegawai Kontrak', 'count' => $stats['status_kontrak'], 'color' => 'bg-amber-500'],
                            ['label' => 'Magang / Intern', 'count' => $stats['status_magang'], 'color' => 'bg-rose-500'],
                            ['label' => 'Mitra / Tamu', 'count' => $stats['status_mitra'], 'color' => 'bg-blue-500'],
                            ['label' => 'Perbantuan', 'count' => $stats['status_bantuan'], 'color' => 'bg-slate-500'],
                        ];
                    @endphp

                    @foreach($statuses as $st)
                        @php
                            $pctSt = round(($st['count'] / $totalGen) * 100);
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                                <span>{{ $st['label'] }}</span>
                                <span class="font-bold text-slate-700">{{ $st['count'] }} Orang ({{ $pctSt }}%)</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full {{ $st['color'] }} rounded-full" style="width: {{ $pctSt }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Column 2: Pendidikan Terakhir -->
        <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-2xs flex flex-col justify-between h-full">
            <div>
                <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-5 flex items-center gap-2">
                    <x-tabler-school class="h-5 w-5 text-indigo-500" />
                    Tingkat Pendidikan
                </h2>
                
                <div class="space-y-4">
                    @php
                        $educationList = [
                            ['key' => 's3', 'label' => 'Pendidikan S3'],
                            ['key' => 's2', 'label' => 'Pendidikan S2'],
                            ['key' => 's1', 'label' => 'Pendidikan S1'],
                            ['key' => 'd3', 'label' => 'Pendidikan D3'],
                            ['key' => 'sma', 'label' => 'SMA / SMK'],
                            ['key' => 'smp', 'label' => 'SMP'],
                            ['key' => 'sd', 'label' => 'SD / Sederajat'],
                        ];
                        $totalEdu = max(1, array_sum($stats['education']));
                    @endphp

                    @foreach($educationList as $edu)
                        @php
                            $count = $stats['education'][$edu['key']] ?? 0;
                            $pctEdu = round(($count / $totalEdu) * 100);
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                                <span>{{ $edu['label'] }}</span>
                                <span class="font-bold text-slate-700">{{ $count }} Karyawan ({{ $pctEdu }}%)</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-violet-500 rounded-full transition-all duration-500" style="width: {{ $pctEdu }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Column 3: Penempatan Bagian -->
        <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-2xs flex flex-col justify-between h-full">
            <div>
                <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-5 flex items-center gap-2">
                    <x-tabler-building class="h-5 w-5 text-indigo-500" />
                    Distribusi Per Bagian Top
                </h2>
                
                <div class="space-y-4">
                    @php
                        $totalKary = max(1, $stats['total_karyawan']);
                        $topBagians = collect($bagianBreakdown)->take(7);
                    @endphp
                    @foreach($topBagians as $bagian)
                        <div>
                            <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                                <span class="truncate max-w-[180px]">{{ $bagian['nama'] }}</span>
                                <span class="font-bold text-slate-700">{{ $bagian['total'] }} Staff ({{ $bagian['percentage'] }}%)</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full transition-all duration-500" style="width: {{ $bagian['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-t border-slate-100">
                <button wire:click="$set('activeTab', 'bagian')" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                    Lihat semua rekap bagian &rarr;
                </button>
            </div>
        </div>

    </div>

    <!-- Kehadiran Hari Ini Summary Grid -->
    <div class="mt-8 rounded-2xl border border-slate-200/70 bg-white p-6 shadow-2xs">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-md font-bold text-slate-800 flex items-center gap-2">
                <x-tabler-clock class="h-5 w-5 text-indigo-500" />
                Ringkasan Kehadiran Hari Ini ({{ date('d F Y') }})
            </h2>
            <button wire:click="$set('activeTab', 'kehadiran')" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                Detail Kehadiran &rarr;
            </button>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
            <div class="bg-emerald-50/60 border border-emerald-200/60 rounded-xl p-3.5 text-center">
                <p class="text-[10px] text-emerald-700 font-bold tracking-wider uppercase mb-1">HADIR</p>
                <p class="text-2xl font-black text-emerald-600">{{ $absensiStats['hadir'] }}</p>
            </div>
            <div class="bg-amber-50/60 border border-amber-200/60 rounded-xl p-3.5 text-center">
                <p class="text-[10px] text-amber-700 font-bold tracking-wider uppercase mb-1">TERLAMBAT</p>
                <p class="text-2xl font-black text-amber-600">{{ $absensiStats['terlambat'] }}</p>
            </div>
            <div class="bg-orange-50/60 border border-orange-200/60 rounded-xl p-3.5 text-center">
                <p class="text-[10px] text-orange-700 font-bold tracking-wider uppercase mb-1">PULANG CEPAT</p>
                <p class="text-2xl font-black text-orange-600">{{ $absensiStats['pulang_cepat'] }}</p>
            </div>
            <div class="bg-rose-50/60 border border-rose-200/60 rounded-xl p-3.5 text-center">
                <p class="text-[10px] text-rose-700 font-bold tracking-wider uppercase mb-1">TIDAK HADIR</p>
                <p class="text-2xl font-black text-rose-600">{{ $absensiStats['tidak_hadir'] }}</p>
            </div>
            <div class="bg-blue-50/60 border border-blue-200/60 rounded-xl p-3.5 text-center">
                <p class="text-[10px] text-blue-700 font-bold tracking-wider uppercase mb-1">CUTI</p>
                <p class="text-2xl font-black text-blue-600">{{ $absensiStats['cuti'] }}</p>
            </div>
            <div class="bg-indigo-50/60 border border-indigo-200/60 rounded-xl p-3.5 text-center">
                <p class="text-[10px] text-indigo-700 font-bold tracking-wider uppercase mb-1">IZIN</p>
                <p class="text-2xl font-black text-indigo-600">{{ $absensiStats['izin'] }}</p>
            </div>
            <div class="bg-slate-50 border border-slate-200/60 rounded-xl p-3.5 text-center">
                <p class="text-[10px] text-slate-500 font-bold tracking-wider uppercase mb-1">VERIFIKASI</p>
                <p class="text-2xl font-black text-slate-700">{{ $absensiStats['perlu_verifikasi'] }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- TAB 2: DETAIL DATA KARYAWAN -->
    @if($activeTab === 'detail')
    <div class="space-y-4">
        <!-- Filter Controls Bar -->
        <div class="rounded-2xl border border-slate-200/70 bg-white p-4 shadow-2xs print:hidden">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search Input -->
                <div class="relative">
                    <x-tabler-search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Cari Nama, NIP, NIK..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-9 pr-3 py-2 text-xs font-medium text-slate-800 focus:bg-white focus:border-indigo-500 focus:outline-hidden transition-all" />
                </div>

                <!-- Bagian Select -->
                <div>
                    <select wire:model.live="selectedBagian" 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-800 focus:bg-white focus:border-indigo-500 focus:outline-hidden transition-all">
                        <option value="">-- Semua Bagian --</option>
                        @foreach($bagianList as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Select -->
                <div>
                    <select wire:model.live="selectedStatus" 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-800 focus:bg-white focus:border-indigo-500 focus:outline-hidden transition-all">
                        <option value="">-- Semua Status --</option>
                        <option value="tetap">Pegawai Tetap</option>
                        <option value="kontrak">Pegawai Kontrak</option>
                        <option value="magang">Magang / Intern</option>
                        <option value="mitra">Mitra / Tamu</option>
                        <option value="bantuan">Perbantuan</option>
                    </select>
                </div>

                <!-- Gender Select -->
                <div>
                    <select wire:model.live="selectedJk" 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-800 focus:bg-white focus:border-indigo-500 focus:outline-hidden transition-all">
                        <option value="">-- All Gender --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>

                <!-- Reset Filters Button -->
                <div>
                    <button wire:click="resetFilters" 
                            class="w-full rounded-xl border border-slate-200 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2 px-3 text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors">
                        <x-tabler-rotate-clockwise class="h-3.5 w-3.5 text-slate-500" />
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Employee Table -->
        <div class="rounded-2xl border border-slate-200/70 bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">Karyawan</th>
                            <th class="py-3.5 px-4">NIP / NIK</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Bagian & Jabatan</th>
                            <th class="py-3.5 px-4">Masa Kerja</th>
                            <th class="py-3.5 px-4">Kontak</th>
                            <th class="py-3.5 px-4 text-right print:hidden">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($karyawans as $kary)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-xs shadow-2xs">
                                        {{ strtoupper(substr($kary->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-xs">{{ $kary->full_nama }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $kary->jk === 'L' ? 'Laki-laki' : 'Perempuan' }} · {{ ucfirst($kary->agama ?? '-') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px]">
                                <p class="font-semibold text-slate-700">{{ $kary->nip }}</p>
                                <p class="text-slate-400">{{ $kary->nik }}</p>
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $stVal = is_object($kary->status) ? $kary->status->value : $kary->status;
                                    $badgeClass = match($stVal) {
                                        'tetap' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'kontrak' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'magang' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'mitra' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200'
                                    };
                                    $labelStr = is_object($kary->status) ? $kary->status->nama() : ucfirst($stVal ?? '-');
                                @endphp
                                <span class="inline-flex items-center rounded-lg px-2 py-0.5 text-[10px] font-bold border {{ $badgeClass }}">
                                    {{ $labelStr }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-semibold text-slate-800">
                                    {{ $kary->latestJabatan?->jabatan?->bagian?->nama ?? '-' }}
                                </p>
                                <p class="text-[11px] text-slate-400">
                                    {{ $kary->latestJabatan?->jabatan?->nama ?? '-' }}
                                </p>
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-medium text-slate-700">{{ $kary->masakerja }}</p>
                                <p class="text-[11px] text-slate-400">Masuk: {{ date('d-m-Y', strtotime($kary->tgl_masuk)) }}</p>
                            </td>
                            <td class="py-3 px-4 text-[11px]">
                                <p class="font-medium text-slate-700">{{ $kary->hp ?? '-' }}</p>
                                <p class="text-slate-400 truncate max-w-[140px]">{{ $kary->dom_alamat ?? $kary->alamat ?? '-' }}</p>
                            </td>
                            <td class="py-3 px-4 text-right print:hidden">
                                <a href="{{ route('kepegawaian.karyawan.edit', $kary->id) }}" 
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg transition-colors">
                                    <x-tabler-edit class="h-3.5 w-3.5" />
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <x-tabler-users-minus class="mx-auto h-8 w-8 text-slate-300 mb-2" />
                                <p class="font-semibold text-slate-600">Tidak ada data karyawan ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau reset filter.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($karyawans && $karyawans->hasPages())
            <div class="border-t border-slate-200/70 px-4 py-3 bg-slate-50/50 print:hidden">
                {{ $karyawans->links() }}
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- TAB 3: REKAP PER BAGIAN -->
    @if($activeTab === 'bagian')
    <div class="space-y-4">
        <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-2xs overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-md font-bold text-slate-800">Ringkasan Distribusi Staff Per Bagian / Unit</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Statistik jumlah karyawan, komposisi gender, dan status kepegawaian di tiap departemen RSBA.</p>
                </div>
                <button wire:click="exportBagianCsv" 
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50">
                    <x-tabler-download class="h-3.5 w-3.5 text-slate-500" />
                    Export CSV Bagian
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">Bagian / Departemen</th>
                            <th class="py-3 px-4 text-center">Total Staff</th>
                            <th class="py-3 px-4 text-center">Pria</th>
                            <th class="py-3 px-4 text-center">Wanita</th>
                            <th class="py-3 px-4 text-center">Tetap</th>
                            <th class="py-3 px-4 text-center">Kontrak</th>
                            <th class="py-3 px-4 text-center">Lainnya</th>
                            <th class="py-3 px-4">Porsi (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($bagianBreakdown as $idx => $row)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $row['nama'] }}</td>
                            <td class="py-3 px-4 text-center font-bold text-indigo-600 text-sm">{{ $row['total'] }}</td>
                            <td class="py-3 px-4 text-center text-blue-600 font-semibold">{{ $row['male'] }}</td>
                            <td class="py-3 px-4 text-center text-rose-600 font-semibold">{{ $row['female'] }}</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-semibold">{{ $row['tetap'] }}</td>
                            <td class="py-3 px-4 text-center text-amber-600 font-semibold">{{ $row['kontrak'] }}</td>
                            <td class="py-3 px-4 text-center text-slate-500 font-semibold">{{ $row['lainnya'] }}</td>
                            <td class="py-3 px-4 w-48">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $row['percentage'] }}%"></div>
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-700 min-w-[36px]">{{ $row['percentage'] }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- TAB 4: KEHADIRAN HARI INI -->
    @if($activeTab === 'kehadiran')
    <div class="space-y-4">
        <!-- Search bar for attendance -->
        <div class="rounded-2xl border border-slate-200/70 bg-white p-4 shadow-2xs print:hidden flex items-center justify-between">
            <div class="relative w-72">
                <x-tabler-search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Cari karyawan di jadwal hari ini..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-9 pr-3 py-2 text-xs font-medium text-slate-800 focus:bg-white focus:border-indigo-500 focus:outline-hidden transition-all" />
            </div>

            <div class="text-xs font-bold text-slate-500">
                Tanggal: <span class="text-slate-800 font-extrabold">{{ date('d F Y') }}</span>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200/70 bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">Nama Karyawan</th>
                            <th class="py-3.5 px-4">Shift</th>
                            <th class="py-3.5 px-4">Jam Jadwal</th>
                            <th class="py-3.5 px-4">Jam Masuk / Keluar</th>
                            <th class="py-3.5 px-4 text-center">Status Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($kehadiranList as $row)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $row->jadwalKerja?->karyawan?->full_nama ?? '-' }}
                                <p class="text-[11px] text-slate-400 font-normal">
                                    NIP: {{ $row->jadwalKerja?->karyawan?->nip ?? '-' }}
                                </p>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-700">
                                {{ $row->shift?->nama ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-600">
                                {{ $row->shift?->jam_masuk ?? '-' }} - {{ $row->shift?->jam_keluar ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px]">
                                <span class="{{ $row->jam_masuk ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                                    In: {{ $row->jam_masuk ?? '--:--' }}
                                </span> 
                                · 
                                <span class="{{ $row->jam_keluar ? 'text-blue-700 font-bold' : 'text-slate-400' }}">
                                    Out: {{ $row->jam_keluar ?? '--:--' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @php
                                    $stBadge = match($row->status_kehadiran) {
                                        'hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'terlambat' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'pulang_cepat' => 'bg-orange-50 text-orange-700 border-orange-200',
                                        'tidak_hadir' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'cuti', 'izin' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200'
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-[10px] font-bold border uppercase tracking-wider {{ $stBadge }}">
                                    {{ str_replace('_', ' ', $row->status_kehadiran ?? 'Belum Absen') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <x-tabler-clock-off class="mx-auto h-8 w-8 text-slate-300 mb-2" />
                                <p class="font-semibold text-slate-600">Belum ada data jadwal/kehadiran untuk hari ini</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($kehadiranList && $kehadiranList->hasPages())
            <div class="border-t border-slate-200/70 px-4 py-3 bg-slate-50/50 print:hidden">
                {{ $kehadiranList->links() }}
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
