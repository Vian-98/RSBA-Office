<div class="flex flex-col gap-4">
    <div class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">
                Konfigurasi Jadwal
            </h2>
            <p class="text-sm text-gray-500">
                Pengaturan master shift kerja. (Pengelolaan Koordinator Ruangan dapat diakses di menu Master Data Ruangan)
            </p>
        </div>
    </div>

    <div class="rounded-lg bg-white p-4 shadow-sm" x-data="{ tab: @entangle('tab') }">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button wire:click="$set('tab', 'master-shift')" @click="tab = 'master-shift'"
                    :class="tab === 'master-shift' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Master Shift
                </button>
                <button wire:click="$set('tab', 'aturan-jadwal')" @click="tab = 'aturan-jadwal'"
                    :class="tab === 'aturan-jadwal' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Aturan Jadwal
                </button>
            </nav>
        </div>

        <div class="mt-4">
            @if($tab === 'master-shift')
                @livewire('master.jadwal-shift.index')
            @endif

            @if($tab === 'aturan-jadwal')
                @livewire('master.jadwal-aturan.index')
            @endif
            
            {{-- Hidden tab, accessible only via URL ?tab=shift-ruangan --}}
            @if($tab === 'shift-ruangan')
                @livewire('master.ruangan-shift.index')
            @endif
        </div>
    </div>
</div>
