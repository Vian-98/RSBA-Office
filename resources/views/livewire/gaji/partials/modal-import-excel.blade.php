<!-- Modal Bulk Import Excel Gaji -->
<x-ts:modal wire="isImportModalOpen" title="Impor Gaji Massal (Excel)" size="lg" class="relative z-50">
    <div class="space-y-4">
        <div class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-xs text-indigo-800 space-y-1">
            <p class="font-bold">Panduan Impor Excel:</p>
            <ol class="list-decimal list-inside space-y-0.5 text-indigo-700">
                <li>Unduh <b>Templat Excel</b> terlebih dahulu agar format kolom sesuai.</li>
                <li>Edit nominal Gaji Pokok, Tunjangan, Uang Lembur, Potongan, No. Rekening, dll.</li>
                <li>Unggah kembali file Excel yang telah diedit di bawah ini.</li>
                <li>Sistem akan mencocokkan data berdasarkan NIP karyawan secara otomatis.</li>
            </ol>
        </div>

        <div>
            <x-ts:upload wire:model.live="excelFile" placeholder="Pilih File Excel (.xlsx / .xls)" accept=".xlsx,.xls,.csv" close-after-upload />
        </div>

        <!-- Indicator Upload Progress -->
        <div wire:loading wire:target="excelFile" class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center gap-2 text-xs text-indigo-700 font-medium animate-pulse">
            <x-tabler-loader-2 class="h-4 w-4 animate-spin text-indigo-600 shrink-0" />
            <span>Sedang mengunggah file ke sistem...</span>
        </div>

        <!-- Status File Terunggah -->
        @if($excelFile)
            <div wire:loading.remove wire:target="excelFile" class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs text-emerald-900 font-medium shadow-3xs">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-emerald-500 text-white rounded-lg">
                        <x-tabler-file-spreadsheet class="h-5 w-5" />
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-emerald-600 tracking-wider">File Siap Di-Impor</span>
                        <span class="text-xs font-extrabold text-slate-800">{{ $excelFile->getClientOriginalName() }}</span>
                        <span class="text-[10px] text-slate-400 font-medium block">Ukuran: {{ number_format($excelFile->getSize() / 1024, 1) }} KB</span>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 text-[11px] font-bold px-2.5 py-1 rounded-full border border-emerald-200">
                    <x-tabler-circle-check class="h-4 w-4 text-emerald-600" />
                    Terunggah
                </span>
            </div>
        @endif
    </div>

    <x-slot:footer>
        <div class="flex justify-end gap-2">
            <x-ts:button size="sm" flat color="slate" wire:click="closeImportModal">Batal</x-ts:button>
            <x-ts:button size="sm" color="indigo" wire:click="importExcel" loading="importExcel" :disabled="!$excelFile">
                <x-tabler-upload class="h-4 w-4 mr-1" />
                Mulai Impor Gaji
            </x-ts:button>
        </div>
    </x-slot:footer>
</x-ts:modal>
