<div x-data="{
        wasSidebarCollapsed: false,
        collapsSidebar() {
            this.wasSidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            if (!this.wasSidebarCollapsed) {
                window.dispatchEvent(new CustomEvent('force-sidebar-collapse'));
                localStorage.setItem('sidebar-collapsed', 'true');
            }
        },
        restoreSidebar() {
            if (!this.wasSidebarCollapsed) {
                window.dispatchEvent(new CustomEvent('force-sidebar-expand'));
                localStorage.setItem('sidebar-collapsed', 'false');
            }
        }
     }"
     @open-modal.window="if ($event.detail.id === 'modal-so-input' || $event.detail.id === 'modal-so-investigasi') collapsSidebar()"
     @close-modal.window="if ($event.detail.id === 'modal-so-input' || $event.detail.id === 'modal-so-investigasi') restoreSidebar()"
>
    {{ $this->table }}


    <x-filament::modal id="modal-so-input" :close-by-clicking-away="false" :autofocus="false" width="screen">
        <x-slot:heading>
            <div class="pl-24">Stok Opname</div>
        </x-slot:heading>

        <livewire:StokOpname.Input :id="$selectedId" :key="'so-input-' . $selectedId" />
    </x-filament::modal>

    <x-filament::modal id="modal-so-investigasi" :close-by-clicking-away="false" :autofocus="false" width="7xl">
        <x-slot:heading>Investigasi Opname</x-slot:heading>

        <livewire:StokOpname.Investigasi :id="$selectedId" :key="'so-investigasi-' . $selectedId" @opname-validasi-saved="$refresh" />
    </x-filament::modal>
</div>

