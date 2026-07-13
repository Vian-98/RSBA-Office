<div class="flex flex-col gap-2">

    <div class="flex flex-col justify-between gap-2 rounded-lg bg-white px-4 py-2 lg:flex-row">

        {{-- search input --}}
        <div class="relative flex w-3/4 flex-row items-center gap-2 lg:w-1/3">

            <div class="w-full">
                <!-- Input Field -->
                <input x-ref="searchInput" placeholder="Pencarian..." class="h-8 w-full rounded-lg border-gray-200 px-10 transition-all duration-300 focus:outline-none" autocomplete="off" />

                <!-- Icon (Search) -->
                <x-ts:icon name="tabler.search" class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 transform text-gray-400" />

                {{-- clear icon --}}
                {{-- <button x-show="searchTerm" @click="reset" class="absolute right-3 top-1/2 -translate-y-1/2 transform text-red-500 hover:text-red-600" type="button">
                    <x-ts:icon name="tabler.x" class="h-4 w-4" />
                </button> --}}

            </div>

            {{-- Pencarian Files --}}
            <x-ts:button sm outline color="" icon="tabler.search" x-on:click="$dispatch('open-modal',{id:'modal-akre-arsip-files'})">
                Files
            </x-ts:button>

            <x-filament::modal id="modal-akre-arsip-files" width="7xl" :close-by-escaping="false" :close-by-clicking-away="false" slide-over>
                <x-slot:heading>
                    Pencarian Files
                </x-slot:heading>
                <livewire:Akreditasi.Documents.Pencarian :kegiatanId="$chapter->kegiatan_id" :chapterId="$chapter->id" :key="'pencarian-docs-' . $chapter->id" />
            </x-filament::modal>
        </div>
        @can('sekretariat-akreditasi')
            <div class="flex flex-row gap-2">

                <x-ts:button sm outline icon="tabler.plus" x-on:click="$dispatch('open-modal',{id:'modal-new-bab'})">Bab</x-ts:button>

                <x-ts:button sm outline icon="tabler.plus" x-on:click="$dispatch('open-modal',{id:'modal-new-penilaian'})">Element Penilaian</x-ts:button>

                {{-- <x-ts:button sm outline icon="tabler.users" x-on:click="$dispatch('open-modal',{id:'modal-new-'})">Anggota</x-ts:button> --}}

                {{-- <x-ts:button sm outline icon="tabler.file-type-zip" loading="downloadZip()" wire:click="downloadZip()">Download</x-ts:button> --}}

                <x-ts:button sm outline icon="tabler.file-type-zip" href="{{ route('kepegawaian.akreditasi.download.download.chapter', $chapter->id) }}">Download</x-ts:button>


                <x-filament::modal id="modal-new-bab" width="2xl">
                    <x-slot:heading>Bab Standar</x-slot:heading>
                    <livewire:Akreditasi.Element.AddBab :chapterId="$chapter->id" :key="'add-bab' . $chapter->id" />
                </x-filament::modal>

                <x-filament::modal id="modal-new-penilaian" width="4xl">
                    <x-slot:heading>Element Penilaian Bab</x-slot:heading>
                    <livewire:Akreditasi.Ep.AddElementPenilaian :chapterId="$chapter->id" :key="'add-ep' . $chapter->id" />
                </x-filament::modal>

            </div>
        @endcan
    </div>


    {{-- Bab Standar --}}
    <div class="flex flex-col gap-6 rounded-md bg-white p-4">
        @forelse ($this->babs as $item)
            @switch($item->bab)
                @case('bab')
                    {{-- Header Bab --}}
                    <div class="flex flex-col gap-2">
                        <span class="font-semibold">{{ $item->no }}. {{ $item->nama }}</span>
                    </div>
                @break

                @case('sub')
                    <div class="ms-4 flex flex-col gap-2 lg:flex-row">
                        {{-- Status & Nilai Container --}}
                        <div class="flex w-full flex-col lg:w-32">
                            <livewire:Akreditasi.Element.Stats :babId="$item->id" :key="'stats-bab-' . $item->id" />
                        </div>

                        {{-- Konten Sub Bab --}}
                        <div class="flex-1 cursor-pointer rounded-md border border-gray-200 bg-white p-2 shadow-md">
                            <h3 class="mb-2 font-medium text-indigo-500">
                                {{ $item->no }}. {{ $item->nama }}
                            </h3>

                            <div class="flex flex-col gap-2 text-wrap text-sm text-gray-700">
                                @if ($item->deskripsi)
                                    <div class="text-xs">
                                        <p>
                                            <span class="font-medium">Deskripsi:</span>
                                            <span>
                                                {!! str($item->deskripsi)->sanitizeHtml() !!}
                                            </span>
                                        </p>
                                    </div>
                                @endif

                                @if ($item->maksud_tujuan)
                                    <div class="text-xs">
                                        <p>
                                            {!! str($item->maksud_tujuan)->sanitizeHtml() !!}
                                        </p>
                                    </div>
                                @endif
                            </div>


                            @if (in_array($item->id, $expandedItems ?? []))
                                <div class="mt-4 space-y-3 border-t border-gray-200 pt-4">
                                    <livewire:Akreditasi.Ep.ListEp :babId="$item->id" :modalPreffix="'bab-' . $item->id" :key="'table-ep-' . $item->id" />
                                </div>
                            @endif

                            {{-- Load More / Show Less Button --}}
                            <div class="mt-3 flex justify-center border-t border-gray-100 pt-3">

                                <button type="button" wire:click="toggleDetail({{ $item->id }})"
                                    class="Ftransition-all group flex w-full items-center justify-center gap-2 rounded-lg bg-white p-2 shadow-sm duration-300 hover:border-indigo-400 hover:bg-indigo-50 hover:shadow-md active:scale-95">

                                    <span class="text-sm text-gray-500 transition-colors duration-300">
                                        {{ in_array($item->id, $expandedItems ?? []) ? 'Sembunyikan' : 'Tampilkan Detail' }}
                                    </span>

                                    <div class="{{ in_array($item->id, $expandedItems ?? []) ? 'rotate-180' : '' }} flex flex-col -space-y-2 transition-transform duration-200">
                                        <svg class="h-3 w-3 text-gray-400 transition-colors group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                @break
            @endswitch

            @empty
                <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-12">
                    <svg class="mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-lg font-medium text-gray-600">Belum ada data Bab & Sub Bab</p>
                    <p class="mt-1 text-sm text-gray-500">Silakan tambahkan data terlebih dahulu</p>
                </div>
            @endforelse
        </div>
        <div class="w-full">
            {{ $this->babs->links() }}
        </div>
    </div>
