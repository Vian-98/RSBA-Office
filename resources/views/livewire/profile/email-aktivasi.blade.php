<div class="space-y-4">
    @if ($viewState === 'view')
        <!-- Default State: Status Viewer -->
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-2xs">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="rounded-xl p-3 shrink-0 {{ $isVerified ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                        @if($isVerified)
                            <x-tabler-mail-opened class="h-6 w-6" />
                        @else
                            <x-tabler-mail-forward class="h-6 w-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Status Aktivasi Email</h3>
                        <p class="text-xs text-slate-500 mt-1">Alamat email Anda saat ini: <span class="font-semibold text-slate-700">{{ $email }}</span></p>
                        
                        @if($isVerified)
                            <div class="mt-3 flex items-center gap-1.5 text-xs text-emerald-600 font-semibold bg-emerald-50/50 px-2.5 py-1 rounded-lg w-fit">
                                <x-tabler-circle-check class="h-4 w-4" />
                                Terverifikasi & Aktif (Sejak {{ $verifiedAt }})
                            </div>
                        @else
                            <div class="mt-3 flex items-center gap-1.5 text-xs text-amber-600 font-semibold bg-amber-50/50 px-2.5 py-1 rounded-lg w-fit">
                                <x-tabler-alert-circle class="h-4 w-4" />
                                Belum Terverifikasi / Belum Aktif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-ts:button wire:click="showChangeEmail" color="slate" flat class="text-xs font-bold border border-slate-200 bg-white hover:bg-slate-50">
                        Ubah Email
                    </x-ts:button>

                    @if($isVerified)
                        <x-ts:button wire:click="cancelActivation" color="rose" class="text-xs font-bold shadow-sm" loading="cancelActivation">
                            Deaktivasi / Reset
                        </x-ts:button>
                    @else
                        <x-ts:button wire:click="triggerActivation" color="indigo" class="text-xs font-bold shadow-sm" loading="triggerActivation">
                            Aktifkan Email
                        </x-ts:button>
                    @endif
                </div>
            </div>
        </div>

    @elseif ($viewState === 'change_email')
        <!-- State: Change Email Form -->
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-2xs">
            <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">Ubah Alamat Email</h3>
            
            <form wire:submit.prevent="saveNewEmail" class="space-y-4 max-w-md">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Alamat Email Baru</label>
                    <x-ts:input wire:model.defer="new_email" type="email" placeholder="Masukkan email baru Anda..." />
                    @error('new_email')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <x-ts:button type="submit" color="indigo" class="text-xs font-bold shadow-sm" loading="saveNewEmail">
                        Simpan Email
                    </x-ts:button>
                    <x-ts:button type="button" wire:click="cancelChangeEmail" color="slate" flat class="text-xs font-bold border border-slate-200">
                        Batal
                    </x-ts:button>
                </div>
            </form>
        </div>

    @elseif ($viewState === 'verify_code')
        <!-- State: Verify 6-digit Code Form -->
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-2xs">
            <div class="flex items-start gap-4 mb-4">
                <div class="rounded-xl p-3 shrink-0 bg-indigo-50 text-indigo-600">
                    <x-tabler-shield-lock class="h-6 w-6" />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Verifikasi Alamat Email</h3>
                    <p class="text-xs text-slate-500 mt-1">Kami telah mengirimkan 6 digit kode verifikasi ke email: <span class="font-semibold text-slate-700">{{ $email }}</span></p>
                </div>
            </div>

            <form wire:submit.prevent="submitVerificationCode" class="space-y-4 max-w-sm">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Masukkan Kode Verifikasi</label>
                    <x-ts:input wire:model.defer="verification_code" type="text" placeholder="Masukkan 6 digit kode..." class="text-center font-bold tracking-widest text-lg" maxlength="6" />
                    @error('verification_code')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <x-ts:button type="submit" color="indigo" class="text-xs font-bold shadow-sm" loading="submitVerificationCode">
                        Verifikasi Kode
                    </x-ts:button>
                    <x-ts:button type="button" wire:click="triggerActivation" color="slate" flat class="text-xs font-bold border border-slate-200" loading="triggerActivation">
                        Kirim Ulang Kode
                    </x-ts:button>
                    <x-ts:button type="button" wire:click="cancelVerification" color="slate" flat class="text-xs font-bold border border-slate-200">
                        Batal
                    </x-ts:button>
                </div>
            </form>
        </div>
    @endif

    <!-- Additional Help Panel -->
    <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-5 text-xs text-slate-500 space-y-2">
        <span class="font-bold text-slate-700 block mb-1">Mengapa aktivasi email penting?</span>
        <p><strong>1. Notifikasi Cuti & Izin</strong>: Status pengajuan cuti, dinas luar, atau izin Anda akan langsung diinfokan melalui email.</p>
        <p><strong>2. Slip Gaji Bulanan</strong>: Slip gaji resmi akan otomatis dikirimkan ke kotak masuk email Anda setiap bulan setelah disetujui.</p>
        <p><strong>3. Pemulihan Akun</strong>: Jika Anda lupa password, tautan reset password akan dikirimkan ke email terdaftar Anda.</p>
    </div>
</div>
