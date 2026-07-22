<div class="flex flex-col gap-2">

    <div class="flex w-full flex-row rounded-lg bg-white">
        <div class="ms-auto px-3 py-2 flex items-center gap-2">
            @if(auth()->user()?->isDokterOrApprover())
                <a href="{{ route('kepegawaian.jadwal-kerja.tukar-dokter') }}" class="px-3 py-1.5 text-xs font-semibold rounded-md bg-emerald-600 hover:bg-emerald-700 text-white shadow transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Tukar Shift Dokter
                </a>
            @endif
            @can('generate', App\Models\Sdm\JadwalKerja::class)
                <x-ts:button sm icon="tabler.plus" x-on:click="$dispatch('open-modal', {id:'generate-jadwal-kerja'})">
                    Generate Jadwal Baru
                </x-ts:button>
            @endcan
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
