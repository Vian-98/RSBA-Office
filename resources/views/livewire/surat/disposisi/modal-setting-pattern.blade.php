<div>
    <!-- Modal Setting Pattern Agenda -->
    <x-ts:modal wire="modalSettingPattern" id="modal-setting-pattern" title="Pengaturan Format No. Agenda">
        <div class="space-y-4">
            <p class="text-sm text-slate-600 dark:text-slate-300">
                Atur template format nomor agenda secara dinamis. Anda dapat menggunakan variabel placeholder berikut:
            </p>
            <div class="text-xs bg-slate-100 dark:bg-slate-900 p-3 rounded-lg font-mono space-y-1 text-slate-700 dark:text-slate-300">
                <div><span class="font-bold text-indigo-600">{NUMBER:4}</span> : Urutan nomor dengan padding 4 digit (0001, 0002)</div>
                <div><span class="font-bold text-indigo-600">{YYYY}</span> : Tahun berjalan 4 digit (2026)</div>
                <div><span class="font-bold text-indigo-600">{MM}</span> : Bulan berjalan 2 digit (08)</div>
            </div>
            <x-ts:input wire:model="patternInput" label="Format Template No. Agenda" placeholder="Contoh: AG/{YYYY}/{NUMBER:4}" />
        </div>
        <x-slot name="footer">
            <x-ts:button wire:click="$set('modalSettingPattern', false)" x-on:click="$tsui.close('modal-setting-pattern')" color="slate" outline>Batal</x-ts:button>
            <x-ts:button wire:click="savePatternSetting" color="indigo">Simpan Format</x-ts:button>
        </x-slot>
    </x-ts:modal>
</div>
