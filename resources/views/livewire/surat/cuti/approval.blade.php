<div class="flex flex-col gap-2">
    {{-- detail surat cuti --}}
    <div class="flex w-full flex-col rounded-md border border-indigo-200 px-4 py-2 text-sm">
        <span class="text-indigo-500">#{{ $suratCuti->no_surat }}</span>
        <span>{{ $suratCuti->karyawan?->nama }}</span>
        <span>{{ $suratCuti->lama_cuti }} Hari</span>
        <span>Dari <b>{{ Carbon\Carbon::parse($suratCuti->tgl_mulai)->format('d M Y') }}</b> s/d <b>{{ Carbon\Carbon::parse($suratCuti->tgl_akhir)->format('d M Y') }}</b></span>
    </div>

    <form wire:submit.prevent='submit' class="flex flex-col gap-2 rounded-md border border-indigo-200 px-4 py-2">
        <div x-data="{ status: @entangle('status') }">
            <span class="italic text-indigo-500">Persetujuan</span>
            <div class="flex w-full flex-col gap-2">
                <div class="flex w-full sm:w-1/2 flex-wrap gap-2 justify-between">
                    @foreach ($optionsApproval as $item)
                        <div @click="status = '{{ $item['value'] }}'"
                            :class="status === '{{ $item['value'] }}'
                                ?
                                'bg-{{ $item['color'] }}-200' :
                                ''"
                            class="rounded-md p-1">

                            <x-ts:radio sm wire:model='status' id="{{ $item['value'] }}" value="{{ $item['value'] }}" label="{{ $item['label'] }}" color="{{ $item['color'] }}" />
                        </div>
                    @endforeach
                </div>

                <div class="w-full" x-show="status == 'pending' || status == 'rejected'">
                    <x-ts:textarea wire:model='keterangan' placeholder="Keterangan" resize-auto class="h-12" />
                </div>
            </div>
        </div>

        {{-- Simpan Actions --}}
        <div class="ml-auto flex flex-row justify-end gap-2">
            <x-ts:button type="submit" sm color="green" icon="tabler.checks" loading="submit">
                Simpan
            </x-ts:button>
        </div>
        {{-- End Simpan Actions --}}

    </form>
</div>
