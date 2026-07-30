<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-4" autocomplete="off">
        <div class="flex flex-col gap-2">
            <x-ts:select.styled wire:model.defer='karyawan' placeholder="Pilih Karyawan" searchable :request="route('api.karyawan.reg.dokter')" select="label:nama|value:id" />

            <x-ts:select.styled wire:model.defer='subSpesialis' placeholder="Spesialis" searchable :options="$subSpesialisOpt" select="label:nama|value:id" />

            <x-ts:textarea placeholder="Jadwal"></x-ts:textarea>
        </div>
        <div class="ml-auto flex justify-end gap-2">
            <x-ts:button outline x-on:click="$dispatch('close-modal',{id:'new-dokter'})">
                Tutup
            </x-ts:button>

            <x-ts:button type="submit" loading="submit" icon="tabler.checks">
                Simpan
            </x-ts:button>
        </div>

    </form>
</div>
