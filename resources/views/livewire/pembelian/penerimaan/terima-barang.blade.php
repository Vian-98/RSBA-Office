<div class="flex flex-col gap-3">

    {{-- header --}}
    <div class="mb-2 grid grid-cols-2 rounded border border-gray-200 p-3">
        <div class="flex flex-col">
            <span class="font-gray-500 text-xs font-light">No. Transaksi</span>
            <h1 class="text-4xl font-bold uppercase text-gray-500">{{ $pembelian?->no ?? 'Tidak Ditemukan' }}
            </h1>
            <span class="mt-2 flex flex-row gap-2 text-[0.45rem]">
                @php
                    $status = $pembelian->status;
                    $statusBeli = fn(string $status): string => match ($status) {
                        'waiting' => 'amber',
                        'selesai' => 'green',
                        'dibatalkan' => 'red',
                        default => 'secondary',
                    };
                    $badgeStatus = $statusBeli($status);
                @endphp
                <x-ts:badge :text="ucwords($pembelian->status)" :color="$badgeStatus" outline xs />


                {{-- pembayaran --}}
                @php
                    $pembayaran = $pembelian?->status_pembayaran ?? null;
                    $statusPembayaran = fn($pembayaran) => match ($pembayaran) {
                        'lunas' => 'green',
                        'tempo' => 'amber',
                        null => 'red',
                    };
                    $badgePembayaran = $statusPembayaran($pembayaran);
                @endphp
                <x-ts:badge :text="$pembelian?->status_pembayaran ? ucfirst($pembelian->status_pembayaran) : 'Belum Dibayar'" :color="$badgePembayaran" outline xs />
            </span>
        </div>
        <div class="flex flex-col">
            <div class="flex items-center">
                <span class="w-[150px]">Supplier</span> : {{ $pembelian?->supplier->nama }}
            </div>
            <div class="flex items-center">
                <span class="w-[150px]">Tanggal Beli</span> :
                {{ Carbon\Carbon::parse($pembelian?->tgl)->translatedFormat('d M Y') }}
            </div>
            <div class="flex items-center">
                <span class="w-[150px]">Jenis</span> :
                {{ $pembelian->jenis }}
            </div>
        </div>
    </div>


    {{-- form barng diterim --}}
    <form wire:submit.prevent='submit' autocomplete="off">
        <div class="flex flex-col gap-2">
            <span class="font-semibold text-indigo-500">Barang Yang Diterima </span>
            <div class="grid grid-cols-4 gap-2">
                <x-ts:date wire:model.lazy='tgl_diterima' placeholder="Tgl Diterima" />

                <x-ts:input wire:model.lazy='no_invoice' :disabled="$penerimaan ? true : false" placeholder="No. Invoice / Faktur / No. Nota" />

                <x-ts:input wire:model.lazy='keterangan' placeholder="Keterangan" />

            </div>

            {{-- init data menggunakan alpine js --}}
            <div x-data="{
                subtotals: @entangle('terimaBarang'),
                totalItem: 0,
                totalBeli: 0,
                totalPpn: 0,
                totalDiskon: 0,
                itemDiskon: 0,
                itemPpn: 0,
            
                calculateSubtotal(index) {
                    const item = this.subtotals[index];
            
                    // validasi jumlah diterima
                    const maxDiterima = Number(item.remainingQuantity || 0);
                    if (item.jumlahDiterima > maxDiterima) {
                        item.jumlahDiterima = maxDiterima;
                    }
            
                    if (item.jumlahDiterima < 0) {
                        item.jumlahDiterima = 0;
                    }
            
            
                    let qtyDiterima = Number(item.jumlahDiterima || 0);
                    let harga = Number(item.hargaSatuan || 0);
                    let diskon = Number(item.diskon || 0);
                    let ppn = Number(item.ppn || 0);
            
                    let hargaTotal = qtyDiterima * harga;
                    let hargaNet = hargaTotal - diskon;
                    let ppnAmount = hargaNet * (ppn / 100);
            
                    item.ppnAmount = ppnAmount;
                    item.subtotal = hargaTotal;
                    this.recalcSummary();
                },
            
                recalcSummary() {
                    this.totalItem = 0;
                    this.totalBeli = 0;
                    this.totalDiskon = 0;
                    this.totalPpn = 0;
            
                    this.subtotals.forEach(item => {
                        let qty = Number(item.jumlahDiterima || 0);
                        let diskon = Number(item.diskon || 0);
            
                        this.totalItem += qty;
                        this.totalBeli += Number(item.subtotal || 0);
                        this.totalDiskon += diskon;
                        this.totalPpn += Number(item.ppnAmount || 0);
                    });
            
                    this.totalHarga = this.totalBeli + this.totalPpn;
                    this.itemDiskon = this.subtotals.filter(item => Number(item.diskon) > 0).length;
                    this.itemPpn = this.subtotals.filter(item => Number(item.ppn) > 0).length;
                },
            
                formatCurrency(value) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(value);
                },
            }" class="w-full space-y-2 overflow-auto">
                {{-- end init data --}}

                <table class="border-collapses w-full">
                    <colgroup>
                        <col style="width: 4%">
                        <col style="width: 6%">
                        <col style="width: 18%">
                        <col style="width: 6%">
                        <col style="width: 6%">
                        <col style="width: 7%">
                        <col style="width: 7%">
                        <col style="width: 10%">
                        <col style="width: 10%">
                        <col style="width: 6%">
                        <col style="width: 11%">
                        <col style="width: 2%">
                    </colgroup>

                    <thead>
                        <tr class="border-b text-left text-xs font-thin text-gray-600">
                            <th class="p-2">No.</th>
                            <th class="p-2">Tipe</th>
                            <th class="p-2">Barang</th>
                            <th class="p-2">Satuan</th>
                            <th class="p-2">Dipesan</th>
                            <th class="p-2">Telah Diterima</th>
                            <th class="p-2">Diterima Skr</th>
                            <th class="p-2">Harga Satuan</th>
                            <th class="p-2">Diskon <span class="block text-[10px] font-thin">(Total)</span></th>
                            <th class="p-2">PPN</th>
                            <th class="p-2">Sub Total <span class="block text-[10px] font-thin">(Qty - Diskon)</th>
                            <th class="text-right"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($pembelian->details as $index => $item)
                            <tr class="border-b border-dashed text-sm even:bg-gray-100/75 hover:bg-indigo-100" :key="{{ $index }}">
                                <td class="p-2">{{ $loop->iteration }}</td>
                                <td class="p-2">{{ $item->barang->bhp ? 'BHP' : 'Barang' }}</td>
                                <td class="p-2">{{ $item->barang->nama }}</td>
                                <td class="p-2">{{ $item->barang->satuan->nama }}</td>
                                <td class="p-2">{{ $item->jumlah }}</td>
                                <td class="p-2">{{ $item->terimas?->sum('jumlah') }}</td>

                                {{-- optimize pakai alpine --}}
                                @php
                                    $sisaBlmDiterima = (int) $item->jumlah - (int) $item->terimas?->sum('jumlah');
                                @endphp
                                @if ($sisaBlmDiterima > 0)
                                    {{-- Diterima --}}
                                    <td class="p-2">
                                        <input required type="number" x-model.number="subtotals[{{ $index }}].jumlahDiterima" x-on:input="calculateSubtotal({{ $index }})"
                                            min="0" max="{{ $sisaBlmDiterima }}" class="h-8 max-w-24 rounded-lg border border-gray-100" placeholder="Diterima" />

                                        @error('any')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </td>

                                    {{-- Harga Satuan --}}
                                    <td class="p-2">
                                        <input required type="number" x-model.number="subtotals[{{ $index }}].hargaSatuan" x-on:input="calculateSubtotal({{ $index }})" min="0"
                                            x-bind:disabled="subtotals[{{ $index }}].is_received ? true : false" class="h-8 max-w-32 rounded-lg border border-gray-100"
                                            placeholder="Harga Satuan" />

                                        @error('any')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </td>

                                    <td class="p-2">
                                        <input required type="number" x-model.number="subtotals[{{ $index }}].diskon" x-on:input="calculateSubtotal({{ $index }})" min="0"
                                            x-bind:disabled="subtotals[{{ $index }}].is_received ? true : false" class="h-8 max-w-24 rounded-lg border border-gray-100" placeholder="Diskon" />
                                    </td>

                                    <td class="p-2">
                                        <input required type="number" x-model.number="subtotals[{{ $index }}].ppn" x-on:input="calculateSubtotal({{ $index }})" min="0"
                                            x-bind:disabled="subtotals[{{ $index }}].is_received ? true : false" class="h-8 max-w-16 rounded-lg border border-gray-100" placeholder="%" />
                                    </td>

                                    {{-- sub total --}}
                                    <td class="p-2">
                                        <span x-text="formatCurrency(subtotals[{{ $index }}].subtotal || 0)"></span>
                                    </td>

                                    <td x-data="{ open: false, style: '' }" class="text-right">
                                        {{-- tombol action garansi dan batch --}}
                                        <x-tabler-label title="Batch / Serial" class="h-5 w-auto cursor-pointer text-indigo-500" role="button"
                                            x-on:click="let r = $el.getBoundingClientRect(); style = 'top:'+(r.bottom+6)+'px; left:'+(r.left-180)+'px'; open = !open;" />

                                        <!-- Tooltip Batch dan Garansi -->
                                        <div x-show="open" x-transition.scale.origin.top @click.outside="open = false" class="fixed right-0 z-50 w-56 rounded-lg border bg-white p-3 shadow-lg"
                                            :style="style">

                                            <!-- arrow -->
                                            <div class="absolute -top-1 right-4 h-2 w-2 rotate-45 border-l border-t bg-white"></div>

                                            <div class="space-y-2">
                                                <div>
                                                    <label class="text-[11px] text-gray-500">
                                                        Batch / Serial
                                                    </label>
                                                    <input type="text" x-model="subtotals[{{ $index }}].batch" class="h-7 w-full rounded-lg border border-gray-100 text-xs"
                                                        placeholder="Batch / Serial Numbers" />
                                                </div>

                                                <div>
                                                    <label class="text-[11px] text-gray-500">
                                                        Exp Date / Warranty
                                                    </label>
                                                    <input type="date" x-model="subtotals[{{ $index }}].waranty_date" class="h-7 w-full rounded-lg border border-gray-100 text-xs"
                                                        placeholder="Exp Date / Waranty Date" />
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                @else
                                    <td colspan="6" class="p-2">
                                        <x-ts:badge color="teal">Sudah diterima</x-ts:badge>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-4 py-2 text-center text-gray-500">
                                    Tidak ada pesanan barang.
                                </td>
                            </tr>
                        @endforelse



                    </tbody>
                </table>

                {{-- summary total --}}
                <div class="grid w-full grid-cols-2 rounded-md border border-indigo-200 bg-indigo-100/75 px-4 py-2 lg:grid-cols-2">
                    <div class="flex flex-col">
                        <span class="text-xs font-thin italic text-gray-500">Item:</span>
                        <span class="text-xl font-semibold text-indigo-500" x-text="totalItem"></span>
                    </div>

                    <div class="col-span-2 flex w-full flex-col lg:col-span-1">
                        <div class="flex flex-col justify-end text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="text-gray-800" x-text="`Rp ${totalBeli.toLocaleString()}`"></span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-600">Diskon</span>
                                <span class="text-red-500" x-text="`(${itemDiskon})` +  ` Rp ${totalDiskon.toLocaleString()}`"></span>
                            </div>

                            <div class="flex justify-between border-t-2 border-dashed border-gray-200 font-semibold">
                                <span class="text-gray-600">Subtotal setelah diskon</span>
                                <span class="text-gray-800" x-text="`Rp ${(totalBeli - totalDiskon).toLocaleString()}`"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">PPN</span>
                                <span class="text-gray-800" x-text="`(${itemPpn}) Rp ${totalPpn.toLocaleString()}`"></span>
                            </div>
                            <div class="flex justify-between border-t-2 border-dashed border-indigo-200 pt-1">
                                <span class="text-base font-bold text-indigo-700">Total Pembayaran</span>
                                <span class="text-xl font-bold text-indigo-600" x-text="`Rp ${((totalBeli - totalDiskon) + totalPpn).toLocaleString()}`"></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="ml-auto flex justify-end gap-2">

                <x-ts:button type="button" sm color="red" x-on:click="$dispatch('tutup-modal-terima')">Batal</x-ts:button>
                {{-- button cancel terima barang --}}

                {{-- simpan action --}}
                <div class="relative">
                    <div x-data="{
                        waitOrDone: false,
                    }" class="relative">

                        <x-ts:button sm icon="tabler.checks" x-on:click="waitOrDone = true">
                            Simpan
                        </x-ts:button>

                        <!-- Tooltip Modal -->
                        <div class="border-indgo-300 absolute right-0 z-50 mt-2 max-w-fit rounded-lg border bg-white p-4 shadow-lg" x-show="waitOrDone" x-transition x-trap.noscroll="waitOrDone"
                            x-on:click.away="waitOrDone = false" x-on:keydown.escape.window="waitOrDone = false">

                            <!-- Tooltip Header -->
                            <div class="mb-3 flex items-center justify-between text-sm">
                                <span class="flex flex-row items-center gap-2 whitespace-nowrap font-medium text-indigo-500">
                                    <x-ts:icon name="tabler.shopping-cart-plus" class="h-5 w-5" />
                                    Konfirmasi Penerimaan Barang
                                </span>
                                <span role="button" x-on:click="waitOrDone = false" class="text-gray-400 hover:text-gray-600">
                                    &times;
                                </span>
                            </div>

                            <!-- Options -->
                            <div class="flex items-center justify-between gap-4">
                                <span class="flex flex-row items-center gap-2 whitespace-nowrap text-xs font-light text-gray-500">
                                    Masih menunggu pengiriman selanjutnya, atau selesaikan transaksi sekarang ?
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="mt-4 flex justify-end gap-2">
                                <x-ts:button outline xs icon="tabler.file-isr" wire:click="submit('sebagian')" loading="submit('sebagian')">
                                    Simpan, Tunggu Berikutnya
                                </x-ts:button>
                                <x-ts:button outline xs color="green" icon="tabler.checks" wire:click="submit('selesai')" loading="submit('selesai')">
                                    Simpan, Selesaikan Sekarang
                                </x-ts:button>
                            </div>
                        </div>
                        {{-- end Tooltip Modal --}}

                    </div>
                </div>
                {{-- end simpan action --}}

            </div>
        </div>

    </form>

</div>
