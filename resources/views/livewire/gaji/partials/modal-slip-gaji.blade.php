<!-- Salary Slip Modal (Detailed Print Layout) -->
<x-ts:modal wire="isOpenModal" size="3xl" class="relative z-50">
    <x-slot:title>
        <span class="flex items-center gap-1.5 font-bold text-slate-800">
            <x-tabler-file-invoice class="h-5 w-5 text-indigo-500" />
            Slip Gaji Karyawan
        </span>
    </x-slot:title>

    @if($selectedSlip)
        @include('livewire.gaji.partials.modal-slip.printable')
        @include('livewire.gaji.partials.modal-slip.audit-logs')

        <x-slot:footer>
            <div class="flex justify-end gap-2.5">
                <x-ts:button size="sm" flat color="slate" wire:click="closeModal">Tutup</x-ts:button>
                <x-ts:button size="sm" color="sky" class="font-bold text-white bg-sky-600 hover:bg-sky-700" wire:click="sendEmail({{ $selectedSlip['id'] }})" loading="sendEmail">
                    <x-tabler-mail class="mr-1.5 h-4 w-4" />
                    Kirim ke Email
                </x-ts:button>
                <x-ts:button size="sm" color="indigo" class="font-bold" onclick="printSalarySlip()">
                    <x-tabler-printer class="mr-1.5 h-4 w-4" />
                    Cetak Slip Gaji
                </x-ts:button>
            </div>
        </x-slot:footer>
    @endif
</x-ts:modal>

@include('livewire.gaji.partials.modal-slip.print-script')
