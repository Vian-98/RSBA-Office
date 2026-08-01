<div>
    <form wire:submit.prevent="submit" class="flex flex-col gap-2" autocomplete="off">
        @csrf

        <div class="flex w-full flex-col gap-2">
            <x-ts:input wire:model.defer="kode" label="Kode" placeholder="Misal: PAGI" />
            <x-ts:input wire:model.defer="nama" label="Nama Shift" placeholder="Misal: Shift Pagi" />
            
            <div class="flex gap-2">
                <div class="w-1/2">
                    <x-ts:input type="time" wire:model.defer="jam_masuk" label="Jam Masuk" />
                </div>
                <div class="w-1/2">
                    <x-ts:input type="time" wire:model.defer="jam_keluar" label="Jam Keluar" />
                </div>
            </div>

            <x-ts:input type="number" wire:model.defer="toleransi_telat_menit" label="Toleransi Telat (Menit)" />
            <x-ts:input type="color" wire:model.defer="warna" label="Warna (Tampilan Kalender)" />

            <div class="flex flex-col gap-3 mt-2">
                <x-ts:toggle wire:model.defer="lintas_hari" label="Lintas Hari (Jam Keluar < Jam Masuk)" />
                <x-ts:toggle wire:model.defer="aktif" label="Aktif" />
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <x-ts:button md outline @click="$dispatch('close-modal',{id:'new-jadwal-shift'})">Tutup</x-ts:button>
            <x-ts:button loading="submit" md type="submit">Simpan</x-ts:button>
        </div>
    </form>
</div>
