@php
    $authUser = auth()->user();
    $authKaryawan = $authUser?->karyawan;
    $authInitials = '';
    if ($authKaryawan && $authKaryawan->nama) {
        $words = preg_split('/\s+/', trim($authKaryawan->nama));
        for ($i = 0; $i < min(2, count($words)); $i++) {
            $authInitials .= strtoupper(substr($words[$i], 0, 1));
        }
    }
@endphp

<div x-show="isOpen()" class="fixed inset-0 z-50 flex h-screen bg-white bg-opacity-75 md:static md:bg-transparent">
    <div @click.away="handleAway()" @keyup.escape.window="handleAway()" 
         class="scrollbar-hidden w-72 flex flex-col justify-between h-full bg-white shadow-xl border-r border-slate-100 transition-all duration-300 ease-in-out">
        
        <div id="sidebar-scroll-container" class="flex-1 min-h-0 flex flex-col gap-5 overflow-y-auto scrollbar-hidden py-6">
            {{-- logo & Title --}}
            <div class="flex items-center px-6 gap-3">
                <div class="shrink-0">
                    <x-logo class="h-10 w-auto" />
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-800 tracking-wider whitespace-nowrap">RS Bintang Amin</span>
                    <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest leading-none mt-0.5">Office Portal</span>
                </div>
            </div>

            {{-- search --}}
            <div class="px-4">
                <div class="relative flex items-center rounded-full bg-slate-100 hover:bg-slate-200/60 transition-all duration-200 px-3 py-2">
                    <x-tabler-search class="h-4 w-4 text-slate-400 shrink-0 cursor-pointer" />
                    <input wire:model.live.debounce.300ms="searchMenu" 
                           placeholder="Cari menu..."
                           class="ml-2 w-full bg-transparent text-xs text-slate-700 outline-none border-none p-0 focus:ring-0 focus:outline-none" />
                </div>
            </div>


            {{-- menu list --}}
            <div class="px-3">
                <x-menus :menus="$menus" class="flex" />
            </div>
        </div>

        {{-- Profile Footer --}}
        @if ($authUser && $authKaryawan)
            <div class="border-t border-slate-100 p-4 bg-slate-50/50">
                <div class="flex items-center justify-between gap-3">
                    {{-- Avatar & Info --}}
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="h-9 w-9 shrink-0 overflow-hidden rounded-full border border-slate-200 bg-indigo-50 flex items-center justify-center font-bold text-indigo-600 text-xs shadow-3xs">
                            @if ($authKaryawan->foto)
                                <img src="{{ route('api.users.avatar', ['userId' => $authUser->id]) }}" class="h-full w-full object-cover" alt="Avatar" />
                            @else
                                {{ $authInitials }}
                            @endif
                        </div>
                        
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-bold text-slate-800 truncate whitespace-nowrap">{{ $authKaryawan->nama }}</span>
                            <span class="text-[10px] text-slate-400 truncate whitespace-nowrap mt-0.5">{{ $authUser->email }}</span>
                        </div>
                    </div>

                    {{-- Logout Button --}}
                    <button wire:click="logout" 
                            class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors shrink-0" 
                            title="Log out">
                        <x-tabler-logout class="h-4 w-4" />
                    </button>
                </div>
            </div>
        @endif

    </div>
</div>
