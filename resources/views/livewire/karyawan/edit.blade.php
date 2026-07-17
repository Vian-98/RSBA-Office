<div class="w-full">
    <div class="mt-4 flex w-full flex-row">
        <span class="flex items-center gap-1 text-indigo-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-lg px-3 py-1.5 text-sm font-semibold transition-all duration-200 cursor-pointer" role="button" wire:click='directback'>
            <x-tabler-chevron-left class="w-4 h-4" />
            {{ __('Kembali') }}
        </span>
    </div>

    <div class="mt-4 flex flex-col gap-5 lg:flex-row lg:items-start">
        {{-- overview --}}
        <div class="flex w-full flex-col rounded-lg bg-white border border-gray-100/75 shadow-sm lg:sticky lg:top-5 lg:w-1/4">
            <div class="flex flex-col items-center justify-center p-6">
                <x-ts:avatar image="" class="h-32 w-32 shadow-sm" />
                <h2 class="text-indigo-600 text-xl font-bold mt-4 text-center">{{ $karyawan->nama }}</h2>
                <h2 class="text-gray-400 text-sm font-medium mt-1">{{ $karyawan->nip }}</h2>
            </div>

            <div class="flex flex-col space-y-3 border-t border-gray-100 p-5 text-sm">
                <div class="flex items-start">
                    <span class="w-24 shrink-0 font-medium text-gray-500">Nama</span>
                    <span class="mx-1 text-gray-400">:</span>
                    <span class="text-gray-800 font-semibold">
                        @php
                            $namaFull = '';
                            if (!empty($karyawan->gelar_depan)) {
                                $namaFull .= trim($karyawan->gelar_depan) . ' ';
                            }
                            $namaFull .= trim($karyawan->nama);
                            $gelars = array_filter([$karyawan->gelar_belakang, $karyawan->gelar_belakang2]);
                            if (!empty($gelars)) {
                                $namaFull .= ', ' . implode(', ', array_map('trim', $gelars));
                            }
                        @endphp
                        {{ $namaFull }}
                    </span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 shrink-0 font-medium text-gray-500">Jabatan</span>
                    <span class="mx-1 text-gray-400">:</span>
                    <span class="text-gray-800">{{ $karyawan->jabatan?->first()->nama ?? '-' }}</span>
                </div>
                <div class="flex items-center">
                    <span class="w-24 shrink-0 font-medium text-gray-500">Status</span>
                    <span class="mx-1 text-gray-400">:</span>
                    <x-filament::badge color="{{ $karyawan->status->color() }}">
                        {{ $karyawan->status->nama() }}
                    </x-filament::badge>
                </div>
                <div class="flex items-start">
                    <span class="w-24 shrink-0 font-medium text-gray-500">Masa Kerja</span>
                    <span class="mx-1 text-gray-400">:</span>
                    <span class="text-gray-800">{{ $karyawan->masakerja }}</span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 shrink-0 font-medium text-gray-500">Usia</span>
                    <span class="mx-1 text-gray-400">:</span>
                    <span class="text-gray-800">{{ $karyawan->usia }}</span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 shrink-0 font-medium text-gray-500">Kontak</span>
                    <span class="mx-1 text-gray-400">:</span>
                    <span class="text-gray-800">
                        @php
                            $phones = array_filter([$karyawan->hp, $karyawan->hp2]);
                        @endphp
                        {{ !empty($phones) ? implode(', ', $phones) : '-' }}
                    </span>
                </div>
                <div class="flex items-start">
                    <span class="w-24 shrink-0 font-medium text-gray-500">Email</span>
                    <span class="mx-1 text-gray-400">:</span>
                    <span class="text-gray-800 break-all">{{ $karyawan->user->email ?? 'Belum Register' }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 border-t border-gray-100 p-4">
                <x-ts:button loading="delete({{ $karyawan->id }})" wire:click="delete({{ $karyawan->id }})" class="bg-red-600 hover:bg-red-700 text-white font-medium shadow-sm transition-colors duration-200 justify-center" icon="tabler.trash">
                    Hapus
                </x-ts:button>

                {{-- action resign --}}
                <x-ts:button x-on:click="$tsui.open.modal('modal-resign-karyawan')" class="bg-amber-500 hover:bg-amber-600 text-white font-medium shadow-sm transition-colors duration-200 justify-center" icon="tabler.user-minus">
                    Resign
                </x-ts:button>
            </div>
        </div>



        {{-- update data --}}
        {{-- this section can scrollable --}}
        <div class="scrollbar-hidden flex max-h-screen w-full flex-col space-y-3 overflow-y-auto lg:max-h-[calc(100vh-20px)] lg:w-3/4">

            <x-collapsible-card title="Kedinasan" icon="tabler-briefcase" color="indigo">
                <livewire:Karyawan.EditKedinasan :id="$karyawan->id" :key="'dinas-' . $karyawan->id" @new-jabatan-created="$refresh" @status-updated="$refresh" />
            </x-collapsible-card>

            <x-collapsible-card title="Identitas" icon="tabler-user-edit" color="indigo" :defaultOpen="false">
                <livewire:Karyawan.EditIdentitas :id="$karyawan->id" :key="'identitas-' . $karyawan->id" @updated-karywan="$refresh" />
            </x-collapsible-card>

            <x-collapsible-card title="Pendidikan" icon="tabler-school" color="indigo" :defaultOpen="false">
                <livewire:Karyawan.Pendidikan.PendidikanList :id="$karyawan->id" :key="'pendidikan-' . $karyawan->id" @deleted-pendidikan-karyawan="$refresh" @pendidikan-karyawan-created="$refresh" />
            </x-collapsible-card>

            <x-collapsible-card title="Dokumen" icon="tabler-file-type-doc" color="indigo" :defaultOpen="false">
                <livewire:Karyawan.Document.DocumentList :id="$karyawan->id" :key="'dokumen-' . $karyawan->id" @document-karyawan-created="$refresh" @document-karyawan-deleted="$refresh" />
            </x-collapsible-card>
        </div>
    </div>


    {{-- modal form resign --}}
    <x-ts:modal id="modal-resign-karyawan" center title="Resign" x-on:karyawan-resign-updated.window="$tsui.close.modal('modal-resign-karyawan')">
        {{-- form --}}
        <livewire:Karyawan.Resign :id="$karyawan->id" :key="'modal-resign-' . $karyawan->id" />
    </x-ts:modal>
    {{-- end acton resign --}}
</div>
