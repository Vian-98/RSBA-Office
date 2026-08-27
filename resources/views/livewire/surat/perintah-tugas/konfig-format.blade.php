<form wire:submit.prevent="save" class="flex flex-col gap-4">
    <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-3 text-xs text-amber-900 leading-relaxed">
        <strong>💡 Konfigurasi Format Nomor:</strong> Anda dapat menyesuaikan template format nomor untuk Surat Perintah Tugas (SPT).
    </div>

    {{-- Pengaturan Format Nomor Surat --}}
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
        <x-ts:input label="Template Format Nomor SPT *" wire:model="format_nomor" hint="Tag: {no}, {kode_jabatan}, {tanggal}, {dd}, {mm}, {yyyy}" />
        <div class="mt-1.5 flex items-center gap-1.5 text-[11px] text-slate-500 font-mono">
            <span>Contoh hasil:</span>
            <span class="font-bold text-slate-700">1/S4/SPT/PBA-DIR/14.08.2026</span>
        </div>
    </div>

    <x-ts:input label="Keterangan Format" wire:model="keterangan" />

    <div class="ml-auto flex items-center gap-2 pt-2">
        <x-ts:button type="button" outline color="secondary" sm x-on:click="$dispatch('close-modal', {id: 'modal-konfig-format-spt'})">
            Batal
        </x-ts:button>
        <x-ts:button type="submit" color="primary" sm icon="tabler.device-floppy" loading="save">
            Simpan Format Nomor
        </x-ts:button>
    </div>
</form>
