<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                <x-tabler-chevron-right class="h-3 w-3" />
                <span class="text-slate-500">Rekap Bulanan</span>
            </div>
            <p class="text-xs text-slate-500">Analisis pengeluaran gaji, tren bulanan, dan estimasi beban payroll rutin.</p>
        </div>
        <div class="flex items-center gap-3">
            <x-ts:button href="{{ route('dashboard') }}" flat color="slate" class="text-xs font-bold bg-white border border-slate-200">
                <x-tabler-arrow-left class="h-4 w-4 mr-1.5" />
                Kembali
            </x-ts:button>
            @if(!$isOnlyPajak)
            <x-ts:button type="button" outline color="indigo" class="text-xs font-bold bg-white border border-indigo-200 text-indigo-600 shadow-sm" x-on:click="$tsui.open.modal('modal-payroll-parameters')">
                <x-tabler-settings class="h-4 w-4 mr-1.5" />
                Parameter Payroll
            </x-ts:button>
            @endif
            @can('view-kepegawaian-gaji-detail')
                <x-ts:button href="{{ route('kepegawaian.gaji.detail', ['periode' => $periode]) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm">
                    <x-tabler-calculator class="h-4 w-4 mr-1.5" />
                    Kelola Gaji Karyawan
                </x-ts:button>
            @endcan
            <div class="w-48">
                <x-month-picker wire:model.live="periode" />
            </div>
        </div>
    </div>

    <!-- Quick Stats Section -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Card: Gaji Bersih -->
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-2xs relative overflow-hidden transition-all duration-300 hover:shadow-xs">
            <div class="absolute right-0 top-0 -mr-6 -mt-6 h-24 w-24 rounded-full bg-indigo-50/40 blur-xl"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Gaji Bersih</span>
                <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
                    <x-tabler-cash class="h-5 w-5" />
                </div>
            </div>
            <div class="mt-[5px]">
                <h3 class="text-2xl font-black text-slate-800">Rp {{ number_format($totalGajiBersih, 0, ',', '.') }}</h3>
                <div class="mt-2.5 flex items-center gap-1.5">
                    @if($percentChange > 0)
                        <span class="inline-flex items-center gap-0.5 rounded-md bg-emerald-50 px-1.5 py-0.5 text-[12px] font-bold text-emerald-700 border border-emerald-100/60">
                            <x-tabler-trending-up class="h-3 w-3" />
                            +{{ number_format($percentChange, 1) }}%
                        </span>
                    @elseif($percentChange < 0)
                        <span class="inline-flex items-center gap-0.5 rounded-md bg-rose-50 px-1.5 py-0.5 text-[12px] font-bold text-rose-700 border border-rose-100/60">
                            <x-tabler-trending-down class="h-3 w-3" />
                            {{ number_format($percentChange, 1) }}%
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-md bg-slate-50 px-1.5 py-0.5 text-[12px] font-bold text-slate-500 border border-slate-100/60">
                            Stagnan
                        </span>
                    @endif
                    <span class="text-[12px] text-slate-400 font-medium">vs bulan lalu (Rp {{ number_format($lastMonthNet, 0, ',', '.') }})</span>
                </div>
            </div>
        </div>

        <!-- Card: Total Potongan -->
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-2xs relative overflow-hidden transition-all duration-300 hover:shadow-xs">
            <div class="absolute right-0 top-0 -mr-6 -mt-6 h-24 w-24 rounded-full bg-rose-50/30 blur-xl"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Potongan</span>
                <div class="flex items-center gap-1.5">
                    <button type="button" x-on:click="$tsui.open.modal('modal-potongan-breakdown')" class="text-rose-600 bg-rose-50 hover:bg-rose-100 px-2 py-1 rounded-md text-[11px] font-bold border border-rose-100 transition-colors flex items-center gap-1">
                        <x-tabler-list-details class="h-3.5 w-3.5" />
                        Rincian
                    </button>
                    <div class="rounded-lg bg-rose-50 p-2 text-rose-600">
                        <x-tabler-receipt-off class="h-5 w-5" />
                    </div>
                </div>
            </div>
            <div class="mt-[5px]">
                <h3 class="text-2xl font-black text-slate-800">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</h3>
                <div class="mt-2.5 flex items-center justify-between text-[12px] text-slate-400 font-medium">
                    <span>Dihitung dari {{ $jumlahKaryawan }} slip gaji bulan ini.</span>
                    <button type="button" x-on:click="$tsui.open.modal('modal-potongan-breakdown')" class="text-rose-600 font-bold hover:underline">
                        Lihat 8 Kategori &rarr;
                    </button>
                </div>
            </div>
        </div>

        <!-- Card: Estimasi Bulan Depan -->
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-2xs relative overflow-hidden transition-all duration-300 hover:shadow-xs">
            <div class="absolute right-0 top-0 -mr-6 -mt-6 h-24 w-24 rounded-full bg-amber-50/40 blur-xl"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Estimasi Beban Rutin Bulan Depan</span>
                <div class="rounded-lg bg-amber-50 p-2 text-amber-600">
                    <x-tabler-calculator class="h-5 w-5" />
                </div>
            </div>
            <div class="mt-[5px]">
                <h3 class="text-2xl font-black text-slate-800">Rp {{ number_format($estimasiBulanDepan, 0, ',', '.') }}</h3>
                <div class="mt-2.5 flex items-center gap-1.5 text-[12px] text-slate-400 font-medium">
                    <span class="inline-flex items-center gap-0.5 rounded-md bg-amber-50 px-1.5 py-0.5 text-[12px] font-bold text-amber-700 border border-amber-100/60">
                        Info
                    </span>
                    <span>Lembur & THR dikecualikan karena fluktuatif.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area: Chart & Table stacked on Left, Breakdown & Parameter Aktif stacked on Right -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:items-stretch">
        <!-- Left: 6-Month Trend & Table -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            <!-- Interactive Trend Chart -->
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-2xs">
                <h2 class="text-md font-bold text-slate-800 mb-1">Grafik Tren Pengeluaran Gaji</h2>
                <p class="text-xs text-slate-400 mb-6">Klik pada grafik batang untuk mengganti periode analisis.</p>

                @php
                    $maxNet = collect($trendMonths)->max('total_gaji_bersih') ?: 1;
                @endphp

                <div class="h-64 flex items-end justify-between gap-4 pt-6 border-b border-slate-100 pb-2">
                    @foreach($trendMonths as $item)
                        @php
                            $heightPercent = ($item['total_gaji_bersih'] / $maxNet) * 100;
                            $isActive = $item['periode'] === $this->periode;
                        @endphp
                        <div class="flex flex-col items-center flex-1 h-full justify-end group">
                            <!-- Tooltip / Label value -->
                            <div class="mb-2 text-[10px] font-extrabold text-slate-800 bg-slate-950 text-white px-2 py-1 rounded-md shadow-lg hidden group-hover:block transition-all duration-200">
                                Rp {{ number_format($item['total_gaji_bersih'], 0, ',', '.') }}
                            </div>
                            
                            <!-- Bar -->
                            <div 
                                wire:click="setPeriode('{{ $item['periode'] }}')"
                                style="height: calc({{ $heightPercent }}% - 20px)" 
                                class="w-full rounded-t-lg transition-all duration-300 cursor-pointer shadow-3xs hover:opacity-90 
                                       {{ $isActive ? 'bg-gradient-to-t from-indigo-600 to-indigo-500 ring-2 ring-indigo-200 ring-offset-2' : 'bg-slate-200 hover:bg-slate-300' }}">
                            </div>

                            <!-- Label -->
                            <span class="mt-2.5 text-[10px] font-semibold {{ $isActive ? 'text-indigo-600 font-bold' : 'text-slate-400' }}">
                                {{ \Carbon\Carbon::parse($item['periode'] . '-01')->translatedFormat('M y') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Trend Table -->
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xs flex-1 flex flex-col">
                <div class="px-6 py-4 border-b border-slate-50">
                    <h2 class="text-md font-bold text-slate-800">Tabel Riwayat Penggajian</h2>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="w-full border-collapse text-left text-sm text-slate-600">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase text-slate-400">
                                <th class="px-4 py-4 whitespace-nowrap">Periode</th>
                                <th class="px-4 py-4 text-center whitespace-nowrap">Karyawan</th>
                                <th class="px-4 py-4 whitespace-nowrap">Total Potongan</th>
                                <th class="px-4 py-4 whitespace-nowrap">Total Gaji Bersih</th>
                                <th class="px-4 py-4 text-center whitespace-nowrap">Status</th>
                                <th class="px-4 py-4 text-center whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($trendMonths as $item)
                                @php
                                    $isActive = $item['periode'] === $this->periode;
                                @endphp
                                <tr 
                                    wire:click="setPeriode('{{ $item['periode'] }}')"
                                    class="transition-colors cursor-pointer hover:bg-indigo-50/30 {{ $isActive ? 'bg-indigo-50/40 font-semibold' : '' }}">
                                    <td class="px-4 py-4 text-slate-800 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            @if($isActive)
                                                <span class="h-2 w-2 rounded-full bg-indigo-600 animate-pulse flex-shrink-0"></span>
                                            @endif
                                            {{ $item['label'] }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center text-slate-600 whitespace-nowrap">
                                        {{ $item['karyawan_count'] }} Karyawan
                                    </td>
                                    <td class="px-4 py-4 font-medium text-slate-600 whitespace-nowrap">
                                        Rp {{ number_format($item['total_potongan'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 font-bold whitespace-nowrap {{ $isActive ? 'text-indigo-600' : 'text-slate-800' }}">
                                        Rp {{ number_format($item['total_gaji_bersih'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        @if($item['status'] === 'approved')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-100">
                                                <x-tabler-lock class="h-3 w-3" />
                                                Disetujui
                                            </span>
                                        @elseif($item['status'] === 'review_pajak')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 border border-amber-100">
                                                <x-tabler-eye-check class="h-3 w-3" />
                                                Review Pajak
                                            </span>
                                        @elseif($item['status'] === 'review_sdm')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 border border-blue-100">
                                                <x-tabler-clipboard-check class="h-3 w-3" />
                                                Review SDM
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200">
                                                <x-tabler-lock-open class="h-3 w-3" />
                                                Draf
                                            </span>
                                        @endif
                                    <td class="px-4 py-4 text-center whitespace-nowrap" @click.stop>
                                        <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                                            @can('view-kepegawaian-gaji-detail')
                                                <a href="{{ route('kepegawaian.gaji.detail', ['periode' => $item['periode']]) }}" class="inline-flex items-center gap-1 rounded-lg px-3 py-1 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 hover:text-indigo-800 transition-all whitespace-nowrap">
                                                    <x-tabler-calculator class="h-3.5 w-3.5" />
                                                    {{ $item['status'] === 'approved' ? 'Lihat Detail' : 'Kelola Gaji' }}
                                                </a>
                                            @else
                                                <span class="text-xs text-slate-400 font-medium">Buka Detail</span>
                                            @endcan

                                            {{-- SDM Actions --}}
                                            @if($isSDM)
                                                @if($item['status'] === 'draft' && $item['karyawan_count'] > 0)
                                                    {{-- SDM: Kirim ke Pajak --}}
                                                    <button type="button" wire:click="submitToReviewPajak('{{ $item['periode'] }}')" wire:confirm="Kirim data gaji periode ini ke Tim Pajak untuk direview?" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-amber-600 bg-amber-50 hover:bg-amber-100 hover:text-amber-800 transition-all whitespace-nowrap" title="Kirim ke Pajak">
                                                        <x-tabler-send class="h-4 w-4" />
                                                    </button>
                                                @elseif($item['status'] === 'review_pajak')
                                                    {{-- Waiting for Pajak --}}
                                                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-amber-500 bg-amber-50/50 border border-amber-100 cursor-default whitespace-nowrap" title="Menunggu review dari Tim Pajak">
                                                        <x-tabler-clock class="h-4 w-4" />
                                                    </span>
                                                @elseif($item['status'] === 'review_sdm')
                                                    {{-- SDM: Finalisasi & SP3 --}}
                                                    <button type="button" wire:click="openFinalisasiModal('{{ $item['periode'] }}', {{ $item['karyawan_count'] }}, {{ $item['total_potongan'] }}, {{ $item['total_gaji_bersih'] }})" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-100 hover:text-emerald-800 transition-all whitespace-nowrap" title="Setujui & Kunci">
                                                        <x-tabler-lock class="h-4 w-4" />
                                                    </button>
                                                @elseif($item['status'] === 'approved')
                                                    @if($item['sp3_status'] === 'approved')
                                                        @role('Super-Admin')
                                                            <button type="button" wire:click="unlockPeriode('{{ $item['periode'] }}')" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-orange-600 bg-orange-50 hover:bg-orange-100 hover:text-orange-800 transition-all whitespace-nowrap" title="Force Unlock (SP3 disetujui Direksi)">
                                                                <x-tabler-shield-lock class="h-4 w-4" />
                                                            </button>
                                                        @else
                                                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-slate-400 bg-slate-50 border border-slate-200 cursor-not-allowed whitespace-nowrap" title="SP3 sudah disetujui Direksi">
                                                                <x-tabler-lock class="h-4 w-4" />
                                                            </span>
                                                        @endrole
                                                    @else
                                                        <button type="button" wire:click="unlockPeriode('{{ $item['periode'] }}')" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-800 transition-all whitespace-nowrap" title="{{ $item['sp3_status'] === 'rejected' ? 'SP3 ditolak Direksi. Buka kunci untuk merevisi.' : 'Buka kunci periode ini.' }}">
                                                            <x-tabler-lock-open class="h-4 w-4" />
                                                        </button>
                                                    @endif
                                                @endif
                                            @endif

                                            {{-- Pajak Actions --}}
                                            @if($isOnlyPajak && $item['status'] === 'review_pajak')
                                                <button type="button" wire:click="approveByPajak('{{ $item['periode'] }}')" wire:confirm="Setujui data pajak untuk periode ini dan kembalikan ke SDM?" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-100 hover:text-emerald-800 transition-all whitespace-nowrap" title="Setujui Pajak">
                                                    <x-tabler-check class="h-4 w-4" />
                                                </button>
                                                <button type="button" wire:click="rejectByPajak('{{ $item['periode'] }}')" wire:confirm="Tolak dan kembalikan ke SDM untuk diperbaiki?" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-800 transition-all whitespace-nowrap" title="Tolak Pajak (Kembalikan ke SDM)">
                                                    <x-tabler-x class="h-4 w-4" />
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Breakdown & Parameter Aktif -->
        <div class="flex flex-col gap-6">
            <!-- Distribusi Gaji per Bagian -->
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-2xs h-fit space-y-6">
                <div>
                    <h2 class="text-md font-bold text-slate-800">Distribusi Gaji per Bagian</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Analisis porsi pengeluaran payroll bulan {{ \Carbon\Carbon::parse($this->periode . '-01')->translatedFormat('F Y') }}</p>
                </div>

                @if(empty($bagianBreakdown))
                    <div class="py-12 text-center text-slate-400">
                        <x-tabler-database-x class="mx-auto h-10 w-10 text-slate-300 mb-3" />
                        <p class="text-xs">Tidak ada data penggajian untuk periode ini.</p>
                    </div>
                @else
                    @php
                        $maxBagianSum = collect($bagianBreakdown)->max('total_gaji_bersih') ?: 1;
                    @endphp

                    <div class="space-y-5">
                        @foreach($bagianBreakdown as $bagName => $data)
                            @php
                                        $widthPercent = ($data['total_gaji_bersih'] / $maxBagianSum) * 100;
                            @endphp
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <div>
                                        <span class="font-bold text-slate-700">{{ $bagName }}</span>
                                        <span class="text-slate-400 ml-1">({{ $data['karyawan_count'] }} staf)</span>
                                    </div>
                                    <span class="font-extrabold text-slate-800">Rp {{ number_format($data['total_gaji_bersih'], 0, ',', '.') }}</span>
                                </div>
                                <!-- Bar gauge -->
                                <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div style="width: {{ $widthPercent }}%" class="h-full bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-full"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Parameter Aktif Card -->
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-2xs space-y-4 flex-1">
                <div class="flex items-center justify-between border-b border-slate-50 pb-3">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Parameter Aktif</h2>
                        <p class="text-[10px] text-slate-400 mt-0.5">Konfigurasi payroll saat ini</p>
                    </div>
                    <button type="button" x-on:click="$tsui.open.modal('modal-payroll-parameters')" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold flex items-center gap-1">
                        <x-tabler-edit class="h-3.5 w-3.5" />
                        Edit
                    </button>
                </div>

                <div class="flex-1 flex flex-col justify-between space-y-5">
                    <!-- Config rows -->
                    <div class="divide-y divide-slate-100/70">
                        <div class="flex justify-between items-center py-3">
                            <span class="text-slate-500 text-xs font-semibold">UMK Kantor (Base)</span>
                            <span class="font-extrabold text-slate-800 text-sm">Rp {{ number_format($config_umk, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-3">
                            <span class="text-slate-500 text-xs font-semibold">Potongan Telat (Per Kejadian)</span>
                            <span class="font-extrabold text-slate-800 text-sm">Rp {{ number_format($config_potongan_telat, 0, ',', '.') }} / kejadian</span>
                        </div>
                        <div class="flex justify-between items-center py-3">
                            <span class="text-slate-500 text-xs font-semibold">Toleransi Telat</span>
                            <span class="font-extrabold text-slate-800 text-sm">{{ $config_toleransi_telat }} menit</span>
                        </div>
                    </div>

                    <!-- Allocations summary -->
                    <div class="border-t border-slate-100 pt-4">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2.5">Porsi Tunjangan (25% UMK)</span>
                        <div class="divide-y divide-slate-100/70">
                            @foreach($allocations as $alloc)
                                <div class="flex justify-between items-center py-2.5 text-xs">
                                    <span class="text-slate-500 font-semibold flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full {{ $alloc['is_absensi'] ? 'bg-amber-500' : 'bg-indigo-500' }}"></span>
                                        {{ $alloc['nama'] }}
                                    </span>
                                    <span class="font-extrabold text-slate-800 text-sm">{{ $alloc['persen'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Insight Bulanan -->
                    <div class="border-t border-slate-100 pt-4 mt-auto">
                        <div class="rounded-xl bg-indigo-600 p-5 text-white shadow-xs space-y-2.5">
                            <div class="flex items-center gap-2 text-[10px] font-bold tracking-wider uppercase opacity-90">
                                <x-tabler-bulb class="h-4.5 w-4.5 shrink-0 text-amber-300 animate-pulse" />
                                <span>Insight Bulanan</span>
                            </div>
                            <p class="text-xs leading-relaxed opacity-95 font-medium">
                                {{ $insightText }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Parameter Payroll Modal -->
    <x-ts:modal id="modal-payroll-parameters" title="Parameter & Alokasi Payroll" size="2xl" class="relative z-50">
        <form wire:submit.prevent="saveParameters" class="space-y-5 p-2">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <x-ts:input label="UMK Kantor (Rupiah)" wire:model.defer="config_umk" type="text" prefix="Rp" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let inp = $el.querySelector('input') || $el; inp.value = (inp.value || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" />
                </div>
                <div>
                    <x-ts:input label="Potongan Telat (Rupiah/Kejadian)" wire:model.defer="config_potongan_telat" type="text" prefix="Rp" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let inp = $el.querySelector('input') || $el; inp.value = (inp.value || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" />
                </div>
                <div>
                    <x-ts:input label="Toleransi Telat (Menit)" wire:model.defer="config_toleransi_telat" type="number" suffix="Min" />
                </div>
            </div>

            <!-- Alokasi Tunjangan (25% UMK) -->
            <div class="border-t border-slate-100 pt-4 space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Alokasi Tunjangan (25% UMK)</span>
                    <x-ts:button type="button" size="xs" color="indigo" outline wire:click="addAllocation" class="text-[10px] font-bold py-1 px-2.5">
                        + Tambah
                    </x-ts:button>
                </div>
                
                <div class="space-y-4 max-h-[300px] overflow-y-auto pr-1">
                    @foreach($allocations as $index => $alloc)
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl space-y-2 relative">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Kategori #{{ $index + 1 }}</span>
                                <button type="button" wire:click="removeAllocation({{ $index }})" class="text-red-500 hover:text-red-700">
                                    <x-tabler-trash class="h-4 w-4" />
                                </button>
                            </div>
                            <div class="grid grid-cols-1 gap-2">
                                <div>
                                    <x-ts:input label="Nama Tunjangan" wire:model.defer="allocations.{{ $index }}.nama" placeholder="Tunjangan Tetap / Absensi" />
                                </div>
                                <div class="flex gap-2 items-center">
                                    <div class="flex-1">
                                        <x-ts:input label="Porsi (%)" wire:model.defer="allocations.{{ $index }}.persen" type="number" min="0" max="100" suffix="%" />
                                    </div>
                                    <div class="pt-5 pl-2">
                                        <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                            <input type="checkbox" wire:model.defer="allocations.{{ $index }}.is_absensi" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4" />
                                            <span class="text-xs font-semibold text-slate-600">Absensi?</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="p-3 bg-indigo-50/50 rounded-lg text-[10px] text-slate-500 border border-indigo-100/50 leading-relaxed">
                    <span class="font-bold text-indigo-700 block mb-0.5">Catatan:</span>
                    Total persentase dari seluruh alokasi yang ditambahkan harus tepat <strong>100%</strong> agar alokasi tunjangan bernilai pas 25% dari UMK.
                </div>
            </div>

            <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100">
                <x-ts:button type="button" flat color="slate" x-on:click="$tsui.close.modal('modal-payroll-parameters')">Batal</x-ts:button>
                <x-ts:button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm">
                    Simpan Parameter
                </x-ts:button>
            </div>
        </form>
    </x-ts:modal>

    <!-- Modal Finalisasi & SP3 -->
    <x-ts:modal wire="isFinalisasiModalOpen" size="md" class="relative z-50">
        <x-slot:title>
            <span class="flex items-center gap-1.5 font-bold text-slate-800">
                <x-tabler-lock class="h-5 w-5 text-emerald-500" />
                Finalisasi & Kirim ke SP3
            </span>
        </x-slot:title>

        <form wire:submit.prevent="submitFinalisasi" class="space-y-4 p-2">
            <!-- Summary Information -->
            <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-4 space-y-2 text-xs">
                <span class="text-[10px] text-emerald-700 font-bold uppercase tracking-wider block mb-1">Ringkasan Penggajian Bulanan</span>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Periode</span>
                    <span class="text-slate-800 font-bold">
                        @if($finalisasiPeriode)
                            {{ \Carbon\Carbon::parse($finalisasiPeriode . '-01')->translatedFormat('F Y') }}
                        @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Total Karyawan</span>
                    <span class="text-slate-800 font-bold">{{ $finalisasiKaryawanCount }} Karyawan</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Total Potongan</span>
                    <span class="text-slate-800 font-bold">Rp {{ number_format($finalisasiTotalPotongan, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t border-emerald-100/50 pt-2 mt-1">
                    <span class="text-emerald-700 font-bold">Total Gaji Bersih</span>
                    <span class="text-emerald-800 font-black text-sm">Rp {{ number_format($finalisasiTotalGajiBersih, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="p-3 bg-amber-50 rounded-xl text-[10px] text-amber-700 border border-amber-100 leading-relaxed">
                <span class="font-bold block mb-0.5">Penting:</span>
                Tindakan ini akan mengunci payroll periode tersebut dari segala bentuk pengeditan dan secara otomatis menerbitkan dokumen pencairan dana (SP3).
            </div>

            <!-- SP3 Setup Form Fields -->
            <div class="space-y-3.5 pt-2">
                <div>
                    <x-ts:input label="Tanggal SP3" wire:model.defer="formSp3Tgl" type="date" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Metode Pembayaran</label>
                    <select wire:model.defer="formSp3Bayar" class="w-full rounded-lg border-gray-300 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="trf">Transfer Bank</option>
                        <option value="tunai">Tunai</option>
                        <option value="giro">Giro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Pejabat Menyetujui (Approval SP3)</label>
                    <select wire:model.defer="formSp3JabatanId" class="w-full rounded-lg border-gray-300 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih Pejabat --</option>
                        @foreach($mengetahuiOptions as $opt)
                            <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100">
                <x-ts:button type="button" flat color="slate" wire:click="closeFinalisasiModal">Batal</x-ts:button>
                <x-ts:button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm">
                    Finalisasi & Kirim ke SP3
                </x-ts:button>
            </div>
        </form>
    </x-ts:modal>

    <!-- Modal Rincian Total Potongan -->
    <x-ts:modal id="modal-potongan-breakdown" title="Rincian Total Potongan Payroll" size="lg" class="relative z-50">
        <div class="space-y-4">
            <div class="p-4 bg-rose-50/60 border border-rose-100 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-600 block">Total Seluruh Potongan</span>
                    <h2 class="text-2xl font-black text-rose-950 mt-0.5">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</h2>
                    <span class="text-xs text-rose-700 font-medium">Periode: {{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }} ({{ $jumlahKaryawan }} Karyawan)</span>
                </div>
                <div class="p-3 bg-rose-500 text-white rounded-xl shadow-sm">
                    <x-tabler-receipt-off class="h-7 w-7" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                @php
                    $items = [
                        ['label' => 'BPJS Kesehatan', 'key' => 'bpjs_kes', 'icon' => 'tabler-heart-rate-monitor', 'color' => 'bg-emerald-500', 'textColor' => 'text-emerald-700'],
                        ['label' => 'BPJS Ketenagakerjaan', 'key' => 'bpjs_tk', 'icon' => 'tabler-shield-check', 'color' => 'bg-blue-500', 'textColor' => 'text-blue-700'],
                        ['label' => 'PPh 21 (Pajak)', 'key' => 'pph21', 'icon' => 'tabler-receipt-tax', 'color' => 'bg-amber-500', 'textColor' => 'text-amber-700'],
                        ['label' => 'Potongan Absensi / Telat', 'key' => 'absensi', 'icon' => 'tabler-clock-off', 'color' => 'bg-rose-500', 'textColor' => 'text-rose-700'],
                        ['label' => 'Potongan Cash Bon', 'key' => 'cash_bon', 'icon' => 'tabler-wallet-off', 'color' => 'bg-purple-500', 'textColor' => 'text-purple-700'],
                        ['label' => 'Potongan Obat / Rawat', 'key' => 'obat', 'icon' => 'tabler-pill', 'color' => 'bg-cyan-500', 'textColor' => 'text-cyan-700'],
                        ['label' => 'Potongan Bank', 'key' => 'bank', 'icon' => 'tabler-building-bank', 'color' => 'bg-indigo-500', 'textColor' => 'text-indigo-700'],
                        ['label' => 'Potongan Lain-Lain', 'key' => 'lain', 'icon' => 'tabler-dots', 'color' => 'bg-slate-500', 'textColor' => 'text-slate-700'],
                    ];
                @endphp

                @foreach($items as $item)
                    @php
                        $val = $potonganBreakdown[$item['key']] ?? 0;
                        $pct = $totalPotongan > 0 ? ($val / $totalPotongan) * 100 : 0;
                    @endphp
                    <div class="p-3.5 bg-white border border-slate-100 rounded-xl shadow-2xs flex flex-col justify-between space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ $item['color'] }}"></span>
                                {{ $item['label'] }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-sm">
                                {{ number_format($pct, 1) }}%
                            </span>
                        </div>
                        <div class="flex items-baseline justify-between pt-1">
                            <span class="text-base font-extrabold text-slate-800">Rp {{ number_format($val, 0, ',', '.') }}</span>
                        </div>
                        <!-- Progress bar -->
                        <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                            <div class="{{ $item['color'] }} h-full rounded-full" style="width: {{ min(100, max(0, $pct)) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <x-slot:footer>
            <div class="flex justify-end">
                <x-ts:button size="sm" flat color="slate" x-on:click="$tsui.close.modal('modal-potongan-breakdown')">Tutup</x-ts:button>
            </div>
        </x-slot:footer>
    </x-ts:modal>
</div>
