<div class="flex flex-col gap-2">

    <div x-data="{ popUpConfirmSubmit: false }" class="flex flex-row rounded-lg bg-white px-4 py-2">
        <div class="ml-auto flex justify-end gap-2">
            <x-ts:button sm icon="tabler.calendar-plus" x-ref="addSoButton" x-on:click="popUpConfirmSubmit = true">
                Pelaksanaan
            </x-ts:button>
            {{-- wire:click="create()" --}}


            <div x-show="popUpConfirmSubmit" x-transition x-trap.noscroll="popUpConfirmSubmit" x-on:click.away="popUpConfirmSubmit = false" x-on:keydown.escape.window="popUpConfirmSubmit = false"
                x-anchor.bottom-end="$refs.addSoButton" class="absolute z-50 mt-2 w-max max-w-sm rounded-lg border border-gray-300 bg-white p-4 shadow-lg">


                <div class="flex items-center justify-between">
                    <span class="flex flex-row items-center gap-2 whitespace-nowrap text-sm font-medium text-indigo-500">
                        <x-ts:icon name="tabler.alert-circle" class="h-5 w-5" />
                        Yakin, Memulai Stok Opname ?
                    </span>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <span class="ms-8 flex flex-row items-center gap-2 whitespace-nowrap text-wrap text-xs font-light text-gray-500">
                        Saat opname berlangsung, kegiatan transaksi gudang akan ditutup.
                    </span>
                </div>
                <!-- Actions -->
                <div class="mt-4 flex justify-end gap-2">
                    <x-ts:button outline xs color="red" x-on:click="popUpConfirmSubmit = false">
                        Tidak
                    </x-ts:button>

                    {{-- button action validasi --}}
                    <x-ts:button xs loading="create" outline sm color="green" x-on:click="$wire.create()">
                        Ya, Mulai
                    </x-ts:button>
                </div>

            </div>
        </div>
    </div>

    <div class="rounded-lg bg-white px-4 py-2">
        <livewire:StokOpname.TablePelaksanaan :key="'table-stok-opname' . Str::random(3)" />
    </div>
</div>
