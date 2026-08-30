@php
    $images = [];
    if (!empty($request->lampiran)) {
        foreach ($request->lampiran as $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                $images[] = Storage::url($file);
            }
        }
    }
@endphp
<div class="flex flex-col gap-4" x-data="{ 
    showPreview: false, 
    previewSrc: '', 
    images: {{ json_encode($images) }},
    currentIndex: 0,
    openPreview(src) {
        this.previewSrc = src;
        this.currentIndex = this.images.indexOf(src);
        this.showPreview = true;
    },
    nextImage() {
        if (this.images.length === 0) return;
        this.currentIndex = (this.currentIndex + 1) % this.images.length;
        this.previewSrc = this.images[this.currentIndex];
    },
    prevImage() {
        if (this.images.length === 0) return;
        this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        this.previewSrc = this.images[this.currentIndex];
    }
}">

    {{-- ─── Header Tiket ─────────────────────────────────────── --}}
    <div class="rounded-xl bg-white px-6 py-4 shadow-sm border border-gray-100">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-lg font-bold text-indigo-600">{{ $request->nomor_tiket }}</span>

                    {{-- Status Badge --}}
                    @php
                        $statusColors = ['open'=>'orange','rejected'=>'red','assigned'=>'blue','in_progress'=>'indigo','resolved'=>'green'];
                        $statusLabels = ['open'=>'Open','rejected'=>'Rejected','assigned'=>'Assigned','in_progress'=>'In Progress','resolved'=>'Resolved'];
                        $st = $request->ticket_status;
                    @endphp
                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-0.5 text-xs font-semibold
                        {{ $st === 'resolved' ? 'bg-green-100 text-green-700' :
                           ($st === 'in_progress' ? 'bg-indigo-100 text-indigo-700' :
                           ($st === 'assigned' ? 'bg-blue-100 text-blue-700' :
                           ($st === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700'))) }}">
                        <span class="h-1.5 w-1.5 rounded-full animate-pulse
                            {{ $st === 'resolved' ? 'bg-green-500' :
                               ($st === 'in_progress' ? 'bg-indigo-500' :
                               ($st === 'assigned' ? 'bg-blue-500' :
                               ($st === 'rejected' ? 'bg-red-500' : 'bg-orange-400'))) }}">
                        </span>
                        {{ $statusLabels[$st] ?? $st }}
                    </span>

                    {{-- Priority Badge --}}
                    @php
                        $pColors = ['normal'=>'bg-gray-100 text-gray-600','penting'=>'bg-yellow-100 text-yellow-700','darurat'=>'bg-red-100 text-red-700'];
                        $pLabels = ['normal'=>'Normal','penting'=>'Urgent','darurat'=>'Emergency'];
                    @endphp
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $pColors[$request->priority] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $pLabels[$request->priority] ?? $request->priority }}
                    </span>
                </div>
                <h1 class="text-xl font-bold text-gray-800">{{ $request->item_nama }}</h1>
                <p class="text-sm text-gray-400">
                    <x-ts:icon name="tabler.map-pin" class="inline h-4 w-4" />
                    {{ $request->lokasi_nama }} &mdash;
                    Kode: <span class="font-mono font-semibold text-gray-600">{{ $request->asset?->kode ?? 'Umum / Non-Aset' }}</span>
                </p>
            </div>
            <div class="text-right text-xs text-gray-400">
                <div>Diajukan oleh <span class="font-semibold text-gray-600">{{ $request->user_request ?? '-' }}</span></div>
                @if ($request->pelapor_kontak)
                    <div>Kontak: <span class="font-mono font-semibold text-gray-600">{{ $request->pelapor_kontak }}</span></div>
                @endif
                <div>{{ $request->created_at?->format('d M Y, H:i') }}</div>
            </div>
        </div>
    </div>

    {{-- ─── Body: Timeline + Thread ──────────────────────────── --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- ─── Kolom Kiri: Timeline + Info ──────────────────── --}}
        <div class="flex flex-col gap-4 lg:col-span-1">

            {{-- Timeline Status --}}
            <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                <h2 class="mb-4 text-sm font-semibold text-gray-700">Status Progress</h2>
                <ol class="relative border-l border-gray-200 space-y-6 ml-3">
                    @foreach ($timeline as $step)
                        <li class="ml-6">
                            <span class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full ring-4 ring-white
                                {{ $step['done'] ? 'bg-green-500' : ($step['active'] ? 'bg-indigo-400 animate-pulse' : 'bg-gray-200') }}">
                                <x-ts:icon name="{{ $step['icon'] }}" class="h-3 w-3 text-white" />
                            </span>
                            <p class="text-sm font-semibold {{ $step['done'] ? 'text-gray-800' : 'text-gray-400' }}">
                                {{ $step['label'] }}
                            </p>
                            <p class="text-xs text-gray-400">{{ $step['sub'] }}</p>
                            @if ($step['time'])
                                <time class="text-[10px] text-gray-300">{{ $step['time'] }}</time>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>

            {{-- Info Deskripsi --}}
            <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                <h2 class="mb-3 text-sm font-semibold text-gray-700">Deskripsi Masalah</h2>
                <p class="text-sm text-gray-600 leading-relaxed">{{ $request->note ?? '-' }}</p>

                @if ($request->ket_priority)
                    <div class="mt-3 rounded-lg bg-yellow-50 p-3 text-xs text-yellow-700">
                        <span class="font-semibold">Keterangan Prioritas:</span> {{ $request->ket_priority }}
                    </div>
                @endif

                {{-- Lampiran --}}
                @if (!empty($request->lampiran))
                    <div class="mt-4">
                        <p class="mb-2 text-xs font-semibold text-gray-500">Lampiran</p>
                        <div class="flex flex-wrap gap-3">
                            @foreach ($request->lampiran as $file)
                                @php
                                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                                @endphp
                                @if ($isImage)
                                    <button type="button" @click="openPreview('{{ Storage::url($file) }}')"
                                       class="group relative block h-20 w-20 overflow-hidden rounded-lg border border-gray-200 shadow-sm bg-gray-50 focus:outline-none">
                                        <img src="{{ Storage::url($file) }}" alt="Lampiran" class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-110" />
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                            <x-ts:icon name="tabler.zoom-in" class="h-5 w-5 text-white" />
                                        </div>
                                    </button>
                                @else
                                    <a href="{{ Storage::url($file) }}" target="_blank"
                                       class="flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-indigo-600 hover:bg-indigo-50 hover:border-indigo-200 transition-all">
                                        <x-ts:icon name="tabler.paperclip" class="h-4 w-4 text-gray-400" />
                                        <span>File {{ strtoupper($extension) }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Info Teknisi --}}
            @if ($request->jadwal)
                <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                    <h2 class="mb-3 text-sm font-semibold text-gray-700">Info Jadwal & Teknisi</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Jadwal</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($request->jadwal->tanggal)->format('d M Y') }}</span>
                        </div>
                        @foreach ($request->jadwal->teknisi as $tk)
                            <div class="flex justify-between">
                                <span class="text-gray-400">Teknisi</span>
                                <span class="font-medium">{{ $tk->user?->karyawan?->nama ?? '-' }}</span>
                            </div>
                        @endforeach
                        @if ($request->jadwal->work)
                            <div class="flex justify-between">
                                <span class="text-gray-400">Mulai</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($request->jadwal->work->mulai)->format('d M Y H:i') }}</span>
                            </div>
                            @if ($request->jadwal->work->selesai)
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Selesai</span>
                                    <span class="font-medium">{{ \Carbon\Carbon::parse($request->jadwal->work->selesai)->format('d M Y H:i') }}</span>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- ─── Kolom Kanan: Thread Diskusi ───────────────────── --}}
        <div class="flex flex-col rounded-xl bg-white shadow-sm border border-gray-100 lg:col-span-2 overflow-hidden">
            <div class="border-b border-gray-100 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-700">Log Aktivitas</h2>
            </div>

            {{-- Thread list --}}
            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4" style="max-height: 500px;">
                @forelse ($request->comments()->where('type', 'log')->get() as $comment)
                        {{-- Log Sistem --}}
                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            <div class="h-px flex-1 bg-gray-100"></div>
                            <span class="flex items-center gap-1">
                                <x-ts:icon name="tabler.robot" class="h-3 w-3" />
                                {{ $comment->body }}
                                &middot; {{ $comment->created_at->format('d M H:i') }}
                            </span>
                            <div class="h-px flex-1 bg-gray-100"></div>
                        </div>
                @empty
                    <div class="py-12 text-center text-sm text-gray-300">
                        <x-ts:icon name="tabler.file-description" class="mx-auto mb-2 h-8 w-8" />
                        Belum ada catatan atau aktivitas log.
                    </div>
                @endforelse
            </div>
        </div>
        </div>

    
    {{-- Modal Preview Gambar --}}
    <div x-show="showPreview" 
         x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/85 backdrop-blur-xs p-4"
         @click="showPreview = false"
         @keyup.escape.window="showPreview = false"
         @keyup.arrow-left.window="if(showPreview) prevImage()"
         @keyup.arrow-right.window="if(showPreview) nextImage()"
         @keyup.a.window="if(showPreview) prevImage()"
         @keyup.d.window="if(showPreview) nextImage()"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Index Helper --}}
        <span x-show="images.length > 1" class="absolute top-4 left-6 text-sm font-semibold text-white/80 bg-black/40 px-3 py-1.5 rounded-full z-[110]" x-text="(currentIndex + 1) + ' / ' + images.length"></span>
        
        {{-- Tombol Panah Kiri --}}
        <button type="button" x-show="images.length > 1" @click.stop="prevImage()" 
                class="absolute left-6 top-1/2 -translate-y-1/2 z-[110] rounded-full bg-white/10 hover:bg-white/20 p-3 text-white hover:scale-105 transition-all focus:outline-none"
                title="Panah Kiri / A">
            <x-ts:icon name="tabler.chevron-left" class="h-8 w-8" />
        </button>

        {{-- Tombol Panah Kanan --}}
        <button type="button" x-show="images.length > 1" @click.stop="nextImage()" 
                class="absolute right-6 top-1/2 -translate-y-1/2 z-[110] rounded-full bg-white/10 hover:bg-white/20 p-3 text-white hover:scale-105 transition-all focus:outline-none"
                title="Panah Kanan / D">
            <x-ts:icon name="tabler.chevron-right" class="h-8 w-8" />
        </button>

        <div class="relative max-h-[90vh] max-w-[90vw]" @click.stop>
            <img :src="previewSrc" class="max-h-[85vh] max-w-[85vw] rounded-lg object-contain shadow-2xl" />
            <button type="button" @click="showPreview = false" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition-colors">
                <x-ts:icon name="tabler.x" class="h-8 w-8" />
            </button>
        </div>
    </div>
</div>
