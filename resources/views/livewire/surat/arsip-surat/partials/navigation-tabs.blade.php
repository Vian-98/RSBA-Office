{{-- Navigation Tabs Kategori --}}
<div class="border-b border-slate-200 bg-slate-50/60 p-2 overflow-x-auto">
    <div class="flex items-center gap-1.5 min-w-max">
        <button 
            wire:click="$set('filterType', 'all')" 
            type="button"
            class="px-4 py-2 text-xs font-semibold rounded-xl transition-all flex items-center gap-2 cursor-pointer {{ $filterType === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-200/70' }}"
        >
            <x-tabler-files class="size-4" />
            <span>Semua Kategori</span>
        </button>

        @foreach ($kategoriList as $cat)
            @php
                $isActive = $filterType === $cat->kode;
            @endphp
            <button 
                wire:click="$set('filterType', '{{ $cat->kode }}')" 
                type="button"
                class="px-3.5 py-2 text-xs font-semibold rounded-xl transition-all flex items-center gap-2 cursor-pointer {{ $isActive ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-200/70' }}"
            >
                @switch($cat->icon)
                    @case('school')
                        <x-tabler-school class="size-4" />
                        @break
                    @case('microscope')
                        <x-tabler-microscope class="size-4" />
                        @break
                    @case('clipboard-list')
                        <x-tabler-clipboard-list class="size-4" />
                        @break
                    @case('file-alert')
                        <x-tabler-file-alert class="size-4" />
                        @break
                    @case('receipt-2')
                        <x-tabler-receipt-2 class="size-4" />
                        @break
                    @case('send')
                        <x-tabler-send class="size-4" />
                        @break
                    @default
                        <x-tabler-file-text class="size-4" />
                @endswitch
                <span>{{ $cat->nama }}</span>
            </button>
        @endforeach
    </div>
</div>
