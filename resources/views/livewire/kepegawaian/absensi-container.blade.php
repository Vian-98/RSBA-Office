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
                <button @click="tab = 'kontrol'"
                    :class="tab === 'kontrol' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Kontrol (Log Harian)
                </button>
                <button @click="tab = 'rekap'"
                    :class="tab === 'rekap' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Rekapitulasi
                </button>
                <button @click="tab = 'koreksi'"
                    :class="tab === 'koreksi' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium flex items-center gap-1.5">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    Koreksi Absensi
                </button>
            </nav>
        </div>

        <div class="mt-4">
            <div x-show="tab === 'kontrol'" x-cloak>
                @livewire('kepegawaian.absensi.index')
            </div>
            <div x-show="tab === 'rekap'" x-cloak>
                @livewire('kepegawaian.absensi.rekap')
            </div>
            <div x-show="tab === 'koreksi'" x-cloak>
                @livewire('kepegawaian.absensi.koreksi')
            </div>
        </div>
    </div>
</div>

