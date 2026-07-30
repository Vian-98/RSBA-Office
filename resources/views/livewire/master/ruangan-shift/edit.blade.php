<div>
    <form wire:submit.prevent="submit" class="flex flex-col gap-2" autocomplete="off">
        @csrf

        <div class="flex w-full flex-col gap-2">
            <x-ts:select.styled wire:model.defer="ruangan_id" label="Unit Kerja / Ruangan" placeholder="Pilih Ruangan" :options="$ruanganOptions" select="label:label|value:value" searchable />
            
            <x-ts:select.styled wire:model.defer="shift_id" label="Shift" placeholder="Pilih Shift Dasar" :options="$shiftOptions" select="label:label|value:value" searchable />
        </div>

        <div class="mt-4 rounded border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                Override Jam (Opsional)
            </p>
            <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                Kosongkan untuk menggunakan jam default dari master shift.
            </p>
            <div class="flex w-full flex-col gap-3">
                <div class="flex gap-2">
                    <x-ts:input type="time" wire:model.defer="jam_masuk_override" label="Jam Masuk" class="w-full" />
                    <x-ts:input type="time" wire:model.defer="jam_keluar_override" label="Jam Keluar" class="w-full" />
                </div>
                <x-ts:input type="number" wire:model.defer="toleransi_telat_menit_override" label="Toleransi Telat (menit)" class="w-full" />
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <x-ts:button md outline @click="$dispatch('close-modal',{id:'edit-ruangan-shift'})">Tutup</x-ts:button>
            <x-ts:button loading="submit" md type="submit">Simpan</x-ts:button>
        </div>
    </form>
</div>
