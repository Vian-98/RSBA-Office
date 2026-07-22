<div class="flex flex-col gap-4">
    <div class="rounded-lg bg-white p-4 shadow-sm" x-data="{ tab: @entangle('tab') }">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button wire:click="$set('tab', 'izin-cuti')" @click="tab = 'izin-cuti'"
                    :class="tab === 'izin-cuti' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Izin & Cuti Karyawan
                </button>
                @can('view-kepegawaian-cuti-bersama')
                    <button wire:click="$set('tab', 'cuti-bersama')" @click="tab = 'cuti-bersama'"
                        :class="tab === 'cuti-bersama' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                        class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                        Cuti Bersama
                    </button>
                @endcan
            </nav>
        </div>

        <div class="mt-4">
            @if($tab === 'izin-cuti')
                <div class="flex flex-col gap-2">
                    @can('create-cuti-other-karyawan')
                        <div class="ml-auto flex w-full justify-end rounded-md bg-white px-4 py-2">
                            <x-ts:button sm x-on:click="$dispatch('open-modal',{id:'create-cuti'})" icon="tabler.plus">
                                Buat Cuti
                            </x-ts:button>
                        </div>
                    @endcan

                    <div class="w-full rounded-md bg-white p-4">
                        <livewire:Surat.Cuti.TableCuti :key="Str::random()" />
                    </div>
                </div>
            @endif

            @if($tab === 'cuti-bersama')
                @can('view-kepegawaian-cuti-bersama')
                    @livewire('kepegawaian.cuti-bersama.index')
                @endcan
            @endif
        </div>
    </div>

    <x-filament::modal id="create-cuti" width="lg" :close-by-clicking-away="false">
        <x-slot name="heading">
            Surat Cuti Baru
        </x-slot>

        <livewire:Surat.Cuti.Add :key="Str::random()" @created-cuti="$refresh" />
    </x-filament::modal>

</div>
