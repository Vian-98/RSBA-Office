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
            <x-ts:button type="button" outline color="indigo" class="text-xs font-bold bg-white border border-indigo-200 text-indigo-600 shadow-sm" x-on:click="$tsui.open.modal('modal-payroll-parameters')">
                <x-tabler-settings class="h-4 w-4 mr-1.5" />
                Parameter Payroll
            </x-ts:button>
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
                <div class="rounded-lg bg-rose-50 p-2 text-rose-600">
                    <x-tabler-receipt-off class="h-5 w-5" />
                </div>
            </div>
            <div class="mt-[5px]">
                <h3 class="text-2xl font-black text-slate-800">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</h3>
                <p class="text-[12px] text-slate-400 mt-2.5 font-medium">Dihitung dari {{ $jumlahKaryawan }} slip gaji terbit bulan ini.</p>
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
                            <div class="mb-2 text-2xs font-extrabold text-slate-800 bg-slate-950 text-white px-2 py-1 rounded-md shadow-lg hidden group-hover:block transition-all duration-200">
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
                            <span class="mt-2.5 text-2xs font-semibold {{ $isActive ? 'text-indigo-600 font-bold' : 'text-slate-400' }}">
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
                                        @if($item['is_approved'])
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-100">
                                                <x-tabler-lock class="h-3 w-3" />
                                                Disetujui
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200">
                                                <x-tabler-lock-open class="h-3 w-3" />
                                                Draf
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center" @click.stop>
                                        <div class="flex items-center justify-center gap-2">
                                            @can('view-kepegawaian-gaji-detail')
                                                <a href="{{ route('kepegawaian.gaji.detail', ['periode' => $item['periode']]) }}" class="inline-flex items-center gap-1 rounded-lg px-3 py-1 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 hover:text-indigo-800 transition-all">
                                                    <x-tabler-calculator class="h-3.5 w-3.5" />
                                                    {{ $item['is_approved'] ? 'Lihat Detail' : 'Kelola Gaji' }}
                                                </a>
                                            @else
                                                <span class="text-xs text-slate-400 font-medium">Buka Detail</span>
                                            @endcan

                                            @can('approve-kepegawaian-gaji')
                                                @if($item['is_approved'])
                                                    @if($item['sp3_status'] === 'approved')
                                                        {{-- SP3 disetujui Direksi: hanya Super Admin yang bisa buka kunci --}}
                                                        @role('Super-Admin')
                                                            <button type="button" wire:click="unlockPeriode('{{ $item['periode'] }}')" class="inline-flex items-center gap-1 rounded-lg px-3 py-1 text-xs font-bold text-orange-600 bg-orange-50 hover:bg-orange-100 hover:text-orange-800 transition-all" title="SP3 sudah disetujui Direksi. Hanya Super Admin yang dapat membuka kunci.">
                                                                <x-tabler-shield-lock class="h-3.5 w-3.5" />
                                                                Force Unlock
                                                            </button>
                                                        @else
                                                            <span class="inline-flex items-center gap-1 rounded-lg px-3 py-1 text-xs font-semibold text-slate-400 bg-slate-50 border border-slate-200 cursor-not-allowed" title="SP3 sudah disetujui Direksi. Hanya Super Admin yang dapat membuka kunci.">
                                                                <x-tabler-lock class="h-3.5 w-3.5" />
                                                                SP3 Final
                                                            </span>
                                                        @endrole
                                                    @else
                                                        {{-- SP3 pending/rejected atau belum ada: SDM bisa buka kunci untuk revisi --}}
                                                        <button type="button" wire:click="unlockPeriode('{{ $item['periode'] }}')" class="inline-flex items-center gap-1 rounded-lg px-3 py-1 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-800 transition-all" title="{{ $item['sp3_status'] === 'rejected' ? 'SP3 ditolak Direksi. Buka kunci untuk merevisi gaji.' : 'Buka kunci periode ini.' }}">
                                                            <x-tabler-lock-open class="h-3.5 w-3.5" />
                                                            {{ $item['sp3_status'] === 'rejected' ? 'Revisi Gaji' : 'Buka Kunci' }}
                                                        </button>
                                                    @endif
                                                @else
                                                    @if($item['karyawan_count'] > 0)
                                                        <button type="button" wire:click="openFinalisasiModal('{{ $item['periode'] }}', {{ $item['karyawan_count'] }}, {{ $item['total_potongan'] }}, {{ $item['total_gaji_bersih'] }})" class="inline-flex items-center gap-1 rounded-lg px-3 py-1 text-xs font-bold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 hover:text-emerald-800 transition-all">
                                                            <x-tabler-lock class="h-3.5 w-3.5" />
                                                            Setujui & Kunci
                                                        </button>
                                                    @endif
                                                @endif
                                            @endcan
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
                            <span class="text-slate-500 text-xs font-semibold">Potongan Telat</span>
                            <span class="font-extrabold text-slate-800 text-sm">Rp {{ number_format($config_potongan_telat, 0, ',', '.') }} / menit</span>
                        </div>
                        <div class="flex justify-between items-center py-3">
                            <span class="text-slate-500 text-xs font-semibold">Toleransi Telat</span>
                            <span class="font-extrabold text-slate-800 text-sm">{{ $config_toleransi_telat }} menit</span>
                        </div>
                        <div class="flex justify-between items-center py-3">
                            <span class="text-slate-500 text-xs font-semibold">Tarif Lembur</span>
                            <span class="font-extrabold text-slate-800 text-sm">Rp {{ number_format($config_tarif_lembur, 0, ',', '.') }} / menit</span>
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
                    <x-ts:input label="UMK Kantor (Rupiah)" wire:model.defer="config_umk" type="number" prefix="Rp" />
                </div>
                <div>
                    <x-ts:input label="Potongan Telat (Rupiah/Menit)" wire:model.defer="config_potongan_telat" type="number" prefix="Rp" />
                </div>
                <div>
                    <x-ts:input label="Tarif Lembur (Rupiah/Menit)" wire:model.defer="config_tarif_lembur" type="number" prefix="Rp" />
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
                <span class="text-2xs text-emerald-700 font-bold uppercase tracking-wider block mb-1">Ringkasan Penggajian Bulanan</span>
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
</div>
