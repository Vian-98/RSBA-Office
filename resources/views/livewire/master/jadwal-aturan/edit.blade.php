<div>
    <form wire:submit.prevent="submit" class="flex flex-col gap-2" autocomplete="off">
        @csrf

        <div class="flex w-full flex-col gap-2">
            <x-ts:select.styled
                wire:model.live="bagian_id"
                label="Bagian (opsional)"
                placeholder="Kosongkan untuk Aturan Umum RSBA"
                :options="$bagianOptions"
                select="label:label|value:value"
                searchable
            />

            @if(empty($bagian_id))
                <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-800">
                    Aturan ini berlaku umum untuk semua Bagian yang tidak memiliki override.
                </div>
            @else
                <div class="p-2.5 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
                    Aturan ini hanya berlaku sebagai override untuk Bagian yang dipilih.
                </div>
            @endif

            <x-ts:select.styled wire:model.defer="kode" label="Kode Aturan" placeholder="Pilih Aturan" :options="$kodeOptions" select="label:label|value:value" searchable />

            <x-ts:input wire:model.defer="nilai" label="Nilai" placeholder="Contoh: 14 (untuk hari) atau true/false" />

            <div class="flex flex-col gap-3 mt-2">
                <x-ts:toggle wire:model.defer="aktif" label="Aktif" />
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <x-ts:button md outline @click="$dispatch('close-modal',{id:'edit-jadwal-aturan'})">Tutup</x-ts:button>
            <x-ts:button loading="submit" md type="submit">Simpan</x-ts:button>
        </div>
    </form>
</div>
