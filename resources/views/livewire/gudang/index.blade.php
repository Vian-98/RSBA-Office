<div x-data="gudang" class="flex flex-col gap-2">
    <div class="relative flex w-full flex-row rounded-md bg-white px-4 py-2">
        <span class="flex items-center text-lg italic text-indigo-500" x-text="panelHeading"></span>


        <div class="ml-auto flex justify-end gap-2">
            <x-ts:button outline color="violet" x-show="history.length === 0 " sm x-on:click="togglePanel('permintaan')">
                <x-slot:left>
                    <x-ts:badge color="violet" :text="$permintaanCount" :round="true" light />
                </x-slot:left>
                Permintaan
            </x-ts:button>

            <x-ts:button x-show="history.length === 0" sm icon="tabler.shopping-cart-plus" x-on:click="togglePanel('penerimaan')">Pembelian</x-ts:button>

            <x-ts:button x-show="history.length === 0" x-on:click="togglePanel('distribusi')" sm color="red" icon="tabler.shopping-cart-share">Distribusi</x-ts:button>

            <span role="button" x-show="history.length > 0" x-on:click="goHome()" class="flex flex-row items-center px-2 py-1 text-red-500 hover:rounded-lg hover:bg-red-200/25">
                <x-ts:icon name="tabler.chevron-left" class="h-5 w-5" />
                Kembali
            </span>
        </div>

    </div>

    <div x-show="panelActive === 'main'" class="flex flex-col gap-2">
        <div class="rounded-md border-2 border-white p-1">
            <livewire:Gudang.stats :key="Str::random()" />
        </div>
        <div class="w-full rounded-lg bg-white p-4">
            <livewire:Gudang.TableGudang key="table-gudang" />
        </div>
    </div>

    <div x-show="panelActive === 'permintaan'" class="rounded-md bg-white px-4 py-2">
        <livewire:Pembelian.Permintaan.ListPermintaan :key="Str::random()" />
    </div>

    <div x-show="panelActive === 'preorder'" class="rounded-md bg-white px-4 py-2">
        <livewire:Pembelian.TransaksiBeliPo :key="Str::random()" />
    </div>

    <div x-show="panelActive === 'penerimaan'" class="rounded-md bg-white px-4 py-2">

        <livewire:Pembelian.Penerimaan.Options wire:key="pesanan- {{ uniqid() }}" />
    </div>

    <div x-show="panelActive === 'distribusi'">
        <livewire:Distribusi.Transaksi :key="Str::random()" />
    </div>
</div>

@script
    <script>
        Alpine.data('gudang', () => {
            return {
                panelActive: 'main',
                history: [],



                panelGudang: true,
                panelDistribusi: false,
                panelPembelianPO: false,
                panelPembelianLangsung: false,
                panelPermintaan: false,
                panelHeading: '',

                togglePanel(active) {
                    this.history.push(this.panelActive);
                    this.panelActive = active;

                    this.toggleHeading(active);
                },

                toggleHeading(active) {
                    if (active === 'distribusi') {
                        this.panelHeading = 'Distibusi';
                    } else if (active === 'penerimaan') {
                        this.panelHeading = 'Penerimaan';
                    } else if (active === 'permintaan') {
                        this.panelHeading = 'Permintaan Pengadaan';
                    } else {
                        this.panelHeading = '';
                    }
                },

                goHome() {
                    if (this.history.length > 0) {
                        this.panelActive = 'main';
                        this.history = [];
                    }
                },
            };
        });
    </script>
@endscript
