<div>
    <div class="flex flex-col gap-4 rounded-md bg-white p-4">

        {{-- Header Actions for Coordinator --}}
        @can('approval-maintenance')
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Maintenance & Service Aset</h2>
                    <p class="text-xs text-gray-500">Kelola permintaan, jadwal pengerjaan, dan riwayat pemeliharaan aset barang.</p>
                </div>
                <x-ts:button icon="tabler.plus" color="indigo" x-on:click="$dispatch('open-modal', { id: 'modal-direct-create-maintenance' })">
                    Buat Tiket Maintenance
                </x-ts:button>
            </div>
        @endcan

        <x-ts:tab :selected="auth()->user()->can('approval-maintenance') && $this->getHasNewRequestProperty() ? 'Permintaan' : 'Jadwal'">
            @can('approval-maintenance')
                <x-ts:tab.items tab="Permintaan" class="items-center">
                    @if ($this->getHasNewRequestProperty())
                        <x-slot:left>
                            <span class="absolute block h-1 w-1 animate-pulse rounded-full bg-red-500 ring-2 ring-red-300"></span>
                        </x-slot:left>
                    @endif
                    <livewire:Maintenance.Permintaan.ListPermintaan />
                </x-ts:tab.items>
            @endcan

            <x-ts:tab.items tab="Jadwal">
                <livewire:Maintenance.ListJadwal :key="'list-jadwal'" />
            </x-ts:tab.items>

            {{-- Tab Tiket Saya: semua orang bisa lihat --}}
            <x-ts:tab.items tab="Tiket Saya">
                <livewire:Maintenance.Ticket.MyTickets />
            </x-ts:tab.items>
        </x-ts:tab>

        {{-- Modal Direct Ticket Creation --}}
        @can('approval-maintenance')
            <x-filament::modal id="modal-direct-create-maintenance" width="max-w-3xl" :close-by-clicking-away="false">
                <x-slot:heading>
                    <div class="flex items-center gap-2">
                        <x-ts:icon name="tabler.ticket" class="h-5 w-5 text-indigo-600" />
                        <span class="text-base font-bold text-gray-800">Buat Tiket Maintenance Direct</span>
                    </div>
                </x-slot:heading>
                <livewire:Maintenance.Permintaan.DirectCreate :key="'direct-create-' . Str::random()" />
            </x-filament::modal>
        @endcan

    </div>
</div>
