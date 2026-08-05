<div>
    <form wire:submit.prevent="submit" class="flex flex-col gap-2" autocomplete="off">
        @csrf

        <div class="flex w-full flex-col gap-2">
            <x-ts:select.styled wire:model.defer="ruangan_id" label="Ruangan (Tim)" placeholder="Pilih Ruangan" :options="$ruanganOptions" select="label:label|value:value" searchable />
            @if ($requiresBagianSelection)
                <x-ts:select.styled wire:model.defer="bagian_id" label="Bagian Jadwal" placeholder="Pilih Bagian Jadwal" :options="$bagianOptions" select="label:label|value:value" searchable />
                <x-ts:alert text="Ruangan ini memiliki pegawai dari beberapa bagian. Pilih bagian pemilik jadwal agar approver dan aturan jadwal tepat." color="warning" />
            @endif
            <x-ts:select.styled wire:model.defer="bulan" label="Bulan" placeholder="Pilih Bulan" :options="$bulanOptions" select="label:label|value:value" searchable />

            <x-ts:select.styled wire:model.defer="tahun" label="Tahun" placeholder="Pilih Tahun" :options="$tahunOptions" select="label:label|value:value" searchable />
        </div>

        <x-ts:alert text="Proses ini akan meng-*generate* draf jadwal kosong (dan otomatis terisi khusus pegawai shift reguler) untuk bulan dan ruangan yang dipilih. Proses mungkin memakan waktu beberapa detik." color="info" class="mt-2" />

        <div class="flex justify-end gap-2 pt-4">
            <x-ts:button md outline @click="$dispatch('close-modal',{id:'generate-jadwal-kerja'})">Tutup</x-ts:button>
            <x-ts:button loading="submit" md type="submit">Generate</x-ts:button>
        </div>
    </form>
</div>
