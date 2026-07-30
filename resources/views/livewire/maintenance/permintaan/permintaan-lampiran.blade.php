@php
    $rawLampirans = is_array($lampirans) ? $lampirans : (is_string($lampirans) ? (json_decode($lampirans, true) ?? []) : []);
    $imageUrls = array_map(function($path) {
        if (empty($path)) return '';
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/storage/')) {
            return $path;
        }
        return Storage::url($path);
    }, $rawLampirans);
    $imageUrls = array_filter($imageUrls);
@endphp
<div x-data="{
        currentSlide: 0,
        slidesCount: {{ count($imageUrls) }},
        next() {
            if (this.slidesCount === 0) return;
            this.currentSlide = (this.currentSlide + 1) % this.slidesCount;
        },
        prev() {
            if (this.slidesCount === 0) return;
            this.currentSlide = (this.currentSlide - 1 + this.slidesCount) % this.slidesCount;
        }
     }" 
     class="relative flex flex-col items-center justify-center bg-slate-900 rounded-xl overflow-hidden min-h-[350px] group"
     @keyup.arrow-left.window="prev()"
     @keyup.arrow-right.window="next()"
     @keyup.a.window="prev()"
     @keyup.d.window="next()">

    {{-- Slide Images rendered by Blade for 100% reliability --}}
    <div class="relative w-full aspect-video flex items-center justify-center overflow-hidden bg-slate-950">
        @forelse ($imageUrls as $index => $image)
            <div class="absolute inset-0 flex items-center justify-center" x-show="currentSlide === {{ $index }}"
                 x-cloak
                 x-transition:enter="transition ease-out duration-300 transform scale-95 opacity-0"
                 x-transition:enter-end="transform scale-100 opacity-100"
                 x-transition:leave="transition ease-in duration-200 transform scale-105 opacity-0">
                <img src="{{ $image }}" class="max-h-full max-w-full object-contain" alt="Lampiran {{ $index + 1 }}" />
            </div>
        @empty
            <div class="text-white text-sm">Tidak ada gambar lampiran.</div>
        @endforelse
    </div>

    <!-- Index helper -->
    @if (count($imageUrls) > 1)
        <span class="absolute top-4 left-6 text-xs font-semibold text-white/80 bg-black/40 px-2.5 py-1 rounded-full z-10">
            <span x-text="currentSlide + 1"></span> / {{ count($imageUrls) }}
        </span>
    @endif

    <!-- Navigation Buttons -->
    @if (count($imageUrls) > 1)
        <button type="button" @click="prev()" 
                class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-black/40 hover:bg-black/60 p-2.5 text-white hover:scale-105 transition-all focus:outline-none z-10 opacity-0 group-hover:opacity-100">
            <x-ts:icon name="tabler.chevron-left" class="h-6 w-6" />
        </button>
        <button type="button" @click="next()" 
                class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-black/40 hover:bg-black/60 p-2.5 text-white hover:scale-105 transition-all focus:outline-none z-10 opacity-0 group-hover:opacity-100">
            <x-ts:icon name="tabler.chevron-right" class="h-6 w-6" />
        </button>
    @endif
</div>
