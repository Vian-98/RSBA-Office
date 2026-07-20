<div class="space-y-3">
    @if(empty($notifications))
        <div class="flex flex-col items-center justify-center py-6 text-center">
            <span class="rounded-full bg-slate-50 p-3.5 text-slate-400 mb-2 border border-slate-100/50">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a9.049 9.049 0 0 1-5.137-4.89M9 11.25H21m-6-6v6" />
                </svg>
            </span>
            <h4 class="text-xs font-semibold text-slate-700">Tidak ada notifikasi</h4>
            <p class="text-3xs text-slate-400 max-w-xs mt-0.5">Anda akan menerima pemberitahuan saat ada aktivitas baru.</p>
        </div>
    @else
        @php
            $hasUnread = collect($notifications)->contains('is_read', false);
        @endphp
        
        @if ($hasUnread)
            <div class="flex justify-end mb-1">
                <button wire:click="markAllAsRead" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1">
                    <x-ts:icon name="tabler.circle-check" class="h-3.5 w-3.5" />
                    Tandai semua dibaca
                </button>
            </div>
        @endif

        <div class="flex flex-col gap-2.5">
            @foreach($notifications as $notif)
                @php
                    $isUnread = !$notif['is_read'];
                    $colors = match($notif['type']) {
                        'success' => [
                            'bg' => $isUnread ? 'bg-emerald-50/80 border-emerald-100/70 shadow-emerald-500/5' : 'bg-slate-50/40 border-slate-100/70 opacity-70',
                            'text' => 'text-emerald-700',
                            'iconBg' => $isUnread ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500'
                        ],
                        'warning' => [
                            'bg' => $isUnread ? 'bg-amber-50/80 border-amber-100/70 shadow-amber-500/5' : 'bg-slate-50/40 border-slate-100/70 opacity-70',
                            'text' => 'text-amber-700',
                            'iconBg' => $isUnread ? 'bg-amber-500 text-white' : 'bg-slate-200 text-slate-500'
                        ],
                        'danger' => [
                            'bg' => $isUnread ? 'bg-rose-50/80 border-rose-100/70 shadow-rose-500/5' : 'bg-slate-50/40 border-slate-100/70 opacity-70',
                            'text' => 'text-rose-700',
                            'iconBg' => $isUnread ? 'bg-rose-500 text-white' : 'bg-slate-200 text-slate-500'
                        ],
                        default => [
                            'bg' => $isUnread ? 'bg-indigo-50/80 border-indigo-100/70 shadow-indigo-500/5' : 'bg-slate-50/40 border-slate-100/70 opacity-70',
                            'text' => 'text-indigo-700',
                            'iconBg' => $isUnread ? 'bg-indigo-500 text-white' : 'bg-slate-200 text-slate-500'
                        ],
                    };
                @endphp
                <div class="relative overflow-hidden rounded-xl border transition-all duration-200 p-3.5 shadow-3xs hover:shadow-2xs {{ $colors['bg'] }}">
                    <div class="flex gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg shadow-3xs {{ $colors['iconBg'] }}">
                            @if(str_contains($notif['icon'], 'calendar'))
                                <svg class="h-4.5 w-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            @elseif(str_contains($notif['icon'], 'check'))
                                <svg class="h-4.5 w-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif(str_contains($notif['icon'], 'x'))
                                <svg class="h-4.5 w-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif(str_contains($notif['icon'], 'tool'))
                                <svg class="h-4.5 w-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A1.5 1.5 0 0019.5 21l2-2a1.5 1.5 0 000-2.25l-5.83-5.83M11.42 15.17a4.996 4.996 0 01-7.072 0 4.996 4.996 0 010-7.072 4.996 4.996 0 017.072 0 4.996 4.996 0 010 7.072zM11.42 15.17L12 14.5" />
                                </svg>
                            @else
                                <svg class="h-4.5 w-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.086 1.086L12 12.75l-.041.02a.75.75 0 11-1.086-1.086l.041-.02a.75.75 0 011.086 0zM12 21a9 9 0 100-18 9 9 0 000 18z" />
                                </svg>
                            @endif
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex flex-col min-w-0">
                                    <h4 class="text-xs font-bold text-slate-800 leading-tight">{{ $notif['title'] }}</h4>
                                    <span class="text-[10px] text-slate-400 mt-0.5">{{ $notif['time'] }}</span>
                                </div>
                                @if ($isUnread)
                                    <button wire:click="markAsRead('{{ $notif['id'] }}')" 
                                            class="rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors shrink-0" 
                                            title="Tandai sudah dibaca">
                                        <x-ts:icon name="tabler.check" class="h-4 w-4" />
                                    </button>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-slate-500 leading-relaxed">{{ $notif['message'] }}</p>
                            @if(isset($notif['route']) && Route::has($notif['route']))
                                <a href="{{ route($notif['route'], $notif['route_params'] ?? []) }}" class="mt-2 inline-flex items-center gap-0.5 text-[11px] font-bold {{ $colors['text'] }} hover:underline">
                                    Buka Halaman
                                    <svg class="h-2.5 w-2.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
