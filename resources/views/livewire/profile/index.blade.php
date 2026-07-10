<div>
    <div class="flex flex-col gap-4 lg:flex-row">

        {{-- ===== PANEL KIRI ===== --}}
        <div class="w-full shrink-0 lg:w-64">
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-100">

                {{-- Cover / gradient bar --}}
                <div class="h-24 bg-gradient-to-br from-indigo-400 to-purple-600"></div>

                {{-- Foto & nama --}}
                <div class="flex flex-col items-center px-5 pb-5"
                     x-data="{ userPreview: '{{ $profileTmp ? $profileTmp->temporaryUrl() : asset('storage/' . $this->karyawan->foto) }}' }">

                    {{-- Avatar - posisi overlap cover --}}
                    <div class="-mt-12 mb-3 flex h-24 w-24 cursor-pointer items-center justify-center overflow-hidden rounded-full border-4 border-white bg-indigo-100 shadow"
                         x-on:click="document.getElementById('profileInput').click();"
                         title="Klik untuk ganti foto">
                        <img :src="userPreview" class="h-full w-full object-cover" alt="Foto Profil" />
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
            <x-ts:tab selected="Home" x-on:navigate="$wire.set('tab',$event.detail.select)">
                <x-ts:tab.items tab="Home">
                    <x-slot:left>
                        <x-ts:icon name="tabler.home" class="h-5 w-5" />
                    </x-slot:left>

                    <livewire:Profile.Home :id="$user?->karyawan_id" key="home" />
                </x-ts:tab.items>

                <x-ts:tab.items tab="Identitas">
                    <x-slot:left>
                        <x-ts:icon name="tabler.user-square" class="h-5 w-5" />
                    </x-slot:left>

                    {{-- load edit-identitas --}}
                    <livewire:Karyawan.EditIdentitas :id="$user?->karyawan_id" key="identitas-karyawan" />
                </x-ts:tab.items>

                <x-ts:tab.items tab="Pendidikan">
                    <x-slot:left>
                        <x-ts:icon name="tabler.school" class="h-5 w-5" />
                    </x-slot:left>

                    {{-- load Karyawan.Pendidikan --}}
                    <livewire:Karyawan.Pendidikan.PendidikanList :id="$user?->karyawan_id" key="'pendidikan-list'" @pendidikan-karyawan-created="$refresh" @deleted-pendidikan-karyawan="$refresh" />
                </x-ts:tab.items>

                <x-ts:tab.items tab="Documents">
                    <x-slot:left>
                        <x-ts:icon name="tabler.file-type-doc" class="h-5 w-5" />
                    </x-slot:left>

                    {{-- load Karyawan.Documents --}}
                    <livewire:Karyawan.Document.DocumentList :id="$user?->karyawan_id" key="'document-list'" @document-karyawan-created="$refresh" @document-karyawan-deleted="$refresh" />
                </x-ts:tab.items>

                <x-ts:tab.items tab="Izin dan Cuti">
                    <x-slot:left>
                        <x-ts:icon name="tabler.calendar-pause" class="h-5 w-5" />
                    </x-slot:left>

                    {{-- load Jadwal & Cuti --}}
                    <livewire:Profile.Cuti :id="$user?->karyawan_id" key="cuti-list" />
                </x-ts:tab.items>
            </x-ts:tab>
        </div>

    </div>
</div>
