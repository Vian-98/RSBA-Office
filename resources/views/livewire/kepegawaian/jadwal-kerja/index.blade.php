<div class="flex flex-col gap-2">

    <div class="flex w-full flex-row rounded-lg bg-white">
        <div class="ms-auto px-3 py-2">
            <x-ts:button sm icon="tabler.plus" x-on:click="$dispatch('open-modal', {id:'generate-jadwal-kerja'})">
                Generate Jadwal Baru
            </x-ts:button>
        </div>
    </div>

    <div class="relative overflow-x-auto rounded-lg bg-white px-4 py-2">
        {{ $this->table }}
    </div>

    {{-- Modal Generate --}}
    <x-filament::modal id="generate-jadwal-kerja" width="md" :autofocus="false">
        <x-slot name="heading">
            Generate Jadwal Kerja Baru
        </x-slot>
        <livewire:Kepegawaian.JadwalKerja.Generate lazy @jadwal-kerja-generated="$refresh" />
    </x-filament::modal>

</div>
