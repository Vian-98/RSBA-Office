<div class="space-y-6">
    <!-- Header Page -->
    @include('livewire.gaji.rekap.partials.header')

    <!-- Quick Stats Section (Island Component) -->
    <livewire:gaji.rekap.stats-cards :periode="$periode" :key="'stats-'.$periode" />

    <!-- Row 1: Trend Chart & Distribusi Gaji per Bagian (Matched Height) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:items-stretch">
        <div class="lg:col-span-2">
            <livewire:gaji.rekap.trend-chart :periode="$periode" :key="'chart-'.$periode" />
        </div>
        <div class="lg:col-span-1">
            <livewire:gaji.rekap.bagian-breakdown :periode="$periode" :key="'breakdown-'.$periode" />
        </div>
    </div>

    <!-- Row 2: History Table & Parameter Aktif (Matched Height) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:items-stretch">
        <div class="lg:col-span-2">
            <livewire:gaji.rekap.history-table :periode="$periode" :key="'history-'.$periode" />
        </div>
        <div class="lg:col-span-1">
            <livewire:gaji.rekap.parameter-card :periode="$periode" :key="'params-'.$periode" />
        </div>
    </div>

    <!-- Modals -->
    @include('livewire.gaji.rekap.partials.modal-parameters')
    @include('livewire.gaji.rekap.partials.modal-finalisasi')
    @include('livewire.gaji.rekap.partials.modal-potongan-breakdown')
    @include('livewire.gaji.rekap.partials.modal-auto-send')
    @include('livewire.gaji.rekap.partials.modal-batch-send')
</div>
