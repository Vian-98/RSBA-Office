<div>
    <div class="flex flex-col gap-2 lg:flex-row">
        <div class="w-full lg:w-1/4">
            <div class="flex flex-col space-y-2 rounded-lg bg-white p-8">
                <div class="flex flex-col items-center justify-center" x-data="{ userPreview: '{{ $profileTmp ? $profileTmp->temporaryUrl() : asset('storage/' . $this->karyawan->foto) }}' }">
                    <div class="flex h-44 w-44 cursor-pointer items-center justify-center overflow-hidden rounded-full border border-indigo-300">
                        <img :src="userPreview" class="h-full w-full object-cover" alt="Click to update" x-on:click="document.getElementById('profileInput').click();">
                    </div>

                    <input type="file" wire:model='profileTmp' id="profileInput" style="display: none" @change="userPreview = URL.createObjectURL($event.target.files[0])" />

                    @error('profileTmp')
                        <span class="text-sm italic text-red-500">{{ $message }}</span>
                    @enderror

                    @if ($profileTmp)
                        <x-ts:button xs class="mt-2" wire:click="updateAvatar" loading="updateAvatar">
                            Update
                        </x-ts:button>
                    @endif

                    <span class="text-primary-500 text-lg uppercase">
                        {{ $this->karyawan->nama }}
                    </span>
                    <span class="text-md">
                        {{ $this->karyawan->nip }}
                    </span>
                </div>

                <div class="flex">
                    <span class="w-1/4">Jabatan </span> : {{ $this->karyawan->jabatan?->first()->nama ?? '-' }}
                </div>
                <div class="flex">
                    <span class="w-1/4">Status</span> :
                    <x-filament::badge color="{{ $this->karyawan->status->color() }}">
                        {{ $this->karyawan->status->nama() }}
                    </x-filament::badge>
                </div>
                <div class="flex">
                    <span class="w-1/4">Masa Kerja</span> : {{ $this->karyawan->masakerja }}
                </div>
                <div class="flex">
                    <span class="w-1/4">Sisa Cuti</span> :
                    @if ($this->sisaCuti < 0)
                        <span class="italic text-red-500"> Masa kerja < 1 Th</span>
                            @else
                                {{ $this->sisaCuti }} Hari
                    @endif
                </div>
                <div class="flex">
                    <span class="w-1/4">Role </span> : {{ $user?->getRoleNames()->isEmpty() ? 'Not Assign Roles' : $user->getRoleNames()->implode(', ') }}
                </div>
            </div>
        </div>
        <div class="w-full lg:w-3/4">

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


                <x-ts:tab.items tab="Cuti">
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
