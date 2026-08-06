<div>
    <div class="flex flex-col gap-4">

        <div class="flex flex-row gap-2 border border-gray-200 border-s-4 border-s-indigo-500 px-4 py-2">
            <div class="flex items-center">
                Stok tersedia saat ini :
                <span class="text-indigo-500 font-semibold">
                    &nbsp; {{ $stoks->sum('stok') }}
                </span>
            </div>
        </div>

        <table class="min-w-full table-fixed border-collapses overflow-y-auto overflow-x-auto">
            <thead>
                <tr class="text-left text-sm text-gray-600 border-b">
                    <th class="py-2 px-4">No.</th>
                    <th class="py-2 px-4">Stok Id</th>
                    <th class="py-2 px-4">Tgl Masuk</th>
                    <th class="py-2 px-4">Jumlah Masuk</th>
                    <th class="py-2 px-4">Lokasi</th>
                    <th class="py-2 px-4">Distribusikan</th>
                    <th class="py-2 px-4">Terakhir Distribusi</th>
                    <th class="py-2 px-4">Sisa Stok</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stoks as $index => $item)
                    <tr class="text-left text-sm text-gray-600 border-b even:bg-gray-200/25" :key="{{ $index }}">
                        <td class="py-2 px-4">{{ ($stoks->currentPage() - 1) * $stoks->perPage() + $loop->iteration }}</td>
                        <td class="py-2 px-4">{{ $item->id }}</td>
                        <td class="py-2 px-4">{{ $item->penerimaanDet->penerimaan->tanggal }}</td>
                        <td class="py-2 px-4">{{ $item->jumlah_masuk_aktual }}</td>
                        <td class="py-2 px-4">
                            <div class="flex gap-2 items-center">
                                <!-- Set Lokasi -->
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="flex items-center gap-1 text-indigo-500 hover:text-indigo-700">
                                        <span class="text-xs">
                                            {{ $item->penyimpanan ? $item->penyimpanan->nama : 'Belum diset' }}
                                            @if($item->lemari)
                                                <br><span class="text-gray-400">({{ $item->lemari->nama_lemari }})</span>
                                            @endif
                                        </span>
                                        <x-ts:icon name="tabler.edit" class="w-3 h-3" />
                                    </button>
                                    
                                    <div x-show="open" @click.outside="open = false" style="display: none;" class="absolute z-10 w-64 p-3 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg">
                                        <div class="text-xs font-semibold mb-2">Set Lokasi Utama</div>
                                        <div x-data="{ selectedRuangan: '{{ $item->penyimpanan_id }}', selectedLemari: '{{ $item->lemari_id }}' }">
                                            <select x-model="selectedRuangan" class="w-full mb-2 text-xs border-gray-300 rounded-md">
                                                <option value="">Pilih Ruangan</option>
                                                @foreach($penyimpanans as $p)
                                                    <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                                @endforeach
                                            </select>
                                            
                                            <select x-model="selectedLemari" class="w-full mb-2 text-xs border-gray-300 rounded-md" x-show="selectedRuangan">
                                                <option value="">Pilih Lemari (Opsional)</option>
                                                @foreach($penyimpanans as $p)
                                                    <optgroup label="{{ $p->nama }}" x-show="selectedRuangan == {{ $p->id }}">
                                                        @foreach($p->lemaris as $l)
                                                            <option value="{{ $l->id }}">{{ $l->nama_lemari }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                            
                                            <div class="flex justify-end gap-1 mt-2">
                                                <button @click="open = false" type="button" class="px-2 py-1 text-xs text-gray-500 bg-gray-100 rounded">Batal</button>
                                                <button @click="$wire.setLokasi({{ $item->id }}, selectedRuangan, selectedLemari); open = false" type="button" class="px-2 py-1 text-xs text-white bg-indigo-500 rounded">Simpan</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pecah Lokasi -->
                                @if($item->stok > 1)
                                <div x-data="{ openPecah: false }" class="relative">
                                    <button @click="openPecah = !openPecah" class="flex items-center text-orange-500 hover:text-orange-700" title="Bagi Stok ke Lokasi Lain">
                                        <x-ts:icon name="tabler.copy" class="w-4 h-4" />
                                    </button>

                                    <div x-show="openPecah" @click.outside="openPecah = false" style="display: none;" class="absolute z-10 w-64 p-3 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg left-full ml-2">
                                        <div class="text-xs font-semibold mb-2">Bagi Stok ke Lokasi Lain</div>
                                        <div x-data="{ qty: 1, splitRuangan: '', splitLemari: '' }">
                                            <div class="mb-2">
                                                <label class="text-[10px] text-gray-500">Jumlah dipisah (Max {{ $item->stok - 1 }})</label>
                                                <input type="number" x-model="qty" min="1" max="{{ $item->stok - 1 }}" class="w-full text-xs border-gray-300 rounded-md">
                                            </div>

                                            <select x-model="splitRuangan" class="w-full mb-2 text-xs border-gray-300 rounded-md">
                                                <option value="">Pilih Ruangan Tujuan</option>
                                                @foreach($penyimpanans as $p)
                                                    <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                                @endforeach
                                            </select>
                                            
                                            <select x-model="splitLemari" class="w-full mb-2 text-xs border-gray-300 rounded-md" x-show="splitRuangan">
                                                <option value="">Pilih Lemari (Opsional)</option>
                                                @foreach($penyimpanans as $p)
                                                    <optgroup label="{{ $p->nama }}" x-show="splitRuangan == {{ $p->id }}">
                                                        @foreach($p->lemaris as $l)
                                                            <option value="{{ $l->id }}">{{ $l->nama_lemari }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                            
                                            <div class="flex justify-end gap-1 mt-2">
                                                <button @click="openPecah = false" type="button" class="px-2 py-1 text-xs text-gray-500 bg-gray-100 rounded">Batal</button>
                                                <button @click="$wire.pecahStok({{ $item->id }}, qty, splitRuangan, splitLemari); openPecah = false" type="button" class="px-2 py-1 text-xs text-white bg-orange-500 rounded">Pecah</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="py-2 px-4">
                            <div class="flex gap-2 items-center">
                                <!-- Set Lokasi -->
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="flex items-center gap-1 text-indigo-500 hover:text-indigo-700">
                                        <span class="text-xs">
                                            {{ $item->penyimpanan ? $item->penyimpanan->nama : 'Belum diset' }}
                                            @if($item->lemari)
                                                <br><span class="text-gray-400">({{ $item->lemari->nama_lemari }})</span>
                                            @endif
                                        </span>
                                        <x-ts:icon name="tabler.edit" class="w-3 h-3" />
                                    </button>
                                    
                                    <div x-show="open" @click.outside="open = false" style="display: none;" class="absolute z-10 w-64 p-3 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg">
                                        <div class="text-xs font-semibold mb-2">Set Lokasi Utama</div>
                                        <div x-data="{ selectedRuangan: '{{ $item->penyimpanan_id }}', selectedLemari: '{{ $item->lemari_id }}' }">
                                            <select x-model="selectedRuangan" class="w-full mb-2 text-xs border-gray-300 rounded-md">
                                                <option value="">Pilih Ruangan</option>
                                                @foreach($penyimpanans as $p)
                                                    <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                                @endforeach
                                            </select>
                                            
                                            <select x-model="selectedLemari" class="w-full mb-2 text-xs border-gray-300 rounded-md" x-show="selectedRuangan">
                                                <option value="">Pilih Lemari (Opsional)</option>
                                                @foreach($penyimpanans as $p)
                                                    <optgroup label="{{ $p->nama }}" x-show="selectedRuangan == {{ $p->id }}">
                                                        @foreach($p->lemaris as $l)
                                                            <option value="{{ $l->id }}">{{ $l->nama_lemari }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                            
                                            <div class="flex justify-end gap-1 mt-2">
                                                <button @click="open = false" type="button" class="px-2 py-1 text-xs text-gray-500 bg-gray-100 rounded">Batal</button>
                                                <button @click="$wire.setLokasi({{ $item->id }}, selectedRuangan, selectedLemari); open = false" type="button" class="px-2 py-1 text-xs text-white bg-indigo-500 rounded">Simpan</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pecah Lokasi -->
                                @if($item->stok > 1)
                                <div x-data="{ openPecah: false }" class="relative">
                                    <button @click="openPecah = !openPecah" class="flex items-center text-orange-500 hover:text-orange-700" title="Bagi Stok ke Lokasi Lain">
                                        <x-ts:icon name="tabler.copy" class="w-4 h-4" />
                                    </button>

                                    <div x-show="openPecah" @click.outside="openPecah = false" style="display: none;" class="absolute z-10 w-64 p-3 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg left-full ml-2">
                                        <div class="text-xs font-semibold mb-2">Bagi Stok ke Lokasi Lain</div>
                                        <div x-data="{ qty: 1, splitRuangan: '', splitLemari: '' }">
                                            <div class="mb-2">
                                                <label class="text-[10px] text-gray-500">Jumlah dipisah (Max {{ $item->stok - 1 }})</label>
                                                <input type="number" x-model="qty" min="1" max="{{ $item->stok - 1 }}" class="w-full text-xs border-gray-300 rounded-md">
                                            </div>

                                            <select x-model="splitRuangan" class="w-full mb-2 text-xs border-gray-300 rounded-md">
                                                <option value="">Pilih Ruangan Tujuan</option>
                                                @foreach($penyimpanans as $p)
                                                    <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                                @endforeach
                                            </select>
                                            
                                            <select x-model="splitLemari" class="w-full mb-2 text-xs border-gray-300 rounded-md" x-show="splitRuangan">
                                                <option value="">Pilih Lemari (Opsional)</option>
                                                @foreach($penyimpanans as $p)
                                                    <optgroup label="{{ $p->nama }}" x-show="splitRuangan == {{ $p->id }}">
                                                        @foreach($p->lemaris as $l)
                                                            <option value="{{ $l->id }}">{{ $l->nama_lemari }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                            
                                            <div class="flex justify-end gap-1 mt-2">
                                                <button @click="openPecah = false" type="button" class="px-2 py-1 text-xs text-gray-500 bg-gray-100 rounded">Batal</button>
                                                <button @click="$wire.pecahStok({{ $item->id }}, qty, splitRuangan, splitLemari); openPecah = false" type="button" class="px-2 py-1 text-xs text-white bg-orange-500 rounded">Pecah</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="py-2 px-4">
                            <span role="button" class="flex flex-row gap-2 items-center" wire:model.live='stokId'
                                x-on:click="$wire.set('stokId',{{ $item->id }});$dispatch('open-modal', {id:'modal-distribusi-per-stok'})">
                                {{ $item->distribusiDetails->sum('jml') }}
                                <x-ts:icon name="tabler.external-link" class="w-3 h-3" />
                            </span>
                        </td>
                        <td class="py-2 px-4">
                            @php
                                $terakhirKeluar = '-';
                                if ($item->distribusiDetails->last()?->created_at) {
                                    $terakhirKeluar = \Carbon\Carbon::parse($item->distribusiDetails->last()?->created_at)->diffForHumans();
                                }
                            @endphp

                            {{ $terakhirKeluar }}
                        </td>
                        <td class="py-2 px-4 @if ($item->stok == 0) text-red-500 italic @endif">
                            {{ $item->stok == 0 ? 'Habis' : $item->stok }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-2 px-4 text-center italic text-gray-400">
                            Data tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

        <div class="px-4 py-3 text-sm">
            {{ $stoks->links() }}
        </div>

    </div>


    <x-filament::modal id="modal-distribusi-per-stok" width="3xl">
        <x-slot name="heading">
            Distribusi Stok
        </x-slot>

        <div wire:loading wire:target='stokId'>
            Wait...
        </div>
        <div wire:loading.remove wire:target='stokId'>
            <livewire:Gudang.ViewStokTerdistribusi :$stokId :key="Str::random()" />
        </div>

    </x-filament::modal>
</div>
