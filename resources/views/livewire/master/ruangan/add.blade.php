<div>
    <form wire:submit.prevent="submit" class="space-y-2" autocomplete="off">
        @csrf

        <div class="w-full space-y-3">
            <x-ts:input label="Nama Ruangan" wire:model.lazy="nama" placeholder="Nama Ruangan" />

            <x-ts:select.styled
                label="Koordinator Ruangan (Opsional)"
                wire:model="karyawan_id"
                :options="$karyawanOptions"
                select="label:label|value:value"
                searchable
                placeholder="Pilih Koordinator Ruangan..."
            />
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <x-ts:button md outline @click="$dispatch('close-modal',{id:'new-ruangan'})">Tutup</x-ts:button>
            <x-ts:button loading="submit" md type="submit">Simpan</x-ts:button>
        </div>

    </form>
</div>
