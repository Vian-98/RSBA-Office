<div class="flex flex-col gap-2">
    {{-- <div class="flex flex-row rounded-lg bg-white px-4 py-2"> --}}
    <div class="ml-auto flex items-center justify-end gap-2">
        <x-ts:button sm icon="tabler.plus" x-on:click="$dispatch('open-modal',{id:'modal-add-chapter'})">
            Tambah
        </x-ts:button>
    </div>
    {{-- </div> --}}

    {{-- Layout Chapters --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($this->chapters() as $item)
            {{-- <div @click="$wire.set('selectedChapter', {{ $item->id }})" x-on:click="$dispatch('open-modal',{id:'modal-element-chapter'})" --}}
            <div wire:click='toElement({{ $item->id }})'
                class="transform cursor-pointer rounded-lg border border-gray-200 bg-white p-6 shadow transition duration-300 ease-in-out hover:-translate-y-1 hover:border-indigo-500 hover:bg-indigo-50 hover:shadow-lg">
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500 font-bold text-white">
                        {{ substr($item->singkatan, 0, 1) }}
                    </div>
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>

                <h3 class="mb-2 text-lg font-semibold text-gray-800">
                    {{ $item->singkatan }}
                </h3>

                <p class="text-sm text-gray-500">
                    {{ $item->nama }}
                </p>
                @if (isset($item->nama))
                    <p class="text-xs text-gray-500">
                        Ketua : {{ $item->user?->karyawan?->nama ?? 'Belum Ditentukan' }}
                    </p>
                @endif

                <div>
                    {{-- stats --}}
                    <livewire:Akreditasi.Chapters.Stat :chapterId="$item->id" :key="'stats-chapters' . $item->id" />
                </div>
            </div>
        @endforeach

    </div>


    <x-filament::modal id="modal-add-chapter" width="xl" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>Standar / Chapter</x-slot:heading>

        <livewire:Akreditasi.Chapters.Add :$kegiatanId :$folderKegiatan />
    </x-filament::modal>


    {{-- <x-filament::modal id="modal-element-chapter" width="screen" :close-by-clicking-away="false" :autofocus="false">
        <x-slot:heading>Element Penilaian
            <span class="text-indigo-500">{{ $namaSelectedChapter }}</span>
        </x-slot:heading>

        <livewire:Akreditasi.Element.Index :chapterId="$selectedChapter" :key="'elements' . $selectedChapter" />
    </x-filament::modal> --}}
</div>
