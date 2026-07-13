<div>
    <form wire:submit.prevent="submit" class="flex flex-col gap-2" autocomplete="off">
        @csrf

        <div class="flex w-full flex-col gap-2">
            <x-ts:select.styled wire:model.defer="ruangan_id" label="Ruangan" placeholder="Pilih Ruangan" :options="$ruanganOptions" select="label:label|value:value" searchable />
            
            <x-ts:select.styled wire:model.defer="karyawan_id" label="Koordinator (Karyawan)" placeholder="Pilih Karyawan" :options="$karyawanOptions" select="label:label|value:value" searchable />

            <div class="flex flex-col gap-3 mt-2">
                <x-ts:toggle wire:model.defer="aktif" label="Aktif" />
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <x-ts:button md outline @click="$dispatch('close-modal',{id:'edit-ruangan-koordinator'})">Tutup</x-ts:button>
            <x-ts:button loading="submit" md type="submit">Simpan</x-ts:button>
        </div>
    </form>
</div>
