<div>
    <div class="flex flex-col rounded-md bg-white p-4">

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

    </div>
</div>
