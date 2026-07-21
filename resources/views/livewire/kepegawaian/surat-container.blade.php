<div class="flex flex-col gap-4">
    <div class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">
                Manajemen Surat Kepegawaian
            </h2>
            <p class="text-sm text-gray-500">
                Pengelolaan Surat Cuti Bersama, Permohonan Cuti/Izin, SP3, dan Verifikasi TTE Dokumen.
            </p>
        </div>
    </div>

    <div class="rounded-lg bg-white p-4 shadow-sm" x-data="{ tab: @entangle('tab') }">
        <div class="border-b border-gray-200 overflow-x-auto">
            <nav class="-mb-px flex space-x-8 min-w-max" aria-label="Tabs">
                @if(in_array('cuti-bersama', $allowedTabs))
                    <button wire:click="$set('tab', 'cuti-bersama')" @click="tab = 'cuti-bersama'"
                        :class="tab === 'cuti-bersama' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                        class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors flex items-center gap-2">
                        <x-ts:icon name="tabler.calendar-event" class="h-4 w-4" />
                        Cuti Bersama
                    </button>
                @endif

                @if(in_array('izin-cuti', $allowedTabs))
                    <button wire:click="$set('tab', 'izin-cuti')" @click="tab = 'izin-cuti'"
                        :class="tab === 'izin-cuti' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                        class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors flex items-center gap-2">
                        <x-ts:icon name="tabler.file-text" class="h-4 w-4" />
                        Izin dan Cuti
                    </button>
                @endif

                @if(in_array('sp3', $allowedTabs))
                    <button wire:click="$set('tab', 'sp3')" @click="tab = 'sp3'"
                        :class="tab === 'sp3' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                        class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors flex items-center gap-2">
                        <x-ts:icon name="tabler.file-alert" class="h-4 w-4" />
                        SP3
                    </button>
                @endif

                @if(in_array('verifikasi', $allowedTabs))
                    <button wire:click="$set('tab', 'verifikasi')" @click="tab = 'verifikasi'"
                        :class="tab === 'verifikasi' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                        class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors flex items-center gap-2">
                        <x-ts:icon name="tabler.file-check" class="h-4 w-4" />
                        Verifikasi
                    </button>
                @endif
            </nav>
        </div>

        <div class="mt-4">
            @if($tab === 'cuti-bersama' && in_array('cuti-bersama', $allowedTabs))
                @livewire('kepegawaian.cuti-bersama.index')
            @elseif($tab === 'izin-cuti' && in_array('izin-cuti', $allowedTabs))
                @livewire('surat.cuti.index')
            @elseif($tab === 'sp3' && in_array('sp3', $allowedTabs))
                @livewire('surat.sp3.index')
            @elseif($tab === 'verifikasi' && in_array('verifikasi', $allowedTabs))
                @livewire('surat.verifikasi.index')
            @endif
        </div>
    </div>
</div>
