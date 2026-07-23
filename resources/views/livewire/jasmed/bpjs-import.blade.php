<div class="space-y-4">
    <div class="flex items-center gap-2 text-base font-extrabold text-indigo-700 pb-2 border-b border-indigo-100">
        <x-ts:icon name="tabler.database-import" class="h-5 w-5 text-indigo-600" />
        Impor & Integrasi Data Klaim
    </div>

    {{-- Step 1 --}}
    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4 space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-[10px] font-extrabold text-white">1</span>
                Import Data Pasien INA-CBGs
            </span>
            <button wire:click='downloadTemplate("txt")' class="text-xs font-semibold text-indigo-600 hover:underline flex items-center gap-1">
                <x-ts:icon name="tabler.file-download" class="h-3.5 w-3.5" />
                Template TXT
                <x-spinner target='downloadTemplate("txt")' xs />
            </button>
        </div>
        <p class="text-[11px] text-slate-500">File ekspor data pasien format .txt dari aplikasi INA-CBGs.</p>
        <x-ts:upload wire:model='excelPasien'>
            <x-slot:footer when-uploaded>
                <x-ts:button wire:click="importPasien" loading="importPasien" icon="tabler.database-import" class="w-full mt-2">Upload Data Pasien</x-ts:button>
            </x-slot:footer>
        </x-ts:upload>
    </div>

    {{-- Step 2 --}}
    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4 space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-[10px] font-extrabold text-white">2</span>
                Import Data Disetujui
            </span>
            <button wire:click='downloadTemplate("disetujui")' class="text-xs font-semibold text-indigo-600 hover:underline flex items-center gap-1">
                <x-ts:icon name="tabler.file-download" class="h-3.5 w-3.5" />
                Template Excel
                <x-spinner target='downloadTemplate("disetujui")' xs />
            </button>
        </div>
        <p class="text-[11px] text-slate-500">File klaim yang telah disetujui (Approved Jasmed).</p>
        <x-ts:upload wire:model='excelDisetujui'>
            <x-slot:footer when-uploaded>
                <x-ts:button wire:click="importDisetujui" loading="importDisetujui" icon="tabler.database-import" class="w-full mt-2">Upload Data Disetujui</x-ts:button>
            </x-slot:footer>
        </x-ts:upload>
    </div>

    {{-- Step 3 --}}
    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4 space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-purple-600 text-[10px] font-extrabold text-white">3</span>
                Import Data Dokter (SIMRS)
            </span>
            <button wire:click='downloadTemplate("visit")' class="text-xs font-semibold text-indigo-600 hover:underline flex items-center gap-1">
                <x-ts:icon name="tabler.file-download" class="h-3.5 w-3.5" />
                Template Dokter
                <x-spinner target='downloadTemplate("visit")' xs />
            </button>
        </div>
        <p class="text-[11px] text-slate-500">File riwayat visit dan tindakan dokter dari SIMRS.</p>
        <x-ts:upload wire:model='excelDokter'>
            <x-slot:footer when-uploaded>
                <x-ts:button wire:click="importDokter" loading="importDokter" icon="tabler.database-import" class="w-full mt-2">Upload Data Dokter</x-ts:button>
            </x-slot:footer>
        </x-ts:upload>

        <div class="pt-1">
            <button class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 hover:underline" x-on:click="$dispatch('open-modal',{id:'modalDataDokter'})">
                <x-ts:icon name="tabler.user-search" class="h-4 w-4" />
                Periksa & Validasi Pemetaan Data Dokter
            </button>
        </div>
    </div>

    {{-- Step 4 --}}
    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4 space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-600 text-[10px] font-extrabold text-white">4</span>
                Import Data Rincian INA-CBGs
            </span>
            <button wire:click='downloadTemplate("rincian_inacbg")' class="text-xs font-semibold text-indigo-600 hover:underline flex items-center gap-1">
                <x-ts:icon name="tabler.file-download" class="h-3.5 w-3.5" />
                Template Rincian
                <x-spinner target='downloadTemplate("rincian_inacbg")' xs />
            </button>
        </div>
        <p class="text-[11px] text-slate-500">File rincian komponen klaim INA-CBGs per pasien.</p>
        <x-ts:upload wire:model='excelRincian'>
            <x-slot:footer when-uploaded>
                <x-ts:button wire:click="importRincian" loading="importRincian" icon="tabler.database-import" class="w-full mt-2">Upload Data Rincian</x-ts:button>
            </x-slot:footer>
        </x-ts:upload>
    </div>

    {{-- Modal Check Data Dokter --}}
    <x-filament::modal id="modalDataDokter" width="3/4" class="max-h-screen overflow-auto">
        <x-slot:heading>Pemetaan & Data Dokter</x-slot:heading>
        <livewire:Jasmed.Dokter.Index :$cabar :key="Str::random()" />
    </x-filament::modal>
</div>
