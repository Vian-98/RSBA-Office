<div class="flex flex-col gap-4">
    <div class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">
                Kontrol Absensi
            </h2>
            <p class="text-sm text-gray-500">
                Monitoring harian dan rekapitulasi data absensi.
            </p>
        </div>
    </div>

    <div class="rounded-lg bg-white p-4 shadow-sm" x-data="{ tab: @entangle('tab') }">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button wire:click="$set('tab', 'kontrol')" @click="tab = 'kontrol'"
                    :class="tab === 'kontrol' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Kontrol (Log Harian)
                </button>
                @can('view-kepegawaian-absensi')
                <button wire:click="$set('tab', 'rekap')" @click="tab = 'rekap'"
                    :class="tab === 'rekap' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Rekapitulasi
                </button>
                @endcan
            </nav>
        </div>

        <div class="mt-4">
            @if($tab === 'kontrol')
                <livewire:kepegawaian.absensi.index lazy />
            @endif
            @can('view-kepegawaian-absensi')
                @if($tab === 'rekap')
                    <livewire:kepegawaian.absensi.rekap lazy />
                @endif
            @endcan
        </div>
    </div>
</div>

