<div>
    <div class="flex flex-col gap-4 lg:flex-row">

        {{-- ===== PANEL KIRI ===== --}}
        <div class="w-full shrink-0 lg:w-64">
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-100">

                {{-- Cover / gradient bar --}}
                <div class="h-24 bg-gradient-to-br from-indigo-400 to-purple-600"></div>

                {{-- Foto & nama --}}
                 <div class="flex flex-col items-center px-5 pb-5"
                     x-data="{ userPreview: '{{ $profileTmp ? $profileTmp->temporaryUrl() : ($this->avatarUrl ?? '') }}' }">

                    {{-- Avatar - posisi overlap cover --}}
                    <div class="-mt-12 mb-3 flex h-24 w-24 cursor-pointer items-center justify-center overflow-hidden rounded-full border-4 border-white bg-indigo-100 shadow"
                         x-on:click="document.getElementById('profileInput').click();"
                         title="Klik untuk ganti foto">
                        <template x-if="userPreview">
                            <img :src="userPreview" class="h-full w-full object-cover" alt="Foto Profil" />
                        </template>
                        <template x-if="!userPreview">
                            <span class="text-lg font-bold text-indigo-600">{{ $this->avatarInitials }}</span>
                        </template>
                    </div>

                    <input type="file" wire:model='profileTmp' id="profileInput" style="display:none"
                           @change="userPreview = URL.createObjectURL($event.target.files[0])" />

                    @error('profileTmp')
                        <span class="mb-1 text-xs italic text-red-500">{{ $message }}</span>
                    @enderror

                    @if($profileTmp)
                        <x-ts:button xs class="mb-2" wire:click="updateAvatar" loading="updateAvatar">
                            Simpan Foto
                        </x-ts:button>
                    @endif

                    {{-- Nama --}}
                    <h2 class="text-center text-base font-bold uppercase tracking-wide text-slate-800">
                        {{ $this->karyawan->full_nama }}
                    </h2>
                    <span class="mt-0.5 text-xs text-slate-400">{{ $this->karyawan->nip }}</span>

                    {{-- Badge role --}}
                    <span class="mt-2 inline-block rounded-full bg-indigo-50 px-3 py-0.5 text-xs font-semibold text-indigo-600">
                        {{ $user?->getRoleNames()[0] ?? 'Belum Ada Role' }}
                    </span>
                </div>

                {{-- Divider --}}
                <div class="border-t border-slate-100"></div>

                {{-- Detail info --}}
                <div class="space-y-0 px-5 py-4 text-sm">
                    {{-- Jabatan --}}
                    <div class="flex items-start gap-2 py-2.5 border-b border-slate-50">
                        <x-tabler-briefcase class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                        <div>
                            <p class="text-xs text-slate-400">Jabatan</p>
                            <p class="font-medium text-slate-700">{{ $this->karyawan->jabatan?->first()?->nama ?? '-' }}</p>
                        </div>
                    </div>

                    {{-- Ruangan / Unit --}}
                    <div class="flex items-start gap-2 py-2.5 border-b border-slate-50">
                        <x-tabler-door class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                        <div>
                            <p class="text-xs text-slate-400">Ruangan / Unit</p>
                            <p class="font-medium text-slate-700">{{ $this->karyawan->ruangan->nama ?? '-' }}</p>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="flex items-start gap-2 py-2.5 border-b border-slate-50">
                        <x-tabler-shield-check class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                        <div>
                            <p class="text-xs text-slate-400">Status</p>
                            <x-filament::badge color="{{ $this->karyawan->status->color() }}">
                                {{ $this->karyawan->status->nama() }}
                            </x-filament::badge>
                        </div>
                    </div>

                    {{-- Masa kerja --}}
                    <div class="flex items-start gap-2 py-2.5 border-b border-slate-50">
                        <x-tabler-calendar-stats class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                        <div>
                            <p class="text-xs text-slate-400">Masa Kerja</p>
                            <p class="font-medium text-slate-700">{{ $this->karyawan->masakerja }}</p>
                        </div>
                    </div>

                    {{-- Sisa cuti --}}
                    <div class="flex items-start gap-2 py-2.5">
                        <x-tabler-beach class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                        <div>
                            <p class="text-xs text-slate-400">Sisa Cuti</p>
                            @if($this->sisaCuti < 0)
                                <span class="text-xs italic text-rose-500">Masa kerja &lt; 1 Tahun</span>
                            @else
                                <p class="font-semibold text-emerald-600">{{ $this->sisaCuti }} Hari</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== PANEL KANAN (Tabs) ===== --}}
        <div class="min-w-0 flex-1">
            <div class="rounded-2xl bg-white p-3 sm:p-5 shadow-sm border border-slate-100" x-data="{ tab: @entangle('tab') }">
                <div class="overflow-x-auto scrollbar-hidden pb-1">
                    <nav class="inline-flex gap-2 sm:gap-3 min-w-max p-0.5" aria-label="Tabs">
                        <button wire:click="$set('tab', 'home')" @click="tab = 'home'"
                            :class="tab === 'home' ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-slate-100/90 hover:bg-slate-200/80 border border-slate-200 text-slate-700 font-semibold'"
                            class="whitespace-nowrap rounded-xl py-2 px-4 text-xs sm:text-sm transition-all duration-150 flex items-center gap-2">
                            <x-ts:icon name="tabler.home" class="h-4 w-4 shrink-0" />
                            Home
                        </button>
                        <button wire:click="$set('tab', 'identitas')" @click="tab = 'identitas'"
                            :class="tab === 'identitas' ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-slate-100/90 hover:bg-slate-200/80 border border-slate-200 text-slate-700 font-semibold'"
                            class="whitespace-nowrap rounded-xl py-2 px-4 text-xs sm:text-sm transition-all duration-150 flex items-center gap-2">
                            <x-ts:icon name="tabler.user-square" class="h-4 w-4 shrink-0" />
                            Identitas
                        </button>
                        <button wire:click="$set('tab', 'pendidikan')" @click="tab = 'pendidikan'"
                            :class="tab === 'pendidikan' ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-slate-100/90 hover:bg-slate-200/80 border border-slate-200 text-slate-700 font-semibold'"
                            class="whitespace-nowrap rounded-xl py-2 px-4 text-xs sm:text-sm transition-all duration-150 flex items-center gap-2">
                            <x-ts:icon name="tabler.school" class="h-4 w-4 shrink-0" />
                            Pendidikan
                        </button>
                        <button wire:click="$set('tab', 'documents')" @click="tab = 'documents'"
                            :class="tab === 'documents' ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-slate-100/90 hover:bg-slate-200/80 border border-slate-200 text-slate-700 font-semibold'"
                            class="whitespace-nowrap rounded-xl py-2 px-4 text-xs sm:text-sm transition-all duration-150 flex items-center gap-2">
                            <x-ts:icon name="tabler.file-type-doc" class="h-4 w-4 shrink-0" />
                            Dokumen
                        </button>
                        <button wire:click="$set('tab', 'cuti')" @click="tab = 'cuti'"
                            :class="tab === 'cuti' ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-slate-100/90 hover:bg-slate-200/80 border border-slate-200 text-slate-700 font-semibold'"
                            class="whitespace-nowrap rounded-xl py-2 px-4 text-xs sm:text-sm transition-all duration-150 flex items-center gap-2">
                            <x-ts:icon name="tabler.calendar-pause" class="h-4 w-4 shrink-0" />
                            Izin dan Cuti
                        </button>
                    </nav>
                </div>

                <div class="mt-4">
                    @if($tab === 'home' || $tab === 'Home')
                        <livewire:Profile.Home :id="$user?->karyawan_id" key="home" />
                    @elseif($tab === 'identitas' || $tab === 'Identitas')
                        <livewire:Karyawan.EditIdentitas :id="$user?->karyawan_id" key="identitas-karyawan" />
                    @elseif($tab === 'pendidikan' || $tab === 'Pendidikan')
                        <livewire:Karyawan.Pendidikan.PendidikanList :id="$user?->karyawan_id" key="pendidikan-list" @pendidikan-karyawan-created="$refresh" @deleted-pendidikan-karyawan="$refresh" />
                    @elseif($tab === 'documents' || $tab === 'Documents')
                        <livewire:Karyawan.Document.DocumentList :id="$user?->karyawan_id" key="document-list" @document-karyawan-created="$refresh" @document-karyawan-deleted="$refresh" />
                    @elseif($tab === 'cuti' || $tab === 'Izin dan Cuti')
                        <livewire:Profile.Cuti :id="$user?->karyawan_id" key="cuti-list" />
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
