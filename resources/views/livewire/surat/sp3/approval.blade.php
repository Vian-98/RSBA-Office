<div class="flex flex-col gap-2">
    <livewire:Surat.Sp3.Details :$suratSp3 :key="Str::random()" />

    <form x-data="approvalSp3" wire:submit.prevent='submit' class="flex flex-col gap-4 rounded-md border border-indigo-200 px-4 py-2">
        <div>
            <span class="italic text-indigo-500">Persetujuan</span>
            <div class="flex w-full flex-col gap-2">
                <div class="flex w-1/2 justify-between">
                    @foreach ($optionsApproval as $item)
                        <div @click="setStatus('{{ $item['value'] }}')"
                            :class="status === '{{ $item['value'] }}'
                                ?
                                'bg-{{ $item['color'] }}-200' :
                                ''"
                            class="rounded-md p-1">

                            <x-ts:radio sm wire:model='status' id="{{ $item['value'] }}" value="{{ $item['value'] }}" label="{{ $item['label'] }}" color="{{ $item['color'] }}" />
                        </div>
                    @endforeach
                </div>

                <div class="w-full" x-show="status === 'rejected'">
                    <x-ts:textarea wire:model='keterangan' placeholder="Keterangan" resize-auto class="h-12" />
                </div>
            </div>
        </div>

        {{-- Simpan Actions --}}
        <div class="ml-auto flex flex-row justify-end gap-2">
            <x-ts:button type="submit" sm color="green" icon="tabler.checks" loading="submit">
                <span x-text="btnSimpanTxt"></span>
            </x-ts:button>
        </div>
        {{-- End Simpan Actions --}}

    </form>
</div>
@script
    <script>
        Alpine.data('approvalSp3', () => {
            return {
                status: @entangle('status'),
                get btnSimpanTxt() {
                    const map = {
                        'manual': 'Simpan & Print',
                        'approved': 'Simpan',
                        'rejected': 'Simpan',
                    };
                    return map[this.status] ?? 'Simpan';
                },

                setStatus(value) {
                    this.status = value
                    if (this.status === 'manual') {
                        this.btnSimpanTxt = "Simpan & Print"
                    }
                }
            }
        })
    </script>
@endscript
