@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col items-center justify-center gap-2 select-none">
        <!-- Top Side: Page buttons -->
        <div class="flex items-center gap-1.5">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200/60 bg-slate-50/50 text-slate-300 cursor-not-allowed">
                    <x-ts:icon name="tabler.chevron-left" class="h-4 w-4" />
                </span>
            @else
                <button type="button" wire:click="previousPage" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200/80 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                    <x-ts:icon name="tabler.chevron-left" class="h-4 w-4" />
                </button>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="inline-flex h-9 px-2 items-center justify-center text-xs font-semibold text-slate-400">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex h-9 min-w-9 px-3 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-xs">
                                {{ $page }}
                            </span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }})" class="inline-flex h-9 min-w-9 px-3 items-center justify-center rounded-xl border border-slate-200/80 bg-white text-slate-600 text-xs font-semibold hover:bg-slate-50 hover:text-slate-800 transition-colors">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200/80 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                    <x-ts:icon name="tabler.chevron-right" class="h-4 w-4" />
                </button>
            @else
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200/60 bg-slate-50/50 text-slate-300 cursor-not-allowed">
                    <x-ts:icon name="tabler.chevron-right" class="h-4 w-4" />
                </span>
            @endif
        </div>

        <!-- Bottom Side: Label -->
        <div>
            <p class="text-xs font-medium text-slate-500">
                Menampilkan <span class="font-bold text-slate-800">{{ $paginator->firstItem() }}</span> sampai <span class="font-bold text-slate-800">{{ $paginator->lastItem() }}</span> dari <span class="font-bold text-slate-800">{{ $paginator->total() }}</span> {{ $paginatorLabel ?? 'data' }}
            </p>
        </div>
    </nav>
@endif
