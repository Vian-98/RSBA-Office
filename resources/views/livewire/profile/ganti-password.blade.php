<div class="flex w-full flex-col lg:flex-row gap-4 sm:gap-6">

    <div class="w-full rounded-2xl border border-slate-100 bg-white p-4 sm:p-5 lg:w-1/2 shadow-2xs">
        <h2 class="flex flex-row items-center gap-2 font-bold text-sm text-slate-800 border-b border-slate-100 pb-3 mb-4">
            <x-ts:icon name="tabler.key" class="h-4 w-4 text-indigo-500" />
            Ganti Password
        </h2>
        <form wire:submit.prevent='gantiPassword' class="flex flex-col gap-4" autocomplete="off">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Password Lama</label>
                <x-ts:password wire:model='current_password' placeholder="Masukkan password lama Anda" />
                @error('current_password')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Password Baru</label>
                <x-ts:password wire:model='password' placeholder="Masukkan password baru Anda" />
                @error('password')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Konfirmasi Password Baru</label>
                <x-ts:password wire:model='password_confirmation' placeholder="Masukkan ulang password baru Anda" />
                @error('password_confirmation')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex justify-end pt-2">
                <x-ts:button type='submit' color="indigo" class="text-xs font-bold shadow-sm" loading='gantiPassword'>
                    <x-tabler-checks class="h-4 w-4 mr-1.5" /> Simpan Password
                </x-ts:button>
            </div>
        </form>
    </div>

    <div class="w-full lg:w-1/2">
        <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-5 space-y-3">
            <span class="font-bold text-slate-700 block text-xs uppercase tracking-wider">Aturan Pembuatan Password</span>
            <ul class="text-xs text-slate-500 space-y-2.5">
                <li class="flex items-start gap-2.5">
                    <x-tabler-circle-check class="h-4 w-4 text-emerald-600 shrink-0 mt-0.5" />
                    <span>Panjang minimal <strong class="text-slate-700">8 karakter</strong></span>
                </li>
                <li class="flex items-start gap-2.5">
                    <x-tabler-circle-check class="h-4 w-4 text-emerald-600 shrink-0 mt-0.5" />
                    <span>Harus cocok dengan field <strong class="text-slate-700">Konfirmasi Password Baru</strong></span>
                </li>
                <li class="flex items-start gap-2.5">
                    <x-tabler-circle-check class="h-4 w-4 text-emerald-600 shrink-0 mt-0.5" />
                    <span>Disarankan mengombinasikan huruf besar, angka, dan karakter khusus (@, #, $, dll.)</span>
                </li>
            </ul>
        </div>
    </div>

</div>
