<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-100 pb-5">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800">Laporan Statistik Kepegawaian</h1>
            <p class="text-sm text-slate-500 mt-1">Analisis demografi karyawan, status kepegawaian, tingkat pendidikan, dan penyebaran departemen RSBA.</p>
        </div>
    </div>

    <!-- Summary Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1: Total Karyawan -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-2xs transition-all duration-300 hover:shadow-xs hover:-translate-y-0.5">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 transition-colors">
                    <x-tabler-users class="h-6 w-6" />
                </span>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Karyawan</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-0.5">{{ $stats['total_karyawan'] }}</h3>
                </div>
            </div>
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-500"></div>
        </div>

        <!-- Card 2: Pegawai Tetap -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-2xs transition-all duration-300 hover:shadow-xs hover:-translate-y-0.5">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <x-tabler-id-badge class="h-6 w-6" />
                </span>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pegawai Tetap</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-0.5">{{ $stats['status_tetap'] }}</h3>
                </div>
            </div>
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
        </div>

        <!-- Card 3: Pegawai Kontrak -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-2xs transition-all duration-300 hover:shadow-xs hover:-translate-y-0.5">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <x-tabler-file-certificate class="h-6 w-6" />
                </span>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pegawai Kontrak</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-0.5">{{ $stats['status_kontrak'] }}</h3>
                </div>
            </div>
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500"></div>
        </div>

        <!-- Card 4: Sedang Cuti -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-2xs transition-all duration-300 hover:shadow-xs hover:-translate-y-0.5">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                    <x-tabler-calendar-event class="h-6 w-6" />
                </span>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sedang Cuti</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-0.5">{{ $stats['active_cuti'] }}</h3>
                </div>
            </div>
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-red-500"></div>
        </div>
    </div>

    <!-- Visual Breakdown 3-Column Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 items-stretch">
        
        <!-- Column 1: Demografi (Gender & Status) -->
        <div class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-xs flex flex-col justify-between h-full">
            <div>
                <h2 class="text-md font-bold text-slate-800 border-b border-slate-100 pb-3 mb-5 flex items-center gap-2">
                    <x-tabler-users-group class="h-5 w-5 text-indigo-500" />
                    Demografi & Status
                </h2>
                
                @php
                    $totalGen = max(1, $stats['male'] + $stats['female']);
                    $pctMale = round(($stats['male'] / $totalGen) * 100);
                    $pctFemale = round(($stats['female'] / $totalGen) * 100);
                @endphp
                <!-- Gender Distribution -->
                <div class="space-y-4 mb-6">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jenis Kelamin</h3>
                    
                    <!-- Male Bar -->
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-1.5">
                            <span class="flex items-center gap-1.5"><x-tabler-gender-male class="h-4.5 w-4.5 text-blue-500" /> Laki-laki</span>
                            <span>{{ $stats['male'] }} Orang ({{ $pctMale }}%)</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full transition-all duration-500" style="width: {{ $pctMale }}%"></div>
                        </div>
                    </div>

                    <!-- Female Bar -->
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-1.5">
                            <span class="flex items-center gap-1.5"><x-tabler-gender-female class="h-4.5 w-4.5 text-rose-500" /> Perempuan</span>
                            <span>{{ $stats['female'] }} Orang ({{ $pctFemale }}%)</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
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
        <div class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-xs flex flex-col justify-between h-full">
            <div>
                <h2 class="text-md font-bold text-slate-800 border-b border-slate-100 pb-3 mb-5 flex items-center gap-2">
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
        <div class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-xs flex flex-col justify-between h-full">
            <div>
                <h2 class="text-md font-bold text-slate-800 border-b border-slate-100 pb-3 mb-5 flex items-center gap-2">
                    <x-tabler-building class="h-5 w-5 text-indigo-500" />
                    Distribusi Per Bagian
                </h2>
                
                <div class="space-y-4">
                    @php
                        $totalKary = max(1, $stats['total_karyawan']);
                    @endphp
                    @foreach($stats['bagian'] as $bagian)
                        @php
                            $pctBag = round(($bagian->karyawan_count / $totalKary) * 100);
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                                <span>{{ $bagian->nama }}</span>
                                <span class="font-bold text-slate-700">{{ $bagian->karyawan_count }} Karyawan ({{ $pctBag }}%)</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full transition-all duration-500" style="width: {{ $pctBag }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    <!-- Kehadiran Harian (Absensi) -->
    <div class="mt-8">
        <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
            <x-tabler-clock class="h-6 w-6 text-indigo-500" />
            Kehadiran Hari Ini ({{ date('d M Y') }})
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
            <div class="bg-white border rounded-xl p-4 text-center shadow-sm">
                <p class="text-xs text-gray-500 font-semibold mb-1">HADIR</p>
                <p class="text-2xl font-bold text-green-600">{{ $absensiStats['hadir'] }}</p>
            </div>
            <div class="bg-white border rounded-xl p-4 text-center shadow-sm">
                <p class="text-xs text-gray-500 font-semibold mb-1">TERLAMBAT</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $absensiStats['terlambat'] }}</p>
            </div>
            <div class="bg-white border rounded-xl p-4 text-center shadow-sm">
                <p class="text-xs text-gray-500 font-semibold mb-1">PULANG CEPAT</p>
                <p class="text-2xl font-bold text-orange-500">{{ $absensiStats['pulang_cepat'] }}</p>
            </div>
            <div class="bg-white border rounded-xl p-4 text-center shadow-sm">
                <p class="text-xs text-gray-500 font-semibold mb-1">TIDAK HADIR</p>
                <p class="text-2xl font-bold text-red-600">{{ $absensiStats['tidak_hadir'] }}</p>
            </div>
            <div class="bg-white border rounded-xl p-4 text-center shadow-sm">
                <p class="text-xs text-gray-500 font-semibold mb-1">CUTI</p>
                <p class="text-2xl font-bold text-blue-500">{{ $absensiStats['cuti'] }}</p>
            </div>
            <div class="bg-white border rounded-xl p-4 text-center shadow-sm">
                <p class="text-xs text-gray-500 font-semibold mb-1">IZIN</p>
                <p class="text-2xl font-bold text-indigo-500">{{ $absensiStats['izin'] }}</p>
            </div>
            <div class="bg-white border rounded-xl p-4 text-center shadow-sm">
                <p class="text-xs text-gray-500 font-semibold mb-1">PERLU VERIFIKASI</p>
                <p class="text-2xl font-bold text-gray-600">{{ $absensiStats['perlu_verifikasi'] }}</p>
            </div>
        </div>
    </div>
</div>
