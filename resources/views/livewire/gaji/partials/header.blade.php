<!-- Header Page -->
<div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-2xs space-y-4">
    <!-- Top Row: Icon + Title/Breadcrumb & Primary Navigation Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0 flex-1">
            <div class="rounded-xl bg-indigo-50 p-2.5 text-indigo-600 shrink-0">
                <x-tabler-wallet class="h-6 w-6" />
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition-colors whitespace-nowrap">Kepegawaian</a>
                    <x-tabler-chevron-right class="h-3.5 w-3.5 text-slate-300 shrink-0" />
                    <a href="{{ route('kepegawaian.gaji.index') }}" class="hover:text-indigo-600 transition-colors whitespace-nowrap">Rekap Bulanan</a>
                    <x-tabler-chevron-right class="h-3.5 w-3.5 text-slate-300 shrink-0" />
                    <span class="text-indigo-600 font-bold whitespace-nowrap">Detail Slip</span>
                </div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800 tracking-tight mt-0.5 truncate">
                    Periode Slip: <span class="text-indigo-600 font-black">{{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</span>
                </h1>
            </div>
        </div>

        <!-- Primary Navigation & Export Buttons -->
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <x-ts:button wire:click="exportToExcel" flat color="emerald" class="text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/80 whitespace-nowrap px-3.5 py-2.5 rounded-xl">
                <x-tabler-file-spreadsheet class="h-4 w-4 mr-1.5 shrink-0" />
                Export Rekap
            </x-ts:button>

            <x-ts:button href="{{ route('kepegawaian.gaji.index') }}" flat color="slate" class="text-xs font-bold bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200/80 whitespace-nowrap px-3.5 py-2.5 rounded-xl">
                <x-tabler-arrow-left class="h-4 w-4 mr-1.5 shrink-0" />
                Kembali ke Rekap
            </x-ts:button>
        </div>
    </div>

    <!-- Toolbar Sub-actions (Second Row) -->
    <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-slate-100/80">
        @if($isLocked)
            <x-ts:button wire:click="openAutoSendModal" flat color="purple" class="text-xs font-bold bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200/80 whitespace-nowrap px-3 py-1.5 rounded-lg">
                <x-tabler-clock class="h-3.5 w-3.5 mr-1.5 shrink-0" />
                Jadwal Email Otomatis
            </x-ts:button>

            <x-ts:button wire:click="openBatchSendModal" flat color="sky" class="text-xs font-bold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200/80 whitespace-nowrap px-3 py-1.5 rounded-lg">
                <x-tabler-mail-fast class="h-3.5 w-3.5 mr-1.5 shrink-0" />
                Kirim Massal Email
            </x-ts:button>
        @endif

        <x-ts:button wire:click="downloadTemplate" flat color="emerald" class="text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/80 whitespace-nowrap px-3 py-1.5 rounded-lg">
            <x-tabler-file-download class="h-3.5 w-3.5 mr-1.5 shrink-0" />
            Templat Excel
        </x-ts:button>

        @if(!$isLocked)
            <x-ts:button wire:click="generateBulkDraftSlips" 
                        wire:confirm="Sistem akan menyalin data gaji bulan lalu (atau kalkulasi otomatis) untuk seluruh karyawan periode ini. Lanjutkan?"
                        wire:loading.attr="disabled"
                        flat color="indigo" 
                        class="text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-700 whitespace-nowrap px-3 py-1.5 rounded-lg shadow-2xs">
                <x-tabler-copy-check class="h-3.5 w-3.5 mr-1.5 shrink-0" wire:loading.remove wire:target="generateBulkDraftSlips" />
                <x-tabler-loader-2 class="h-3.5 w-3.5 mr-1.5 shrink-0 animate-spin" wire:loading wire:target="generateBulkDraftSlips" />
                Salin / Selesaikan Input
            </x-ts:button>

            <x-ts:button wire:click="openImportModal" flat color="sky" class="text-xs font-bold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200/80 whitespace-nowrap px-3 py-1.5 rounded-lg">
                <x-tabler-file-upload class="h-3.5 w-3.5 mr-1.5 shrink-0" />
                Impor Excel
            </x-ts:button>
        @endif

        <x-ts:button wire:click="openPeriodLogModal" flat color="indigo" class="text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/80 whitespace-nowrap px-3 py-1.5 rounded-lg">
            <x-tabler-history class="h-3.5 w-3.5 mr-1.5 shrink-0" />
            Log Edit
        </x-ts:button>
    </div>
</div>
