@php
    $tglDistribusi = Carbon\Carbon::parse($distribusi->tanggal)->translatedFormat('d M Y');

    $keluarAs = $distribusi->dist_as;
    $colorKeluarAs = fn($keluarAs) => match ($keluarAs) {
        'keluar' => 'red',
        'asset' => 'green',
        null => 'base',
    };
    $colorKeluarAs = $colorKeluarAs($keluarAs);

    // label
    $labelKeluar = fn($keluarAs) => match ($keluarAs) {
        'keluar' => 'Pengeluaran',
        'asset' => 'Sebagai Aset',
        null => '-',
    };
    $labelKeluar = $labelKeluar($keluarAs);
@endphp

<div x-data="pencarian" class="flex flex-col gap-2">
    <div class="mb-2 grid grid-cols-2 rounded border border-gray-200 p-3">
        <div class="flex flex-col">
            {{-- <span class="text-xs font-light font-gray-500">ID Transaksi</span> --}}
            <h1 class="text-2xl font-bold uppercase text-gray-500">{{ $distribusi->id }}</h1>

            {{-- footer --}}
            <span class="mt-2 flex flex-row items-center gap-2 text-[0.45rem]">
                <span>{{ $tglDistribusi }}</span>
                <x-ts:badge :text="$labelKeluar" :color="$colorKeluarAs" outline xs />
            </span>
        </div>
        <div class="flex flex-col">
            <div class="flex items-center">
                <span class="w-[150px]">Ke</span> : {{ $distribusi->ruangan->nama }}
            </div>
            <div class="flex items-center">
                <span class="w-[150px]">Diterima Oleh</span> : {{ $distribusi->pengirim_nama }}
            </div>

        </div>
    </div>

    <div class="">
        <table class="border-collapses min-w-full table-fixed overflow-auto">
            <thead class="border-b text-left text-sm capitalize text-gray-600">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">SKU</th>
                    <th class="px-4 py-2">Barang</th>
                    <th class="px-4 py-2">Jumlah</th>
                    <th class="px-4 py-2">Satuan</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($details as $index => $item)
                    <tr class="border-b text-left text-sm even:bg-gray-200/25 hover:bg-indigo-100/50" :key="{{ $index }}">
                        <td class="px-4 py-2">
                            <x-ts:checkbox color="orange" x-on:click="addCartToReturn({{ $item->id }})" />
                        </td>
                        <td class="px-4 py-2">{{ $item->stoks->barang->sku }}</td>
                        <td class="px-4 py-2">{{ $item->stoks->barang->nama }}</td>
                        <td class="px-4 py-2">{{ $item->jml }}</td>
                        <td class="px-4 py-2">{{ $item->stoks->barang->satuan->nama }}</td>
                    </tr>
                @empty
                    <tr class="border-b text-left text-sm text-gray-600 even:bg-gray-200/25">
                        <td class="px-4 py-2 text-center italic" colspan="{{ count($headers) + 1 }}">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex flex-row justify-end gap-2">
        <x-ts:button sm color="primary" icon="tabler.printer" x-on:click="printArea('print-distribusi')">
            Print
        </x-ts:button>


        <x-ts:button sm color="orange" x-show="cartToReturn.length != 0">
            (<span x-text="cartToReturn.length"></span>)
            <x-ts:icon name="tabler.arrow-back-up-double" class="h-5 w-5" />
            Return Ke Gudang
        </x-ts:button>
    </div>

    {{-- print area --}}
    <div id="print-distribusi" class="hidden">
        <livewire:Distribusi.PrintDistribusi :$distribusi />
    </div>

</div>
@script
    <script>
        Alpine.data('pencarian', () => {
            return {
                cartToReturn: [],

                addCartToReturn(id) {
                    // console.log(id);
                    const existingItem = this.cartToReturn.find(item => item.id === id);
                    if (existingItem) {
                        //remove from cartToReturn
                        this.cartToReturn = this.cartToReturn.filter(item => item.id !== id);
                    } else {
                        //push to cartToReturn
                        const newItem = {
                            id: id
                        };

                        this.cartToReturn.push(newItem);
                    }


                },


                // print() {
                //     let printContents = document.getElementById('printArea').innerHTML;
                //     let originalContents = document.body.innerHTML;

                //     document.body.innerHTML = printContents; // Replace page content with print content
                //     window.print(); // Show print dialog
                //     document.body.innerHTML = originalContents; // Restore original content after printing

                //     location.reload(); // Reload to restore Livewire functionality
                // }
            }
        });
    </script>
@endscript
