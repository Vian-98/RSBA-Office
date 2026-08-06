<div class="space-y-6">
    <!-- Header Page -->
    @include('livewire.gaji.rekap.partials.header')

    <!-- Quick Stats Section -->
    @include('livewire.gaji.rekap.partials.stats-cards')

    <!-- Main Content Area: Chart & Table stacked on Left, Breakdown & Parameter Aktif stacked on Right -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:items-stretch">
        <!-- Left: 6-Month Trend & Table -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            @include('livewire.gaji.rekap.partials.trend-chart')
            @include('livewire.gaji.rekap.partials.history-table')
        </div>

        <!-- Right: Breakdown & Parameter Aktif -->
        <div class="flex flex-col gap-6">
            @include('livewire.gaji.rekap.partials.bagian-breakdown')
            @include('livewire.gaji.rekap.partials.parameter-card')
        </div>
    </div>

    <!-- Modals -->
    @include('livewire.gaji.rekap.partials.modal-parameters')
    @include('livewire.gaji.rekap.partials.modal-finalisasi')
    @include('livewire.gaji.rekap.partials.modal-potongan-breakdown')
    @include('livewire.gaji.rekap.partials.modal-auto-send')
    @include('livewire.gaji.rekap.partials.modal-batch-send')
</div>
