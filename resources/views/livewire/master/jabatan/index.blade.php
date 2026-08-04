<div class="flex flex-col gap-3">
    @php
        $active = 'text-sm font-bold border-b-2 border-indigo-600 bg-indigo-50 text-indigo-600';
    @endphp

    <div class="flex w-full flex-row items-center justify-between rounded-lg bg-white p-2 shadow-sm border border-slate-100">
        <div class="flex items-center space-x-1">
            <x-ts:button sm flat wire:click="selectTab('table')" @class([$active => $activeTab === 'table'])>
                <x-ts:icon name="tabler.table" class="h-4 w-4 mr-1" />
                Daftar Jabatan
            </x-ts:button>

            <x-ts:button sm flat wire:click="selectTab('chart')" @class([$active => $activeTab === 'chart'])>
                <x-ts:icon name="tabler.sitemap" class="h-4 w-4 mr-1" />
                Bagan Struktur Organisasi
            </x-ts:button>
        </div>

        <div>
            @if($activeTab === 'table')
                <x-ts:button sm icon="tabler.user-plus" x-on:click="$dispatch('open-modal', {id:'new-jabatan'})">
                    Jabatan Baru
                </x-ts:button>
            @endif
        </div>
    </div>

    @if($activeTab === 'table')
        <div class="relative items-center overflow-x-auto rounded-lg bg-white px-4 py-2 shadow-sm border border-slate-100">
            <livewire:Master.Jabatan.JabatanTable :key="'table-'.$activeTab" />
        </div>
    @elseif($activeTab === 'chart')
        <div class="w-full">
            <livewire:Master.Jabatan.OrgChart :key="'chart-'.$activeTab" />
        </div>
    @endif

    {{-- Modal new Jabatan --}}
    <x-filament::modal id="new-jabatan" :autofocus="false">
        <x-slot name="heading">
            Jabatan Baru
        </x-slot>
        {{-- form --}}
        <livewire:Master.Jabatan.Add @new-jabatan-created="$refresh" :key="Str::random()" />
    </x-filament::modal>
</div>
