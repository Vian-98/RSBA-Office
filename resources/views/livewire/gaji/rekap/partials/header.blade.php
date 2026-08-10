<!-- Header Page -->
<div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-2xs space-y-4">
    <!-- Top Row: Breadcrumbs & Title + Main Action Buttons -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                <x-tabler-chevron-right class="h-3.5 w-3.5 text-slate-300" />
                <span class="text-indigo-600 font-bold">Rekap Bulanan</span>
            </div>
            <h1 class="text-base sm:text-lg font-extrabold text-slate-800 tracking-tight mt-0.5">Rekap Gaji Bulanan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Analisis pengeluaran gaji, tren bulanan, dan estimasi beban payroll rutin.</p>
        </div>

        <!-- Primary Actions & Month Picker -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <div class="w-44 shrink-0">
                <x-month-picker wire:model.live="periode" />
            </div>
            @can('view-kepegawaian-gaji-detail')
                <x-ts:button href="{{ route('kepegawaian.gaji.detail', ['periode' => $periode]) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-2xs whitespace-nowrap px-4 py-2.5 rounded-xl">
                    <x-tabler-calculator class="h-4 w-4 mr-1.5 shrink-0" />
                    Kelola Gaji Karyawan
                </x-ts:button>
            @endcan
            <x-ts:button href="{{ route('dashboard') }}" flat color="slate" class="text-xs font-bold bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200/80 whitespace-nowrap px-3 py-2.5 rounded-xl">
                <x-tabler-arrow-left class="h-4 w-4 mr-1 shrink-0" />
                Kembali
            </x-ts:button>
        </div>
    </div>

    <!-- Toolbar Sub-actions (Second Row) -->
    <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-slate-100/80">
        @if(!empty($isLocked) && $isLocked)
            <x-ts:button wire:click="openAutoSendModal" flat color="purple" class="text-xs font-bold bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200/80 whitespace-nowrap px-3 py-1.5 rounded-lg">
                <x-tabler-clock class="h-3.5 w-3.5 mr-1.5 shrink-0" />
                Jadwal Email Otomatis
            </x-ts:button>

            <x-ts:button wire:click="openBatchSendModal" flat color="sky" class="text-xs font-bold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200/80 whitespace-nowrap px-3 py-1.5 rounded-lg">
                <x-tabler-mail-fast class="h-3.5 w-3.5 mr-1.5 shrink-0" />
                Kirim Massal Email
            </x-ts:button>
        @endif

        @if(!$isOnlyPajak)
        <x-ts:button type="button" outline color="indigo" class="text-xs font-bold bg-white border border-indigo-200/80 text-indigo-600 hover:bg-indigo-50 whitespace-nowrap px-3 py-1.5 rounded-lg" x-on:click="$tsui.open.modal('modal-payroll-parameters')">
            <x-tabler-settings class="h-3.5 w-3.5 mr-1.5 shrink-0" />
            Parameter Payroll
        </x-ts:button>
        @endif
    </div>
</div>
