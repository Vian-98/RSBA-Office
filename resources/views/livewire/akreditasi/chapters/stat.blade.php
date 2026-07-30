<div class="mt-2 flex flex-col gap-2">
    @php
        $stats = $this->getStatsBar();
    @endphp

    {{-- Progress Berkas --}}
    <div class="space-y-1.5">
        <div class="flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
                <x-ts:icon name="tabler.file-upload" class="h-4 w-4 text-blue-500" />
                <span class="font-medium text-gray-700">{{ number_format($stats['percentage_ep_with_files'] ?? 0, 1) }}% terpenuhi</span>
            </div>
            <span class="font-semibold text-gray-900">
                {{ $stats['total_ep_memiliki_file'] ?? 0 }} / {{ $stats['total_ep'] ?? 0 }}
            </span>
        </div>
        <div class="relative h-3 w-full overflow-hidden rounded-full bg-gray-200">
            <div class="bg-{{ $stats['color_ep'] }}-500 absolute inset-0 h-full rounded-full bg-gradient-to-r transition-all duration-500 ease-out"
                style="width: {{ $stats['percentage_ep_with_files'] ?? 0 }}%">
                <div class="h-full w-full animate-pulse bg-white/20"></div>
            </div>
        </div>
    </div>

    {{-- Progress Nilai --}}
    <div class="space-y-1.5">
        <div class="flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
                <x-ts:icon name="tabler.star" class="h-4 w-4 text-blue-500" />
                <span class="font-medium text-gray-700">{{ number_format($stats['percentage_nilai'] ?? 0, 1) }}% terpenuhi</span>
            </div>
            <span class="font-semibold text-gray-900">
                {{ $stats['total_nilai'] ?? 0 }} / {{ $stats['total_target_nilai'] ?? 0 }}
            </span>
        </div>
        <div class="relative h-3 w-full overflow-hidden rounded-full bg-gray-200">
            <div class="bg-{{ $stats['color_nilai'] }}-500 absolute inset-0 h-full rounded-full bg-gradient-to-r transition-all duration-500 ease-out"
                style="width: {{ $stats['percentage_nilai'] ?? 0 }}%">
                <div class="h-full w-full animate-pulse bg-white/20"></div>
            </div>
        </div>
    </div>
</div>
