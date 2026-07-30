<div>
    <form wire:submit.prevent="submit" class="flex flex-col gap-2" autocomplete="off">
        @csrf

        <div class="flex w-full flex-col gap-2">
            <x-ts:select.styled wire:model.live="ruangan_id" label="Ruangan" placeholder="Pilih Ruangan" :options="$ruanganOptions" select="label:label|value:value" searchable />
            
            {{-- Filter Kategori --}}
            <div class="flex items-center gap-1.5 my-1">
                <span class="text-xs font-medium text-gray-600">Filter Karyawan:</span>
                <button type="button" wire:click="$set('kategoriFilter', 'all')" class="px-2 py-0.5 text-xs rounded transition-colors {{ $kategoriFilter === 'all' ? 'bg-primary-600 text-white font-semibold' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Semua</button>
                <button type="button" wire:click="$set('kategoriFilter', 'dokter')" class="px-2 py-0.5 text-xs rounded transition-colors {{ $kategoriFilter === 'dokter' ? 'bg-blue-600 text-white font-semibold' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">👨‍⚕️ Hanya Dokter</button>
                <button type="button" wire:click="$set('kategoriFilter', 'non_dokter')" class="px-2 py-0.5 text-xs rounded transition-colors {{ $kategoriFilter === 'non_dokter' ? 'bg-gray-600 text-white font-semibold' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Non-Dokter</button>
            </div>

            <x-ts:select.styled wire:model.live="karyawan_id" label="Karyawan / Dokter Ditugaskan" placeholder="Pilih Karyawan atau Dokter" :options="$karyawanOptions" select="label:label|value:value" searchable />

            {{-- Pilih akun login untuk koordinator (opsional, auto-suggest jika karyawan punya akun) --}}
            <div class="rounded-lg border border-blue-100 bg-blue-50 p-3">
                <p class="text-xs font-semibold text-blue-700 mb-1.5">Akun Login (Tugas Tambahan Koordinator)</p>
                <p class="text-[11px] text-blue-500 mb-2">Pilih akun user yang akan mendapat akses koordinator ruangan ini. Akun ini tetap memakai role asalnya — koordinator hanya tugas tambahan.</p>
                <x-ts:select.styled wire:model.defer="user_id" label="Akun Login (opsional)" placeholder="Pilih atau kosongkan" :options="$userOptions" select="label:label|value:value" searchable />
            </div>

            <div class="flex flex-col gap-3 mt-2">
                <x-ts:toggle wire:model.defer="aktif" label="Aktif" />
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <x-ts:button md outline @click="$dispatch('close-modal',{id:'new-ruangan-koordinator'})">Tutup</x-ts:button>
            <x-ts:button loading="submit" md type="submit">Simpan</x-ts:button>
        </div>
    </form>
</div>
