<div>
    <form wire:submit.prevent="update" class="flex flex-col gap-2" autocomplete="off">
        <div class="flex w-full flex-col gap-2">

            <x-ts:input wire:model.defer="nama" placeholder="Nama Jabatan" />



            <x-ts:select.styled wire:model.defer='atasan' searchable placeholder="Atasan" :options="$atasan_options" select="label:nama|value:id" />

            <x-ts:input wire:model.defer="kode_surat" placeholder="Kode Surat" />

            <x-ts:select.styled wire:model.defer='bagian' searchable :options="$bagian_options" placeholder="Bagian" select="label:nama|value:id" />

            <x-ts:input label="Tunjangan Jabatan" wire:model.defer="tunjangan_jabatan" type="text" prefix="Rp" placeholder="0" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let inp = $el.querySelector('input') || $el; inp.value = (inp.value || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" />
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <x-ts:button md outline @click="$dispatch('close-modal',{id:'edit-jabatan'})">Tutup</x-ts:button>
            <x-ts:button loading="update" md type="submit">Simpan</x-ts:button>
        </div>
    </form>
</div>
