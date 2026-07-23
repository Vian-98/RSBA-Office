<div>
    <!-- Cuti Quota Stats -->
    @if($cutiStats)
        <div class="mb-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Quota Card -->
            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-2xs flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 shrink-0">
                    <x-tabler-calendar class="h-5 w-5" />
                </span>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Jatah Tahunan</span>
                    <div class="text-base font-extrabold text-slate-800 mt-0.5">{{ $cutiStats['quota'] }} Hari</div>
                </div>
            </div>

            <!-- Used Card -->
            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-2xs flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 shrink-0">
                    <x-tabler-calendar-minus class="h-5 w-5" />
                </span>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Terpakai / Diajukan</span>
                    <div class="text-base font-extrabold text-slate-800 mt-0.5">{{ $cutiStats['used'] }} Hari</div>
                </div>
            </div>

            <!-- Sisa Card -->
            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-2xs flex items-center gap-3">
                @if($cutiStats['eligible'])
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 shrink-0">
                        <x-tabler-calendar-check class="h-5 w-5" />
                    </span>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Sisa Cuti Aktif</span>
                        <div class="text-base font-extrabold text-emerald-600 mt-0.5">{{ $cutiStats['sisa'] }} Hari</div>
                    </div>
                @else
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600 shrink-0">
                        <x-tabler-calendar-off class="h-5 w-5" />
                    </span>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Sisa Cuti Aktif</span>
                        <div class="text-sm font-bold text-rose-500 mt-0.5">Masa kerja &lt; 1 Th</div>
                    </div>
                @endif
            </div>

            <!-- Reset Card -->
            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-2xs flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600 shrink-0">
                    <x-tabler-refresh class="h-5 w-5" />
                </span>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Reset Berikutnya</span>
                    <div class="text-sm sm:text-base font-bold text-slate-700 mt-0.5">{{ $cutiStats['next_reset'] }}</div>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col gap-2">
        <div class="ml-auto flex justify-end">
            <x-ts:button sm x-on:click="$dispatch('open-modal',{id:'new-cuti'})" icon="tabler.mail-plus">
                Pengajuan Izin / Cuti
            </x-ts:button>
        </div>
        <div>
            {{ $this->table }}
        </div>
    </div>

    <x-filament::modal id="new-cuti" width="lg" :close-by-clicking-away="false">
        <x-slot name="heading">
            Pengajuan Izin / Cuti
        </x-slot>

        <livewire:Surat.Cuti.Pengajuan :id="$karyawan->id" :key="Str::random()" @created-cuti="$dispatch('close-modal', {id: 'new-cuti'}); $refresh" />
    </x-filament::modal>

    <x-filament::modal id="view-detil-tanggal" :close-by-clicking-away="false">
        <x-slot name="heading">
            Detil Tanggal Cuti
        </x-slot>

        <livewire:Surat.Cuti.DetilTanggalCuti :$surat :key="$surat?->id" />
    </x-filament::modal>

    <x-filament::modal id="modal-status-cuti">
        <livewire:Surat.Cuti.ViewStatus :$surat :key="'view-status-cuti-' . Str::random(3)" />
    </x-filament::modal>

    <div x-data x-on:trigger-print-cuti.window="$nextTick(() => printArea('print-cuti-approved'))">
        <div class="hidden" id="print-cuti-approved">
            @if ($surat)
                <livewire:Surat.Cuti.PrintCuti :suratCuti="$surat" :key="'print-cuti-' . Str::random(5)" />
            @endif
        </div>
    </div>
</div>
