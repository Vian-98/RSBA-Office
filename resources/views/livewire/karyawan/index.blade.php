<div class="space-y-5">
    {{-- Header Card & Actions --}}
    <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        {{-- Navigation Button Tabs --}}
        <div class="overflow-x-auto scrollbar-hidden pb-1">
            <nav class="inline-flex gap-2.5 sm:gap-3 min-w-max p-1.5 bg-slate-100/80 rounded-2xl border border-slate-200/60" aria-label="Karyawan Tabs">
                <button wire:click="navigateTo('all')"
                    class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ empty($content) || $content === 'all' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                    <x-ts:icon name="tabler.users" class="h-4 w-4 shrink-0" />
                    Semua Karyawan
                </button>

                @can('view-dokter')
                    <button wire:click="navigateTo('dokter')"
                        class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ $content === 'dokter' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border border-indigo-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                        <x-ts:icon name="tabler.stethoscope" class="h-4 w-4 shrink-0" />
                        Dokter
                    </button>
                @endcan

                <button wire:click="navigateTo('resign')"
                    class="whitespace-nowrap rounded-xl py-2.5 px-5 text-xs sm:text-sm font-bold transition-all duration-150 flex items-center gap-2 {{ $content === 'resign' ? 'bg-rose-600 text-white shadow-md shadow-rose-200 border border-rose-600' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 font-semibold' }}">
                    <x-ts:icon name="tabler.briefcase-off" class="h-4 w-4 shrink-0" />
                    Resign
                </button>
            </nav>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 shrink-0">
            @switch($content)
                @case('all')
                    <x-ts:button sm icon="tabler.user-plus" class="py-2" x-on:click="$dispatch('open-modal', {id:'new-karyawan'})">
                        Karyawan Baru
                    </x-ts:button>

                    @can('export-karyawan')
                        <x-ts:dropdown>
                            <x-slot:action>
                                <x-ts:button.circle flat outline sm>
                                    <x-ts:icon name="tabler.dots-vertical" class="h-4 w-4 text-slate-600" />
                                </x-ts:button.circle>
                            </x-slot:action>
                            <x-ts:dropdown.items icon="tabler.upload" text="Import Karyawan" x-on:click="$dispatch('open-modal',{id:'import-karyawan'})" />
                            <x-ts:dropdown.items icon="tabler.download" text="Export Karyawan" separator wire:click='downloadKaryawan' />
                        </x-ts:dropdown>
                    @endcan
                @break

                @case('dokter')
                    <x-ts:button sm icon="tabler.stethoscope" class="py-2" x-on:click="$dispatch('open-modal', {id:'new-dokter'})">
                        Tambah Dokter
                    </x-ts:button>
                @break

                @default
            @endswitch
        </div>
    </div>

    {{-- Content Table Card --}}
    <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
        @switch($content)
            @case('all')
                <livewire:Karyawan.TableKaryawan :key="Str::random()" />
            @break

            @case('dokter')
                <livewire:Karyawan.Dokter.TableDokter :key="Str::random()" />
            @break

            @case('resign')
                <livewire:Karyawan.TableResign key="table-resign" />
            @break
        @endswitch
    </div>
</div>
