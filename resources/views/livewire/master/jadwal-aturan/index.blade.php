<div class="flex flex-col gap-2">

    @can('manage-kepegawaian-master-aturan')
        <div class="flex w-full flex-row rounded-lg bg-white">
            <div class="ms-auto px-3 py-2 flex items-center gap-2">
                <x-ts:button sm color="rose" outline icon="tabler.rotate-2" wire:click="confirmResetAllToAturanUmum">
                    Reset / Samakan Semua ke Aturan Umum
                </x-ts:button>
                <x-ts:button sm icon="tabler.plus" x-on:click="$dispatch('open-modal', {id:'new-jadwal-aturan'})">
                    Tambah Aturan
                </x-ts:button>
            </div>
        </div>
    @endcan

    <div class="relative overflow-x-auto rounded-lg bg-white px-4 py-2">
        {{ $this->table }}
    </div>

    {{-- Modal new --}}
    <x-filament::modal id="new-jadwal-aturan" width="md" :autofocus="false">
        <x-slot name="heading">
            Tambah Aturan
        </x-slot>
        <livewire:Master.JadwalAturan.Add lazy @new-jadwal-aturan-created="$refresh" />
    </x-filament::modal>

    {{-- Modal edit --}}
    <x-filament::modal id="edit-jadwal-aturan" width="md" :autofocus="false">
        <x-slot name="heading">
            Edit Aturan
        </x-slot>
        @if($editingId)
            <livewire:Master.JadwalAturan.Edit lazy :key="$editingId" :id="$editingId" />
        @endif
    </x-filament::modal>
</div>
