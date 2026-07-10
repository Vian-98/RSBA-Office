<div x-data="pembelian" class="flex flex-col gap-2">

    <div class="flex flex-row gap-2 rounded-lg bg-white px-4 py-2">
        @can('terima-pembelian')
            {{-- search input --}}
            <div class="flex w-full flex-col gap-2 lg:w-1/2">
                <livewire:Pembelian.Cari />
            </div>
        @endcan


        <div class="ml-auto flex justify-end gap-2">
            <div class="flex gap-2">
                <x-ts:button x-show="history.length === 0" sm outline color="violet" x-on:click="$wire.set('state',Math.random().toString(36).substring(2, 5)); togglePanel('pesanan')"
                    icon="tabler.file-plus">
                    Pesanan
                </x-ts:button>

                <x-ts:button x-show="history.length === 0" sm outline icon="tabler.playlist-add" x-on:click="togglePanel('penerimaan')">
                    Penerimaan
                </x-ts:button>
            </div>

            <span role="button" x-show="history.length > 0" x-on:click="goBack()" class="flex flex-row items-center px-2 py-1 text-red-500 hover:rounded-lg hover:bg-red-200/25">
                <x-ts:icon name="tabler.chevron-left" class="h-5 w-5" />
                Kembali
            </span>
            {{-- <x-ts:button x-show="history.length > 0" x-on:click="goBack()" sm outline color="red" class="">Kembali</x-ts:button> --}}
        </div>

    </div>



    {{-- Table List Pembelian --}}
    <div x-show="panelActive === 'main'" class="w-full">
        <div class="w-full rounded-md border-2 border-white p-1" x-data="{ showStats: false, refreshKey: Date.now() }">
            <x-ts:toggle sm @click='showStats = !showStats; refreshKey = Date.now()' label="Stats" />

            <div x-show="showStats">
                <livewire:pembelian.stats x-bind:key="'stats'" />
                {{-- x-bind:key="'stats-' + refreshKey" --}}
            </div>
        </div>


        <div x-data="{ activeTab: @entangle('tab') }" class="flex flex-col gap-4">
            <!-- Tab Headers -->
            <div class="flex border-b border-gray-200">
                <button 
                    type="button"
                    x-on:click="activeTab = 'Permintaan'"
                    :class="activeTab === 'Permintaan' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="flex items-center gap-2 border-b-2 py-4 px-1 text-sm focus:outline-none transition-all duration-200"
                >
                    @if ($this->getRequestPembelianProperty > 0)
                        <span class="block h-2 w-2 animate-pulse rounded-full bg-red-500 ring-2 ring-red-300"></span>
                    @endif
                    Permintaan
                </button>

                <button 
                    type="button"
                    x-on:click="activeTab = 'Transaksi'"
                    :class="activeTab === 'Transaksi' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="flex items-center gap-2 border-b-2 py-4 px-1 text-sm focus:outline-none ml-8 transition-all duration-200"
                >
                    <x-ts:icon name="tabler.invoice" class="h-5 w-5" />
                    Transaksi
                </button>

                <button 
                    type="button"
                    x-on:click="activeTab = 'Barang'"
                    :class="activeTab === 'Barang' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="flex items-center gap-2 border-b-2 py-4 px-1 text-sm focus:outline-none ml-8 transition-all duration-200"
                >
                    <x-ts:icon name="tabler.box" class="h-5 w-5" />
                    Barang
                </button>
            </div>

            <!-- Tab Contents -->
            <div class="w-full mt-2">
                <div x-show="activeTab === 'Permintaan'" wire:key="tab-content-permintaan">
                    <livewire:Pembelian.Permintaan.ListPermintaanBarang key="list-permintaan-barang" />
                </div>

                <div x-show="activeTab === 'Transaksi'" wire:key="tab-content-transaksi" x-cloak>
                    <livewire:Pembelian.TablePembelian key="table-pembelian" />
                </div>

                <div x-show="activeTab === 'Barang'" wire:key="tab-content-barang" x-cloak>
                    <livewire:Pembelian.TablePembelianBarang key="table-pembelian-by-barang" />
                </div>
            </div>
        </div>
    </div>


    <div class="w-full rounded-lg bg-white p-4">
        <div x-show="panelActive === 'pesanan'" class="flex flex-col gap-3">
            <h3 class="border-b-2 text-indigo-500">Pesanan</h3>
            <div>
                <livewire:Pembelian.Pesanan.Add @new-pesanan-created="$refresh" :key="'pesanan-' . Str::random()" />
            </div>
        </div>
        <div x-show="panelActive === 'penerimaan'" class="flex flex-col gap-3">
            <h3 class="border-b-2 text-indigo-500">Penerimaan</h3>
            <div>
                <livewire:Pembelian.Penerimaan.Options key="penerimaan" />
            </div>
        </div>
    </div>


</div>

@script
    <script>
        Alpine.data('pembelian', () => {
            return {
                panelActive: 'main',
                history: [],

                togglePanel(active) {
                    this.history.push(this.panelActive);
                    this.panelActive = active;
                },

                goBack() {
                    if (this.history.length > 0) {
                        this.panelActive = 'main';
                        this.history = [];

                    }
                }
            }
        })
    </script>
@endscript
