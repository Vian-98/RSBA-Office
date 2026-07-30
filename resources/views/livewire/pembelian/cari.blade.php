<div>
    <div x-data="{
        searchTerm: '',
        lastSearched: '',
        searchFounded: false,
    
        init() {
            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        },
    
        reset() {
            this.searchTerm = null;
            this.lastSearched = null;
            $wire.set('search', null);
            $wire.set('pembelian', null);
    
        },
    
    }">
        <div class="flex w-full flex-col gap-2 lg:flex-row lg:items-center" x-on:close-modal.window="reset";>

            <div class="relative w-full">
                <!-- Input Field -->
                <input x-ref="searchInput" x-model='searchTerm' x-on:keyup.enter.window="$wire.set('search', $refs.searchInput.value); lastSearched = searchTerm;" placeholder="Cari No. Transaksi, No. PO"
                    class="h-8 w-full rounded-lg border-gray-200 px-10 transition-all duration-300 focus:outline-none" autocomplete="off" />

                <!-- Icon (Search) -->
                <x-ts:icon name="tabler.scan" class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 transform text-gray-400" />

                {{-- clear icon --}}
                <button x-show="searchTerm" @click="reset" class="absolute right-3 top-1/2 -translate-y-1/2 transform text-red-500 hover:text-red-600" type="button">
                    <x-ts:icon name="tabler.x" class="h-4 w-4" />
                </button>

            </div>
            <div class="flex flex-row flex-wrap items-center gap-2 text-xs">
                <template x-if="searchTerm && searchTerm !== lastSearched">
                    <span class="whitespace-nowrap italic text-gray-600"> Enter untuk mencari. &nbsp; </span>
                </template>
                <span class="whitespace-nowrap text-xs italic text-gray-600" wire:loading wire:target='search'> Searching... </span>
                <span class="whitespace-nowrap text-xs text-danger-500" wire:loading.remove x-show="searchTerm">
                    @if ($search && empty($pembelian))
                        Data tidak ditemukan.
                    @endif
                </span>
            </div>
        </div>

        <div class="absolute">
            <x-filament::modal id="{{ $modalPreffix }}-detail" width="5xl">
                @if ($pembelian)
                    <livewire:Pembelian.Detail :id="$pembelian->id" :key="$modalPreffix . 'detail' . $pembelian->id" />
                @endif

                <x-slot name="footer">
                    {{-- Modal footer content --}}
                    <div class="mt-4 flex justify-end">
                        <x-ts:button sm color="red" x-on:click="$dispatch('close-modal',{ id: '{{ $modalPreffix }}-detail'})">Tutup</x-ts:button>
                    </div>
                </x-slot>
            </x-filament::modal>


            <x-filament::modal id="{{ $modalPreffix }}-terima" width="5xl" x-on:tutup-modal-terima.window="$dispatch('close-modal',{id:'{{ $modalPreffix }}-terima'})">
                @if ($pembelian)
                    <livewire:Pembelian.Penerimaan.TerimaBarang :pembelian="$pembelian" :key="$modalPreffix . 'terima' . $pembelian->id" @penerimaan-beli-saved="$refresh" />
                @endif
            </x-filament::modal>
        </div>
    </div>
</div>
