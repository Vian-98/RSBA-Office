<div>
    @if($karyawanInfo)
        {{-- Header info karyawan/dokter --}}
        <div class="mb-4 rounded-lg border border-primary-100 bg-primary-50 px-4 py-3 dark:border-primary-900 dark:bg-primary-950">
            <p class="text-sm font-semibold text-primary-800 dark:text-primary-300">
                {{ $karyawanInfo->full_nama }}
            </p>
            @if($spesialisInfo)
                <p class="text-xs text-primary-600 dark:text-primary-400">
                    {{ $spesialisInfo }}
                </p>
            @endif
        </div>

        {{-- Daftar ruangan dengan checkbox --}}
        <div class="mb-4 max-h-72 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700">
            @forelse($allRuangan as $ruangan)
                <label
                    for="ruangan-{{ $ruangan->id }}"
                    class="flex cursor-pointer items-center gap-3 border-b border-gray-100 px-4 py-2.5 transition-colors last:border-0
                           hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800
                           {{ in_array((string)$ruangan->id, $selectedRuangan) ? 'bg-primary-50 dark:bg-primary-950' : '' }}"
                >
                    <input
                        type="checkbox"
                        id="ruangan-{{ $ruangan->id }}"
                        value="{{ $ruangan->id }}"
                        wire:model.defer="selectedRuangan"
                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600"
                    >
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        {{ $ruangan->nama }}
                    </span>
                </label>
            @empty
                <p class="px-4 py-3 text-sm text-gray-500">Tidak ada ruangan aktif.</p>
            @endforelse
        </div>

        <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
            <x-ts:icon name="tabler.info-circle" class="inline h-3.5 w-3.5" />
            {{ count($selectedRuangan) }} ruangan dipilih dari {{ count($allRuangan) }} ruangan aktif
        </p>

        {{-- Tombol aksi --}}
        <div class="flex justify-end gap-2">
            <x-ts:button outline x-on:click="$dispatch('close-modal', {id: 'modal-koor-ruangan'})">
                Batal
            </x-ts:button>
            <x-ts:button wire:click="save" loading="save" icon="tabler.device-floppy">
                Simpan
            </x-ts:button>
        </div>
    @else
        <p class="py-4 text-center text-sm text-gray-500">Memuat data koordinator...</p>
    @endif
</div>
