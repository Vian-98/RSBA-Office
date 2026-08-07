<!-- Payroll Input Modal -->
<x-ts:modal wire="isInputModalOpen" size="4xl" class="relative z-50">
    <x-slot:title>
        <span class="flex items-center gap-1.5 font-bold text-slate-800">
            <x-tabler-calculator class="h-5 w-5 text-indigo-500" />
            {{ $isLocked ? 'Rincian Data Gaji (Terkunci)' : 'Input Data Gaji' }} - Periode {{ \Carbon\Carbon::parse($this->periode . '-01')->translatedFormat('F Y') }}
        </span>
    </x-slot:title>

    @if($selectedKaryawan)
        <div x-data="{
            formatNominal(val) {
                if (val === 0 || val === '0') return '0';
                return String(val || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        }" class="p-2 space-y-5">
            @include('livewire.gaji.partials.modal-input.header')

            <form wire:submit.prevent="savePayroll" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    @include('livewire.gaji.partials.modal-input.earnings')
                    @include('livewire.gaji.partials.modal-input.deductions')
                </div>

                @include('livewire.gaji.partials.modal-input.summary-footer')
            </form>
        </div>
    @endif
</x-ts:modal>
