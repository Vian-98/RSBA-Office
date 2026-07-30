<div x-data="distribusi" class="flex flex-col gap-2">

    {{-- tab action --}}
    <div x-data="{
        searchTerm: @entangle('search'),
    
        init() {
            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        },
    
        reset() {
            this.searchTerm = '';
            this.$nextTick(() => {
                this.$refs.searchInput.focus();
            })
        },
    }">
        <div class="flex flex-row rounded-lg bg-white px-4 py-2">

            {{-- search input --}}
            <div class="flex w-full flex-row items-center gap-2">

                <div class="relative w-3/4 lg:w-1/3">
                    <!-- Input Field -->
                    <input x-ref="searchInput" wire:model.live.debounce.300ms='search' placeholder="Cari No. Transaksi Distribusi"
                        class="h-8 w-full rounded-lg border-gray-200 px-10 transition-all duration-300 focus:outline-none" autocomplete="off" />

                    <!-- Icon (Search) -->
                    <x-ts:icon name="tabler.scan" class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 transform text-gray-400" />

                    {{-- clear icon --}}
                    <button x-show="searchTerm" @click="reset" class="absolute right-3 top-1/2 -translate-y-1/2 transform text-red-500 hover:text-red-600" type="button">
                        <x-ts:icon name="tabler.x" class="h-4 w-4" />
                    </button>

                </div>
            </div>

            <div class="ml-auto flex items-center justify-end gap-2">

                <span role="button" x-show="transaksiPanel" x-on:click="transaksiDistribusi()" class="flex flex-row items-center px-2 py-1 text-red-500 hover:rounded-lg hover:bg-red-200/25">
                    <x-ts:icon name="tabler.chevron-left" class="h-5 w-5" />
                    Kembali
                </span>

                <x-ts:button sm icon="tabler.plus" x-show="!transaksiPanel" x-on:click="transaksiDistribusi()">
                    Distribusi
                </x-ts:button>
            </div>
        </div>


        {{-- Hasil Cari Untuk Penerimaan Barang --}}
        <div x-show="searchTerm" @keyup.escape.window="searchTerm = ''" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95" class="relative w-3/5">

            <div class="absolute inset-0 left-0 z-10">
                <div class="relative mt-1 transform rounded-md border-2 border-b-4 border-indigo-500 bg-white p-4 shadow-2xl transition-transform">
                    <div class="absolute left-[3%] top-[-12px] mb-1 h-0 w-0 -translate-x-1/2 transform border-b-8 border-l-8 border-r-8 border-transparent border-b-indigo-500">
                    </div>
                    {{-- content --}}

                    <div class="mb-2">
                        <span class="italic text-gray-500" wire:loading wire:target='search'> Searching : </span>
                        <span class="italic text-gray-500" wire:loading.remove> Hasil Pencarian : </span>
                        <span class="font-semibold text-indigo-500" x-text="searchTerm"></span>
                    </div>

                    <div wire:loading wire:target="search" class="text-sm italic text-gray-400">
                        Loading ...
                    </div>

                    <div wire:loading.remove>
                        @if ($distribusi)
                            <livewire:Distribusi.Pencarian :$distribusi :key="Str::random()" />
                        @else
                            <span class="text-sm text-danger-500">Data tidak ditemukan. </span>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>
    {{-- end tab action --}}


    <div x-show="!transaksiPanel" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" class="flex flex-col gap-2">
        {{-- stats --}}
        <div class="w-full rounded-md border-2 border-white p-1">
            <livewire:Distribusi.Stats :key="Str::random()" />
        </div>
        {{-- end stats --}}

        {{-- table --}}
        <div class="w-full">
            <x-ts:tab selected="Permintaan" class="rounded-lg bg-white p-2">

                <x-ts:tab.items tab="Permintaan">
                    @if ($this->getHasNewRequestProperty())
                        <x-slot:left>
                            <span class="absolute block h-1 w-1 animate-pulse rounded-full bg-red-500 ring-2 ring-red-300"></span>
                        </x-slot:left>
                    @endif
                </x-ts:tab.items>

                <x-ts:tab.items tab="Terdistribusi">
                    <x-slot:left>
                        <x-ts:icon name="tabler.table" class="h-5 w-5" />
                    </x-slot:left>
                    <livewire:Distribusi.TableDistribusi :key="Str::random()" />
                </x-ts:tab.items>

            </x-ts:tab>
        </div>

        {{-- end table --}}
    </div>

    <div x-show="transaksiPanel" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95">
        <livewire:Distribusi.Transaksi :key="Str::random()" />
    </div>

</div>

@script
    <script>
        Alpine.data('distribusi', () => {
            return {
                transaksiPanel: false,

                transaksiDistribusi() {
                    this.transaksiPanel = !this.transaksiPanel;
                }
            }
        });
    </script>
@endscript
