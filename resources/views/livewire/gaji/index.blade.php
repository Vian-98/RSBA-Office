<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center bg-white p-4 rounded-2xl border border-slate-100 shadow-2xs">
        <div class="flex items-center gap-3">
            <div class="rounded-xl bg-indigo-50 p-2 text-indigo-600">
                <x-tabler-wallet class="h-5 w-5" />
            </div>
            <div>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                    <span>Kepegawaian</span>
                    <x-tabler-chevron-right class="h-3.5 w-3.5 text-slate-300" />
                    <span>Rekap Bulanan</span>
                    <x-tabler-chevron-right class="h-3.5 w-3.5 text-slate-300" />
                    <span class="text-indigo-600 font-bold">Detail Slip</span>
                </div>
                <h1 class="text-base font-bold text-slate-800 mt-0.5">
                    Periode Slip: <span class="text-indigo-600 font-black">{{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</span>
                </h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <x-ts:button href="{{ route('kepegawaian.gaji.index') }}" flat color="slate" class="text-xs font-bold bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200">
                <x-tabler-arrow-left class="h-4 w-4 mr-1.5" />
                Kembali ke Rekap
            </x-ts:button>
        </div>
    </div>

    @if($isLocked)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 flex items-center gap-3">
            <div class="rounded-lg bg-emerald-500/10 p-2 text-emerald-700">
                <x-tabler-lock class="h-5 w-5" />
            </div>
            <div class="text-xs font-medium">
                <span class="font-bold block text-emerald-900 mb-0.5">Periode Terkunci & Disetujui</span>
                Seluruh data slip gaji pada periode <b>{{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</b> telah disetujui oleh manajemen dan terkunci. Data tidak dapat diedit atau ditambah.
            </div>
        </div>
    @endif

    <!-- Filters Panel -->
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-2xs">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-ts:input wire:model.live.debounce.300ms="search" placeholder="Cari nama karyawan..." icon="tabler.search" class="w-full" />
            </div>
            <div>
                <select wire:model.live="bagianFilter" class="w-full rounded-lg border-gray-300 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Bagian</option>
                    @foreach($bagians as $bag)
                        <option value="{{ $bag->id }}">{{ $bag->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-month-picker wire:model.live="periode" />
            </div>
            <div>
                <select wire:model.live="perPage" class="w-full rounded-lg border-gray-300 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="10">Tampilkan 10 data</option>
                    <option value="25">Tampilkan 25 data</option>
                    <option value="50">Tampilkan 50 data</option>
                    <option value="100">Tampilkan 100 data</option>
                    <option value="-1">Tampilkan Semua data</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Salaries Table -->
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm text-slate-600">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase text-slate-400">
                        <th class="px-6 py-4">Nama & NIP</th>
                        <th class="px-6 py-4">Bagian / Jabatan</th>
                        <th class="px-6 py-4">Status Kerja</th>
                        <th class="px-6 py-4">Status Input</th>
                        <th class="px-6 py-4">Gaji Pokok</th>
                        <th class="px-6 py-4">Tunjangan</th>
                        <th class="px-6 py-4">Gaji Bersih</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($karyawans as $karyawan)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">{{ $karyawan->full_nama }}</div>
                                <div class="text-xs text-slate-400">NIP: {{ $karyawan->nip }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-700">{{ $karyawan->calculated_salary['bagian_nama'] }}</div>
                                <div class="text-xs text-slate-500">{{ $karyawan->calculated_salary['jabatan_nama'] }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $karyawan->status->nama() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($karyawan->payroll_status === 'generated')
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-100">
                                        Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200">
                                        Belum Input
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-700">
                                Rp {{ number_format($karyawan->calculated_salary['gaji_pokok'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-700">
                                Rp {{ number_format($karyawan->calculated_salary['tunjangan'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 font-bold text-indigo-600">
                                Rp {{ number_format($karyawan->calculated_salary['gaji_bersih'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($isLocked)
                                        @if($karyawan->payroll_status === 'generated')
                                            <x-ts:button flat color="indigo" class="text-xs font-bold" wire:click="viewSlip({{ $karyawan->id }})">
                                                <x-tabler-file-text class="h-4 w-4" />
                                                Slip
                                            </x-ts:button>
                                        @else
                                            <span class="text-xs font-semibold text-slate-400 flex items-center gap-1">
                                                <x-tabler-lock class="h-3.5 w-3.5" />
                                                Terkunci
                                            </span>
                                        @endif
                                    @else
                                        @if($karyawan->payroll_status === 'generated')
                                            <x-ts:button flat color="indigo" class="text-xs font-bold" wire:click="viewSlip({{ $karyawan->id }})">
                                                <x-tabler-file-text class="h-4 w-4" />
                                                Slip
                                            </x-ts:button>
                                            <x-ts:button flat color="amber" class="text-xs font-bold" wire:click="openInputModal({{ $karyawan->id }})">
                                                <x-tabler-edit class="h-4 w-4" />
                                                Edit
                                            </x-ts:button>
                                        @else
                                            <x-ts:button flat color="emerald" class="text-xs font-bold" wire:click="openInputModal({{ $karyawan->id }})">
                                                <x-tabler-plus class="h-4 w-4" />
                                                Input Gaji
                                            </x-ts:button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <x-tabler-database-x class="mx-auto h-12 w-12 text-slate-300 mb-3" />
                                <div class="text-sm font-semibold">Tidak Ada Karyawan Ditemukan</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($karyawans->hasPages())
            <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                {{ $karyawans->links() }}
            </div>
        @endif
    </div>

    <!-- Payroll Input Modal -->
    <x-ts:modal wire="isInputModalOpen" size="4xl" class="relative z-50">
        <x-slot:title>
            <span class="flex items-center gap-1.5 font-bold text-slate-800">
                <x-tabler-calculator class="h-5 w-5 text-indigo-500" />
                Input Data Gaji - Periode {{ \Carbon\Carbon::parse($this->periode . '-01')->translatedFormat('F Y') }}
            </span>
        </x-slot:title>

        @if($selectedKaryawan)
            <div class="p-2 space-y-5">
                <!-- Employee Summary Header -->
                <div class="bg-indigo-50/50 border border-indigo-100/75 rounded-2xl p-4 flex flex-col md:flex-row justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-xs text-indigo-600 font-bold uppercase tracking-wider">Identitas Karyawan</span>
                        <h3 class="font-bold text-slate-800 text-lg leading-tight">{{ $selectedKaryawan->full_nama }}</h3>
                        <p class="text-xs text-slate-500">NIP: {{ $selectedKaryawan->nip }} | Status: {{ $selectedKaryawan->status->nama() }}</p>
                    </div>
                    <div class="md:text-right space-y-1 text-slate-600 text-xs">
                        <span class="text-xs text-indigo-600 font-bold uppercase tracking-wider block">Kualifikasi</span>
                        <p>Jabatan: <b class="text-slate-700">{{ $selectedKaryawan->jabatan->first()?->nama ?? '-' }}</b></p>
                        <p>Masa Kerja: <b class="text-slate-700">{{ $selectedKaryawan->masakerja }}</b></p>
                    </div>
                </div>

                @if($carriedOverFromPeriode)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 shadow-2xs flex items-start gap-3">
                        <x-tabler-alert-circle class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" />
                        <div>
                            <span class="font-bold">Info Salin Data:</span> Data pada form ini otomatis disalin dari slip gaji periode <span class="font-bold">{{ \Carbon\Carbon::parse($carriedOverFromPeriode . '-01')->translatedFormat('F Y') }}</span>. Silakan periksa dan sesuaikan sebelum disimpan.
                        </div>
                    </div>
                @endif

                <form wire:submit.prevent="savePayroll" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Panel: Pendapatan -->
                        <div class="space-y-4">
                            <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider block pb-1 border-b border-indigo-150">Komponen Pendapatan (+)</span>
                            
                            <div class="space-y-3">
                                <!-- Row 1: Gaji Pokok & Tunjangan Tetap -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Gaji Pokok (Base)</span>
                                        </div>
                                        <x-ts:input wire:model.defer="form_gaji_pokok" type="number" prefix="Rp" disabled class="bg-slate-100 cursor-not-allowed font-semibold text-slate-600 text-xs" />
                                    </div>
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Tunjangan Tetap</span>
                                        </div>
                                        <x-ts:input wire:model.defer="form_tunjangan_tetap" type="number" prefix="Rp" disabled class="bg-slate-100 cursor-not-allowed font-semibold text-slate-600 text-xs" />
                                    </div>
                                </div>

                                <!-- Row 2: Tunjangan Absensi & Tunjangan Jabatan -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Tunjangan Absensi</span>
                                        </div>
                                        <x-ts:input wire:model.defer="form_tunjangan_absensi" type="number" prefix="Rp" disabled class="bg-slate-100 cursor-not-allowed font-semibold text-slate-600 text-xs" />
                                    </div>
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Tunjangan Jabatan</span>
                                        </div>
                                        <x-ts:input wire:model.defer="form_tunjangan_jabatan" type="number" prefix="Rp" disabled class="bg-slate-100 cursor-not-allowed font-semibold text-slate-600 text-xs" />
                                    </div>
                                </div>

                                <!-- Row 3: Tunjangan Shift & Tunjangan Radiologi -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Tunjangan Shift</span>
                                        </div>
                                        <x-ts:input wire:model.live.debounce.500ms="form_tunjangan_shift" type="number" prefix="Rp" class="text-xs" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
                                    </div>
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Tunjangan Radiologi</span>
                                        </div>
                                        <x-ts:input wire:model.live.debounce.500ms="form_tunjangan_radiologi" type="number" prefix="Rp" class="text-xs" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
                                    </div>
                                </div>

                                <!-- Row 4: Tunjangan Lain & Uang Lembur -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Tunjangan Lain (Total)</span>
                                        </div>
                                        <x-ts:input wire:model.defer="form_tunjangan_lain" type="number" prefix="Rp" disabled class="bg-slate-100 cursor-not-allowed font-semibold text-slate-600 text-xs" />
                                    </div>
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Uang Lembur</span>
                                            @if($calculatedOvertimeMinutes > 0)
                                                <span class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-sm shrink-0 whitespace-nowrap">{{ $calculatedOvertimeMinutes }} mnt lembur</span>
                                            @endif
                                        </div>
                                        <x-ts:input wire:model.live.debounce.500ms="form_uang_lembur" type="number" prefix="Rp" class="text-xs" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
                                    </div>
                                </div>

                                <!-- Row 5: THR & Placeholder -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Tunjangan Hari Raya</span>
                                        </div>
                                        <x-ts:input wire:model.live.debounce.500ms="form_tunjangan_hari_raya" type="number" prefix="Rp" class="text-xs" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
                                    </div>
                                    <div></div>
                                </div>

                                <!-- Tunjangan Lain-Lain Dinamis Section -->
                                <div class="border-t border-slate-100 pt-4 mt-2">
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">Tunjangan Lain-Lain Dinamis</span>
                                    
                                    <div class="flex flex-col sm:flex-row gap-3 items-end mb-4 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                        <div class="flex-1 w-full">
                                            <span class="block text-xs font-semibold text-slate-500 mb-1">Pilih Jenis Tunjangan</span>
                                            <select wire:model.defer="temp_allowance_type_id" class="w-full rounded-lg border-gray-300 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">-- Pilih Jenis --</option>
                                                @foreach($allowanceTypes as $type)
                                                    <option value="{{ $type->id }}">{{ $type->nama }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="w-full sm:w-40">
                                            <x-ts:input label="Nominal" wire:model.defer="temp_allowance_nominal" type="number" prefix="Rp" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
                                        </div>
                                        <div>
                                            <x-ts:button type="button" wire:click="addTunjanganLain" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm w-full sm:w-auto">
                                                Tambah
                                            </x-ts:button>
                                        </div>
                                    </div>

                                    <!-- Table / List of dynamic items -->
                                    <div class="overflow-hidden rounded-xl border border-slate-100 bg-white">
                                        <table class="w-full border-collapse text-left text-xs text-slate-600">
                                            <thead>
                                                <tr class="bg-slate-50 font-semibold text-slate-400 border-b border-slate-100">
                                                    <th class="px-4 py-2">Nama Tunjangan</th>
                                                    <th class="px-4 py-2 text-right">Nominal</th>
                                                    <th class="px-4 py-2 text-center w-16">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-50">
                                                @forelse($form_tunjangan_lain_items as $index => $item)
                                                    <tr>
                                                        <td class="px-4 py-2 font-bold text-slate-700">{{ $item['nama'] }}</td>
                                                        <td class="px-4 py-2 text-right font-semibold text-slate-800">Rp {{ number_format($item['nominal'], 0, ',', '.') }}</td>
                                                        <td class="px-4 py-2 text-center">
                                                            <button type="button" wire:click="removeTunjanganLain({{ $index }})" class="text-red-500 hover:text-red-700">
                                                                <x-tabler-trash class="h-4 w-4 mx-auto" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="px-4 py-3 text-center text-slate-400 italic">Belum ada tunjangan lain-lain tambahan.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel: Potongan -->
                        <div class="space-y-4">
                            <span class="text-xs font-bold text-rose-650 uppercase tracking-wider block pb-1 border-b border-rose-150">Komponen Potongan & Pajak (-)</span>
                            
                            <div class="space-y-3">
                                <!-- Row 1: Absensi & Cash Bon -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Absensi</span>
                                            @if($calculatedLateMinutes > 0)
                                                <span class="text-[9px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-sm shrink-0 whitespace-nowrap">{{ $calculatedLateMinutes }} mnt telat</span>
                                            @endif
                                        </div>
                                        <x-ts:input wire:model.live.debounce.500ms="form_potongan_absensi" type="number" prefix="Rp" class="text-xs" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
                                    </div>
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Cash Bon</span>
                                        </div>
                                        <x-ts:input wire:model.live.debounce.500ms="form_potongan_cash_bon" type="number" prefix="Rp" class="text-xs" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
                                    </div>
                                </div>
                                
                                <!-- Row 2: Obat & Lain-Lain -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Obat / Rawat</span>
                                        </div>
                                        <x-ts:input wire:model.live.debounce.500ms="form_potongan_obat" type="number" prefix="Rp" class="text-xs" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
                                    </div>
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Lain-Lain</span>
                                        </div>
                                        <x-ts:input wire:model.live.debounce.500ms="form_potongan_lain" type="number" prefix="Rp" class="text-xs" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
                                    </div>
                                </div>
                                
                                <!-- Row 3: BPJS Kesehatan & BPJS Ketenagakerjaan (Manual Input) -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">BPJS Kesehatan</span>
                                        </div>
                                        <x-ts:input wire:model.live.debounce.500ms="form_potongan_bpjs_kes" type="number" prefix="Rp" class="text-xs" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
                                    </div>
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">BPJS Ketenagakerjaan</span>
                                        </div>
                                        <x-ts:input wire:model.live.debounce.500ms="form_potongan_bpjs_tk" type="number" prefix="Rp" class="text-xs" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
                                    </div>
                                </div>
                                
                                <!-- Row 4: Bank & Keluarga Add-on -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Potongan Bank</span>
                                        </div>
                                        <x-ts:input wire:model.live.debounce.500ms="form_potongan_bank" type="number" prefix="Rp" class="text-xs" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
                                    </div>
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Keluarga Add-on BPJS</span>
                                            <span class="text-[9px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-sm shrink-0 whitespace-nowrap">+1% / kepala</span>
                                        </div>
                                        <x-ts:input wire:model.live.debounce.500ms="form_bpjs_keluarga_tambahan" type="number" min="0" class="text-xs" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
                                    </div>
                                </div>

                                <!-- Row 5: PPh Pasal 21 & Override Controls -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-slate-100 pt-4 mt-2">
                                    <div>
                                        <div class="flex justify-between items-end h-8 mb-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Pajak PPh Pasal 21</span>
                                                @if($form_pph21_is_overridden)
                                                    <span class="text-[9px] font-bold text-amber-600 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded-sm shrink-0 whitespace-nowrap">Manual (Override)</span>
                                                @else
                                                    <span class="text-[9px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded-sm shrink-0 whitespace-nowrap">Auto (Sistem)</span>
                                                @endif
                                            </div>
                                            @if($form_pph21_is_overridden && !$isLocked)
                                                <button type="button" wire:click="resetPph21ToAuto" class="text-[9px] font-bold text-indigo-650 hover:underline">Reset ke Auto</button>
                                            @endif
                                        </div>
                                        <x-ts:input wire:model.live.debounce.500ms="form_potongan_pph21" type="number" prefix="Rp" class="text-xs" :disabled="$isLocked" x-on:input="$event.target.value = $event.target.value.replace(/^0+(?=\d)/, '')" />
                                    </div>
                                    @if($form_pph21_is_overridden)
                                        <div>
                                            <div class="flex justify-between items-end h-8 mb-1">
                                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-450 leading-tight">Alasan Perubahan Pajak <span class="text-red-500">*</span></span>
                                            </div>
                                            <x-ts:input wire:model="form_pph21_override_reason" type="text" :disabled="$isLocked" placeholder="Wajib diisi, cth: Pajak Natura / Koreksi PTKP" class="text-xs" />
                                        </div>
                                    @else
                                        <div class="flex flex-col justify-center text-[9px] font-semibold text-slate-400 mt-6 leading-normal">
                                            <span>* Pajak bulanan dihitung berdasarkan status PTKP & UMK dengan tarif TER PMK 168/2023.</span>
                                            <span>* Ubah angka di samping untuk meng-override secara manual.</span>
                                        </div>
                                    @endif

                                    @if($form_pph21_is_overridden && $form_pph21_calculated > 0 && abs((double)$form_potongan_pph21 - (double)$form_pph21_calculated) / (double)$form_pph21_calculated > 0.2)
                                        <div class="col-span-1 sm:col-span-2 mt-2 bg-amber-50 border border-amber-100 rounded-xl p-3 flex gap-2 items-start text-amber-700 text-xs">
                                            <x-tabler-alert-triangle class="h-4.5 w-4.5 text-amber-500 shrink-0 mt-0.5" />
                                            <div>
                                                <span class="font-bold block">Peringatan: Perubahan Signifikan</span>
                                                <span>Nilai PPh 21 manual yang Anda masukkan (Rp {{ number_format((double)$form_potongan_pph21, 0, ',', '.') }}) berbeda lebih dari 20% dibandingkan hasil hitung otomatis sistem (Rp {{ number_format((double)$form_pph21_calculated, 0, ',', '.') }}). Pastikan alasan yang dimasukkan sudah benar.</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary & Previews Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-100 pt-4 mt-2">
                        <!-- Rincian Alokasi UMK (25%) Preview -->
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 text-xs font-semibold text-slate-650">
                            <span class="text-2xs text-indigo-650 font-bold uppercase tracking-wider block mb-2">Rincian Alokasi UMK (25% UMK)</span>
                            @foreach($form_umk_allocations as $alloc)
                                <div class="flex justify-between py-0.5">
                                    <span>{{ $alloc['nama'] }} ({{ $alloc['persen'] }}%)</span>
                                    <span class="text-slate-800 font-bold">Rp {{ number_format($alloc['nominal'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Automatic Deductions Preview -->
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 space-y-2 text-xs font-semibold text-slate-655">
                            <span class="text-2xs text-slate-450 font-bold uppercase tracking-wider block mb-2">Estimasi Potongan Otomatis (Auto)</span>
                            <div class="flex justify-between">
                                <span>Pot. BPJS Kesehatan (1% + Add-on)</span>
                                <span class="text-slate-800 font-bold">Rp {{ number_format($calc_bpjs_kes, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Pot. BPJS Ketenagakerjaan (3%)</span>
                                <span class="text-slate-800 font-bold">Rp {{ number_format($calc_bpjs_tk, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>PPh Pasal 21 (Pajak 5%)</span>
                                <span class="text-slate-800 font-bold">Rp {{ number_format($calc_pph21, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Live Totals Footer -->
                    <div class="border-t border-slate-100 pt-4 mt-6 flex flex-col sm:flex-row justify-between items-center gap-4 bg-slate-50/50 p-4 rounded-2xl">
                        <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-sm text-slate-500 font-medium w-full sm:w-auto">
                            <div>Total Gaji Kotor:</div>
                            <div class="font-bold text-slate-700 text-right">Rp {{ number_format($calc_total_gaji, 0, ',', '.') }}</div>
                            <div>Total Potongan:</div>
                            <div class="font-bold text-slate-700 text-right">Rp {{ number_format($calc_total_potongan + $calc_pph21 + $form_potongan_bank, 0, ',', '.') }}</div>
                        </div>
                        <div class="text-center sm:text-right w-full sm:w-auto">
                            <span class="text-xs text-indigo-600 font-bold uppercase tracking-wider block">Gaji Bersih (Penghasilan Netto)</span>
                            <span class="text-2xl font-black text-indigo-600 leading-tight">Rp {{ number_format($calc_gaji_bersih, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <x-ts:button type="button" flat color="slate" wire:click="closeInputModal">{{ $isLocked ? 'Tutup' : 'Batal' }}</x-ts:button>
                        @if(!$isLocked)
                            <x-ts:button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm px-6">
                                Simpan Data Gaji
                            </x-ts:button>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-lg px-4 py-2 text-xs font-bold text-slate-500 bg-slate-100 border border-slate-200">
                                <x-tabler-lock class="h-3.5 w-3.5" />
                                Terbaca Saja
                            </span>
                        @endif
                    </div>
                </form>
            </div>
        @endif
    </x-ts:modal>

    <!-- Salary Slip Modal (Detailed Print Layout) -->
    <x-ts:modal wire="isOpenModal" size="3xl" class="relative z-50">
        <x-slot:title>
            <span class="flex items-center gap-1.5 font-bold text-slate-800">
                <x-tabler-file-invoice class="h-5 w-5 text-indigo-500" />
                Slip Gaji Karyawan
            </span>
        </x-slot:title>

        @if($selectedSlip)
            <!-- Printable Area -->
            <div id="salary-slip-print" class="p-6 bg-white text-slate-800 text-sm select-none">
                <!-- Header -->
                <div class="flex flex-col items-center justify-center pb-4 mb-4 border-b-2 border-slate-900">
                    <x-logo class="h-12 w-auto mb-1" style="height: 48px; width: auto;" />
                    <h2 class="text-base font-black tracking-widest text-slate-800 uppercase leading-none">RS BINTANG AMIN</h2>
                </div>

                <!-- Info Block -->
                <table class="w-full text-xs font-semibold mb-4 border-collapse">
                    <tbody>
                        <tr class="border-t border-b border-slate-800">
                            <td class="w-20 py-1.5 font-bold">Nama</td>
                            <td class="w-4 py-1.5">:</td>
                            <td class="py-1.5 font-bold">{{ $selectedSlip['nama'] }}</td>
                        </tr>
                        <tr class="border-b border-slate-800">
                            <td class="py-1.5 font-bold">NIP</td>
                            <td class="py-1.5">:</td>
                            <td class="py-1.5 font-bold">{{ $selectedSlip['nip'] }}</td>
                        </tr>
                        <tr class="border-b border-slate-800">
                            <td class="py-1.5 font-bold">Jabatan</td>
                            <td class="py-1.5">:</td>
                            <td class="py-1.5 font-bold">{{ $selectedSlip['jabatan'] }}</td>
                        </tr>
                        <tr class="border-b-4 border-double border-slate-800">
                            <td class="py-1.5 font-bold">Bulan</td>
                            <td class="py-1.5">:</td>
                            <td class="py-1.5 font-bold">{{ $selectedSlip['periode'] }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Main Grid Table -->
                <table class="w-full text-xs border-collapse border border-slate-800">
                    <tbody>
                        <!-- Gaji Pokok -->
                        <tr class="border-b border-slate-800">
                            <td class="w-1/2 border-r border-slate-800 px-3 py-1.5 font-medium">Gaji Pokok</td>
                            <td class="w-[15%] border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="w-[10%] border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="w-[25%] px-3 py-1.5 text-right font-medium">{{ number_format($selectedSlip['gaji_pokok'], 0, ',', '.') }}</td>
                        </tr>
                        <!-- Tj. Tetap -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">
                                Tj. Tetap
                                @if(!empty($selectedSlip['allocations_list']))
                                    <div class="text-[10px] text-slate-500 font-normal mt-0.5 pl-3">
                                        @foreach($selectedSlip['allocations_list'] as $alloc)
                                            @if(!$alloc->is_absensi)
                                                • {{ $alloc->nama }}: Rp {{ number_format($alloc->nominal, 0, ',', '.') }}<br>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['tunjangan_tetap'] > 0 ? number_format($selectedSlip['tunjangan_tetap'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        <!-- Tj. Kehadiran -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">
                                Tj. Kehadiran
                                @if(!empty($selectedSlip['allocations_list']))
                                    <div class="text-[10px] text-slate-500 font-normal mt-0.5 pl-3">
                                        @foreach($selectedSlip['allocations_list'] as $alloc)
                                            @if($alloc->is_absensi)
                                                • {{ $alloc->nama }}: Rp {{ number_format($alloc->nominal, 0, ',', '.') }}<br>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['tunjangan_absensi'] > 0 ? number_format($selectedSlip['tunjangan_absensi'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        <!-- Tj. Lain - Lain -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">
                                Tj. Lain – Lain
                                @if(!empty($selectedSlip['tunjangan_lain_items']))
                                    <div class="text-[10px] text-slate-500 font-normal mt-0.5 pl-3">
                                        @foreach($selectedSlip['tunjangan_lain_items'] as $item)
                                            • {{ $item->nama }}: Rp {{ number_format($item->nominal, 0, ',', '.') }}<br>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['tunjangan_lain'] > 0 ? number_format($selectedSlip['tunjangan_lain'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        <!-- Tj. Jabatan -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Tj. Jabatan</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['tunjangan_jabatan'] > 0 ? number_format($selectedSlip['tunjangan_jabatan'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        <!-- Tj. Shift -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Tj. Shift</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['tunjangan_shift'] > 0 ? number_format($selectedSlip['tunjangan_shift'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        <!-- Tj. Radiasi -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Tj. Radiasi</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['tunjangan_radiologi'] > 0 ? number_format($selectedSlip['tunjangan_radiologi'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        <!-- Uang Lembur -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Uang Lembur</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['uang_lembur'] > 0 ? number_format($selectedSlip['uang_lembur'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        <!-- Tj. Hari Raya -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Tj. Hari Raya</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['tunjangan_hari_raya'] > 0 ? number_format($selectedSlip['tunjangan_hari_raya'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        
                        <!-- TOTAL GAJI -->
                        <tr class="border-b-4 border-double border-slate-800 font-bold bg-slate-50/50">
                            <td class="border-r border-slate-800 px-3 py-1.5 uppercase text-slate-800">TOTAL GAJI</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp.</td>
                            <td class="px-3 py-1.5 text-right text-slate-800">{{ number_format($selectedSlip['total_gaji'], 0, ',', '.') }}</td>
                        </tr>

                        <!-- Spacer row -->
                        <tr class="border-b border-slate-800 h-4 bg-slate-50/20">
                            <td class="border-r border-slate-800 px-3 py-1"></td>
                            <td class="border-r border-slate-800 px-3 py-1"></td>
                            <td class="border-r border-slate-800 px-3 py-1 text-center">Rp</td>
                            <td class="px-3 py-1 text-right"></td>
                        </tr>

                        <!-- Pot. Absensi -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Pot. Absensi</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['potongan_absensi'] > 0 ? number_format($selectedSlip['potongan_absensi'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        <!-- Cash Bon -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Cash Bon</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['potongan_cash_bon'] > 0 ? number_format($selectedSlip['potongan_cash_bon'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        <!-- Pot. Obat / Perawatan -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Pot. Obat / Perawatan</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['potongan_obat'] > 0 ? number_format($selectedSlip['potongan_obat'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        <!-- Pot. BPJS Kesehatan -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Pot. BPJS Kesehatan</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['bpjs_kes'] > 0 ? number_format($selectedSlip['bpjs_kes'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        <!-- Pot. BPJS TK -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Pot. BPJS TK</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['bpjs_ket'] > 0 ? number_format($selectedSlip['bpjs_ket'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        <!-- Potongan Lain-lain -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Potongan Lain-lain</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['potongan_lain'] > 0 ? number_format($selectedSlip['potongan_lain'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>

                        <!-- TOTAL POTONGAN -->
                        <tr class="border-b-4 border-double border-slate-800 font-bold bg-slate-50/50">
                            <td class="border-r border-slate-800 px-3 py-1.5 uppercase text-slate-800">TOTAL POTONGAN</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp.</td>
                            <td class="px-3 py-1.5 text-right text-slate-800">{{ number_format($selectedSlip['total_potongan'], 0, ',', '.') }}</td>
                        </tr>

                        <!-- Spacer row -->
                        <tr class="border-b border-slate-800 h-4 bg-slate-50/20">
                            <td class="border-r border-slate-800 px-3 py-1"></td>
                            <td class="border-r border-slate-800 px-3 py-1"></td>
                            <td class="border-r border-slate-800 px-3 py-1 text-center">Rp</td>
                            <td class="px-3 py-1 text-right"></td>
                        </tr>

                        <!-- PPh Pasal 21 -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">PPh Pasal 21</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['pajak'] > 0 ? number_format($selectedSlip['pajak'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        <!-- Pot. Bank -->
                        <tr class="border-b border-slate-800">
                            <td class="border-r border-slate-800 px-3 py-1.5 font-medium">Pot. Bank</td>
                            <td class="border-r border-slate-800 px-3 py-1.5"></td>
                            <td class="border-r border-slate-800 px-3 py-1.5 text-center">Rp</td>
                            <td class="px-3 py-1.5 text-right font-medium">
                                {{ $selectedSlip['potongan_bank'] > 0 ? number_format($selectedSlip['potongan_bank'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>

                        <!-- PENGHASILAN NETTO -->
                        <tr class="font-bold bg-indigo-50/60">
                            <td class="border-l border-r border-slate-800 px-3 py-2 uppercase text-indigo-800">PENGHASILAN NETTO</td>
                            <td class="border-r border-slate-800 px-3 py-2"></td>
                            <td class="border-r border-slate-800 px-3 py-2 text-center text-indigo-800">Rp.</td>
                            <td class="border-r border-slate-800 px-3 py-2 text-right text-indigo-850 text-sm">{{ number_format($selectedSlip['gaji_bersih'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Bottom Signatures -->
                <div class="mt-6 flex flex-col text-xs text-slate-600 pl-6">
                    <p class="font-medium">Bandar Lampung, {{ now()->translatedFormat('d F Y') }}</p>
                    <p class="font-medium">Wadir SDM & Umum</p>
                    <div class="h-16"></div>
                    <p class="font-bold text-slate-800 leading-none">Riyanti, SP., M.Kes</p>
                </div>
            </div>

            <x-slot:footer>
                <div class="flex justify-end gap-2.5">
                    <x-ts:button size="sm" flat color="slate" wire:click="closeModal">Tutup</x-ts:button>
                    <x-ts:button size="sm" color="sky" class="font-bold text-white bg-sky-600 hover:bg-sky-700" wire:click="sendEmail({{ $selectedSlip['id'] }})" loading="sendEmail">
                        <x-tabler-mail class="mr-1.5 h-4 w-4" />
                        Kirim ke Email
                    </x-ts:button>
                    <x-ts:button size="sm" color="indigo" class="font-bold" onclick="printSalarySlip()">
                        <x-tabler-printer class="mr-1.5 h-4 w-4" />
                        Cetak Slip Gaji
                    </x-ts:button>
                </div>
            </x-slot:footer>
        @endif
    </x-ts:modal>

    <!-- Custom Print Script -->
    <script>
        function printSalarySlip() {
            var printContents = document.getElementById('salary-slip-print').innerHTML;

            var printWindow = window.open('', '', 'height=700,width=850');
            printWindow.document.write('<html><head><title>Cetak Slip Gaji</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: sans-serif; font-size: 13px; color: #1e293b; line-height: 1.4; padding: 20px; margin: 0; }');
            printWindow.document.write('.flex { display: flex; }');
            printWindow.document.write('.flex-col { flex-direction: column; }');
            printWindow.document.write('.items-center { align-items: center; }');
            printWindow.document.write('.justify-center { justify-content: center; }');
            printWindow.document.write('.pb-4 { padding-bottom: 12px; }');
            printWindow.document.write('.mb-4 { margin-bottom: 16px; }');
            printWindow.document.write('.border-b-2 { border-bottom: 2px solid #0f172a; }');
            printWindow.document.write('.border-slate-900 { border-color: #0f172a; }');
            printWindow.document.write('.text-base { font-size: 14px; }');
            printWindow.document.write('.font-black { font-weight: 800; }');
            printWindow.document.write('.tracking-widest { letter-spacing: 0.1em; }');
            printWindow.document.write('.uppercase { text-transform: uppercase; }');
            printWindow.document.write('.leading-none { line-height: 1; }');
            printWindow.document.write('.w-full { width: 100%; }');
            printWindow.document.write('.text-xs { font-size: 11px; }');
            printWindow.document.write('.font-semibold { font-weight: 600; }');
            printWindow.document.write('.border-collapse { border-collapse: collapse; }');
            printWindow.document.write('.border-t { border-top: 1px solid #cbd5e1; }');
            printWindow.document.write('.border-b { border-bottom: 1px solid #cbd5e1; }');
            printWindow.document.write('.border-slate-300 { border-color: #cbd5e1; }');
            printWindow.document.write('.py-1\\.5 { padding-top: 6px; padding-bottom: 6px; }');
            printWindow.document.write('.font-bold { font-weight: bold; }');
            printWindow.document.write('.border { border: 1px solid #1e293b; }');
            printWindow.document.write('.border-slate-800 { border-color: #1e293b; }');
            printWindow.document.write('.border-b-4 { border-bottom-width: 4px; }');
            printWindow.document.write('.border-double { border-bottom-style: double !important; border-color: #1e293b !important; }');
            printWindow.document.write('.border-r { border-right: 1px solid #cbd5e1; }');
            printWindow.document.write('.px-3 { padding-left: 12px; padding-right: 12px; }');
            printWindow.document.write('.font-medium { font-weight: 500; }');
            printWindow.document.write('.text-center { text-align: center; }');
            printWindow.document.write('.text-right { text-align: right; }');
            printWindow.document.write('.bg-slate-50\\/50 { background-color: #f8fafc; }');
            printWindow.document.write('.h-4 { height: 16px; }');
            printWindow.document.write('.bg-slate-50\\/20 { background-color: #f8fafc; }');
            printWindow.document.write('.py-2 { padding-top: 8px; padding-bottom: 8px; }');
            printWindow.document.write('.text-indigo-800 { color: #3730a3; }');
            printWindow.document.write('.text-sm { font-size: 13px; }');
            printWindow.document.write('.mt-6 { margin-top: 24px; }');
            printWindow.document.write('.pl-6 { padding-left: 24px; }');
            printWindow.document.write('.text-slate-600 { color: #475569; }');
            
            printWindow.document.write('@media print {');
            printWindow.document.write('  body { -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 10px; margin: 0; }');
            printWindow.document.write('  table { border-collapse: collapse !important; border: 1px solid #1e293b !important; }');
            printWindow.document.write('  td { border-bottom: 1px solid #cbd5e1 !important; border-right: 1px solid #cbd5e1 !important; }');
            printWindow.document.write('  tr.border-double td { border-bottom: 4px double #1e293b !important; }');
            printWindow.document.write('  tr:last-child td { border-bottom: none !important; }');
            printWindow.document.write('  td:last-child { border-right: none !important; }');
            printWindow.document.write('  .bg-indigo-50\\/60 { background-color: #f0f9ff !important; color: #075985 !important; }');
            printWindow.document.write('  .bg-slate-50\\/50 { background-color: #f8fafc !important; }');
            printWindow.document.write('}');
            printWindow.document.write('<\/style>');
            printWindow.document.write('<\/head><body>');
            printWindow.document.write(printContents);
            printWindow.document.write('<\/body><\/html>');
            printWindow.document.close();

            setTimeout(function() {
                printWindow.print();
                printWindow.close();
            }, 250);
        }
    </script>
</div>
