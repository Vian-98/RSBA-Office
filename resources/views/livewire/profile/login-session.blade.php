<div class="space-y-4">
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-2xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Sesi Login Aktif</h3>
                <p class="text-xs text-slate-500 mt-1">Daftar perangkat yang saat ini masuk menggunakan akun Anda.</p>
            </div>
            @if(count($sessions) > 1)
                <x-ts:button wire:click="logoutOtherDevices" color="rose" class="text-xs font-bold shadow-sm" loading="logoutOtherDevices">
                    Keluarkan Perangkat Lain
                </x-ts:button>
            @endif
        </div>

        <div class="divide-y divide-slate-100">
            @foreach($sessions as $sess)
                <div class="flex items-center justify-between py-3.5 first:pt-0 last:pb-0">
                    <div class="flex items-center gap-3.5">
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-2.5 text-slate-500 shrink-0">
                            @if($sess['os'] === 'Windows' || $sess['os'] === 'macOS' || $sess['os'] === 'Linux')
                                <x-tabler-device-laptop class="h-5 w-5" />
                            @else
                                <x-tabler-device-mobile class="h-5 w-5" />
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-slate-800">{{ $sess['browser'] }} ({{ $sess['os'] }})</span>
                                @if($sess['is_current'])
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-3xs font-extrabold text-emerald-700 border border-emerald-200">
                                        Perangkat Ini
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-slate-400 mt-1 font-medium">
                                <span>IP: {{ $sess['ip_address'] }}</span>
                                <span>•</span>
                                <span>{{ $sess['last_activity'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Security Tip Panel -->
    <div class="rounded-2xl border border-slate-100 bg-amber-50/30 p-5 text-xs text-amber-800 flex items-start gap-3">
        <x-tabler-alert-circle class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" />
        <div>
            <span class="font-bold text-amber-900 block mb-0.5">Tips Keamanan</span>
            Jika Anda mendeteksi adanya aktivitas login mencurigakan atau dari lokasi yang tidak dikenali, segera klik tombol <strong>"Keluarkan Perangkat Lain"</strong> di atas dan <strong>ganti password</strong> akun Anda untuk mencegah akses yang tidak sah.
        </div>
    </div>
</div>
