<div class="flex flex-col gap-3">
    {{-- title --}}


    {{-- asset identitas --}}

    {{-- request maintenance --}}
    <div class="flex flex-col gap-2 text-gray-600">
        <div class="text-lg font-semibold">Permintaan </div>
        <div class="ms-4 flex flex-col gap-2">
            <div class="font-semibold text-indigo-500">Request Number: #{{ $work->jadwal->request->id }}</div>
            <div class="space-y-1 text-sm">
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Tanggal</span>
                    <span class="ml-2">: {{ \Carbon\Carbon::parse($work->jadwal->request->created_at)->translatedFormat('d M Y') }}</span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">User Pengaju</span>
                    <span class="ml-2">: {{ $work->jadwal->request->user_request }}</span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Keterangan</span>
                    <span class="ml-2">: {{ $work->jadwal->request->note }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- pengerjaan maintenance --}}
    <div class="flex flex-col gap-2">
        <div class="text-lg font-semibold">Pengerjaan</div>
        @if ($work)
            <div class="ms-4 flex flex-col gap-2 text-sm text-gray-600">
                <div class="font-semibold text-indigo-500">Work Order Number: #{{ $work->id }} </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Teknisi</span>
                    <span class="ml-2">: @php
                        $work->jadwal->teknisi->each(function ($teknisi) {
                            echo "{$teknisi->user->karyawan->nama} [{$teknisi->role}], ";
                        });
                    @endphp </span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Tgl Mulai</span>
                    <span class="ml-2">: {{ \Carbon\Carbon::parse($work->mulai)->translatedFormat('d M Y H:i') }} </span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Tgl Selesai</span>
                    <span class="ml-2">: {{ \Carbon\Carbon::parse($work->selesai)->translatedFormat('d M Y H:i') }} </span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Durasi </span>
                    <span class="ml-2">: {{ \Carbon\Carbon::parse($work->mulai)->diff(\Carbon\Carbon::parse($work->selesai))->locale('id')->forHumans() }} </span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Diselesaikan</span>
                    <span class="ml-2">: {{ $work->user_selesai }} </span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Keterangan</span>
                    <span class="ml-2">: {{ $work->catatan }} </span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 flex-shrink-0 text-nowrap">Total Cost</span>
                    <span class="ml-2">: {{ formatRupiah($work->total_biaya, withDecimals: false) }} </span>
                </div>

            </div>

            {{-- daftar item parts / penggunaan BHP --}}
            <div class="ms-4 flex flex-col gap-2">
                <div class="font-semibold text-gray-500">Penggantian Parts / Komponen / Penggunaan BHP</div>
                @if ($work->parts->count() > 0)
                    <ul class="ms-2 list-disc pl-5 text-sm">
                        @foreach ($work->parts as $part)
                            <li class="text-gray-600">{{ $part->barang->nama }} ({{ number_format($part->qty) . $part->barang->satuan->nama }})</li>
                        @endforeach
                    </ul>
                @else
                    <div class="ms-4 text-sm italic text-gray-600">Tidak ada data</div>
                @endif

            </div>

            {{-- Lampiran Dokumentasi & Foto Permintaan --}}
            @php
                $requestLampirans = is_array($work->jadwal->request->lampiran ?? null) 
                    ? $work->jadwal->request->lampiran 
                    : (is_string($work->jadwal->request->lampiran ?? null) ? (json_decode($work->jadwal->request->lampiran, true) ?? []) : []);
                $workDokumentasi = is_array($work->dokumentasi ?? null) 
                    ? $work->dokumentasi 
                    : (is_string($work->dokumentasi ?? null) ? (json_decode($work->dokumentasi, true) ?? []) : []);
                $allLampirans = array_merge($requestLampirans, $workDokumentasi);

                $formattedImages = array_map(function($img) {
                    return (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, '/storage/')) 
                        ? $img 
                        : Storage::url($img);
                }, $allLampirans);
            @endphp
            @if (count($formattedImages) > 0)
                <div class="ms-4 flex flex-col gap-2 mt-3"
                     x-data="{
                         showLightbox: false,
                         currentIndex: 0,
                         images: {{ json_encode(array_values($formattedImages)) }},
                         openImage(index) {
                             this.currentIndex = index;
                             this.showLightbox = true;
                         },
                         closeImage() {
                             this.showLightbox = false;
                         },
                         next() {
                             if (this.images.length === 0) return;
                             this.currentIndex = (this.currentIndex + 1) % this.images.length;
                         },
                         prev() {
                             if (this.images.length === 0) return;
                             this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
                         }
                     }"
                     @keyup.arrow-right.window="if(showLightbox) next()"
                     @keyup.d.window="if(showLightbox) next()"
                     @keyup.arrow-left.window="if(showLightbox) prev()"
                     @keyup.a.window="if(showLightbox) prev()"
                     @keyup.escape.window="if(showLightbox) closeImage()">
                    
                    <div class="font-semibold text-gray-500">Lampiran Foto & Dokumentasi</div>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($formattedImages as $index => $imgUrl)
                            <button type="button" 
                                    @click="openImage({{ $index }})" 
                                    class="group relative block border border-gray-200 rounded-lg overflow-hidden hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <img src="{{ $imgUrl }}" class="h-28 w-28 object-cover" alt="Dokumentasi Maintenance #{{ $index + 1 }}" />
                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                                    <x-ts:icon name="tabler.zoom-in" class="h-6 w-6" />
                                </div>
                            </button>
                        @endforeach
                    </div>

                    {{-- Lightbox Modal --}}
                    <template x-teleport="body">
                        <div x-show="showLightbox" 
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/90 p-4 backdrop-blur-md"
                             @click="closeImage()">

                            {{-- Close Button --}}
                            <button type="button" 
                                    @click.stop="closeImage()" 
                                    class="absolute top-5 right-5 z-20 rounded-full bg-white/10 hover:bg-white/20 p-2.5 text-white transition-all hover:scale-110 focus:outline-none"
                                    title="Tutup (Esc)">
                                <x-ts:icon name="tabler.x" class="h-7 w-7" />
                            </button>

                            {{-- Counter Badge --}}
                            <div class="absolute top-5 left-5 z-20 flex items-center gap-2 rounded-full bg-black/60 px-4 py-1.5 text-sm font-semibold text-white/90 backdrop-blur-sm border border-white/10">
                                <x-ts:icon name="tabler.photo" class="h-4 w-4 text-indigo-400" />
                                <span><span x-text="currentIndex + 1"></span> / <span x-text="images.length"></span></span>
                            </div>

                            {{-- Left Arrow / Previous --}}
                            <button type="button" 
                                    x-show="images.length > 1" 
                                    @click.stop="prev()" 
                                    class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 z-20 rounded-full bg-black/60 hover:bg-black/80 border border-white/10 p-3 text-white transition-all hover:scale-110 focus:outline-none"
                                    title="Sebelumnya (A / ←)">
                                <x-ts:icon name="tabler.chevron-left" class="h-8 w-8" />
                            </button>

                            {{-- Right Arrow / Next --}}
                            <button type="button" 
                                    x-show="images.length > 1" 
                                    @click.stop="next()" 
                                    class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 z-20 rounded-full bg-black/60 hover:bg-black/80 border border-white/10 p-3 text-white transition-all hover:scale-110 focus:outline-none"
                                    title="Selanjutnya (D / →)">
                                <x-ts:icon name="tabler.chevron-right" class="h-8 w-8" />
                            </button>

                            {{-- Active Image Preview Container --}}
                            <div class="relative max-h-[85vh] max-w-[85vw] flex items-center justify-center" @click.stop>
                                <img :src="images[currentIndex]" 
                                     class="max-h-[82vh] max-w-[82vw] rounded-xl object-contain shadow-2xl transition-all duration-200 border border-white/10" 
                                     alt="Dokumentasi Maintenance Detail" />
                            </div>

                            {{-- Keyboard Shortcut Helper Footer --}}
                            <div class="absolute bottom-5 z-20 hidden sm:flex items-center gap-4 text-xs font-medium text-white/70 bg-black/60 border border-white/10 px-4 py-2 rounded-full backdrop-blur-md">
                                <span class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-white/20 rounded font-mono text-[10px]">A</kbd> / <kbd class="px-1.5 py-0.5 bg-white/20 rounded font-mono text-[10px]">←</kbd> Sebelum</span>
                                <span>•</span>
                                <span class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-white/20 rounded font-mono text-[10px]">D</kbd> / <kbd class="px-1.5 py-0.5 bg-white/20 rounded font-mono text-[10px]">→</kbd> Sesudah</span>
                                <span>•</span>
                                <span class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-white/20 rounded font-mono text-[10px]">ESC</kbd> Tutup</span>
                            </div>
                        </div>
                    </template>
                </div>
            @endif
        @else
            <div class="ms-4 italic text-gray-600">Belum ada pengerjaan.</div>
        @endif

    </div>
</div>
