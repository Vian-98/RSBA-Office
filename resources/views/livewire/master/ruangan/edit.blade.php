<div>
    <form wire:submit.prevent="submit" class="space-y-2" autocomplete="off">
        <div class="w-full space-y-3">
            <x-ts:input label="Nama Ruangan" wire:model.defer="nama" placeholder="Nama Ruangan" />

            <x-ts:select.styled
                label="Koordinator Ruangan"
                wire:model="karyawan_id"
                :options="$karyawanOptions"
                select="label:label|value:value"
                searchable
                placeholder="Pilih / Kosongkan Koordinator Ruangan..."
            />
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <x-ts:button md outline @click="$dispatch('close-modal',{id:'modal-edit-ruangan'})">Tutup</x-ts:button>
            <x-ts:button loading="submit" md type="submit">Simpan Perubahan</x-ts:button>
        </div>

    </form>
</div>
