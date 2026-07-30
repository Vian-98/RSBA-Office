<div class="flex flex-col gap-3">
    <livewire:Asset.Title :assetBarang="$assetBarang" :key="'title-' . $assetBarang->id" />

    <div class="w-full rounded-md border border-gray-300">
        <x-ts:tab selected="Mutasi">

            {{-- Form Mutasi --}}
            <x-ts:tab.items tab="Mutasi">
                <form wire:submit.prevent="submitMutasi" class="flex flex-col gap-2">
                    <div class="flex flex-col gap-2 p-3">
                        @if ($is_inc_component)
                            <x-ts:checkbox wire:model.defer='is_inc_component' label="Termasuk Komponen Didalamnya." />
                        @endif
                        <x-ts:select.styled wire:model.defer='ruanganTujuan' searchable :request="route('api.ruangan')" select="label:nama|value:id" placeholder="Ruangan / Unit Tujuan" />

                        <x-ts:date wire:model.defer='tanggalMutasi' placeholder="Tgl Mutasi" />

                        <x-ts:textarea wire:model.defer='keterangan' placeholder="Keterangan" />
                    </div>

                    <div class="flex w-full items-center justify-end gap-2 p-3">
                        <x-ts:button type="submit" sm icon="tabler.device-desktop-share" loading="submitMutasi">
                            Mutasikan
                        </x-ts:button>
                    </div>
                </form>
            </x-ts:tab.items>


            {{-- Riwayat Mutasi --}}
            <x-ts:tab.items tab="Riwayat">
                <x-slot:left>
                    <x-ts:icon name="tabler.history" class="h-5 w-5" />
                </x-slot:left>

                <x-table-static :headers="$this->headers()" :rows="$this->rows()" :paginator="$this->riwayatMutasis()" striped>
                </x-table-static>

            </x-ts:tab.items>
        </x-ts:tab>

    </div>
</div>
