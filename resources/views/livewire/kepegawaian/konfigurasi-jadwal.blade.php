<div class="flex flex-col gap-4">
    <div class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">
                Konfigurasi Jadwal
            </h2>
            <p class="text-sm text-gray-500">
                Pengaturan master data terkait jadwal kerja dan shift.
            </p>
        </div>
    </div>

    <div class="rounded-lg bg-white p-4 shadow-sm" x-data="{ tab: @entangle('tab') }">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="tab = 'aturan-jadwal'"
                    :class="tab === 'aturan-jadwal' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Aturan Jadwal
                </button>
                <button @click="tab = 'koordinator'"
                    :class="tab === 'koordinator' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Koordinator
                </button>
                <button @click="tab = 'master-shift'"
                    :class="tab === 'master-shift' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Master Shift
                </button>
                <button @click="tab = 'shift-ruangan'"
                    :class="tab === 'shift-ruangan' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Shift per Ruangan
                </button>
            </nav>
        </div>

        <div class="mt-4">
            <div x-show="tab === 'aturan-jadwal'" x-cloak>
                @livewire('master.jadwal-aturan.index')
            </div>
            <div x-show="tab === 'koordinator'" x-cloak>
                @livewire('master.bagian-koordinator.index')
            </div>
            <div x-show="tab === 'master-shift'" x-cloak>
                @livewire('master.jadwal-shift.index')
            </div>
            <div x-show="tab === 'shift-ruangan'" x-cloak>
                @livewire('master.ruangan-shift.index')
            </div>
        </div>
    </div>
</div>
