<div class="space-y-6">
    <!-- Header Page -->
    @include('livewire.gaji.partials.header')

    <!-- Status Banners -->
    @include('livewire.gaji.partials.status-banner')

    <!-- Filters Panel -->
    @include('livewire.gaji.partials.filters')

    <!-- Salaries Table -->
    @include('livewire.gaji.partials.salaries-table')

    <!-- Modals -->
    @include('livewire.gaji.partials.modal-input-gaji')
    @include('livewire.gaji.partials.modal-slip-gaji')
    @include('livewire.gaji.partials.modal-period-log')
    @include('livewire.gaji.partials.modal-import-excel')
    @include('livewire.gaji.partials.modal-auto-send')
    @include('livewire.gaji.partials.modal-batch-send')
</div>
