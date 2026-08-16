<div class="flex flex-col gap-3">
    <livewire:Asset.Title :assetBarang="$assetBarang" :key="'title-' . $assetBarang->id" />
    <div class="w-full rounded-md border border-gray-300">
        <x-ts:tab selected="Riwayat">

            {{-- tabs riwayat permintaan --}}
            <x-ts:tab.items tab="Riwayat">
                <livewire:Maintenance.Permintaan.ListPermintaanByAssets :asset_id="$assetBarang->id" :key="'list-permintaan' . $assetBarang->id" />
            </x-ts:tab.items>

            {{-- tab form permintaan --}}
            <x-ts:tab.items tab="Permintaan">
                <form action="" class="flex flex-col gap-2" wire:submit.prevent="submit" autocomplete="off">

                    @if (count($targetAssetOptions) > 1)
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-semibold text-gray-600">Item yang Diperbaiki:</span>
                            <x-ts:select.styled wire:model.live='target_asset_id' placeholder="Pilih Item Perbaikan" :options="$targetAssetOptions" select="label:label|value:value" :clearable="false" />
                        </div>
                    @endif

                    <x-ts:select.styled wire:model.live.debounce.300ms='priority' placeholder="Jenis Permintaan" :options="$priorityPermintaan" select="label:label|value:value" />

                    @if (!$is_normal)
                        <x-ts:input wire:model.defer='ket_priority' placeholder="Alasan Urgent" />
                    @endif

                    <x-ts:textarea wire:model.defer='note' placeholder="Keterangan / Deskripsikan Masalah" />


                    <div class="mb-2 text-sm text-gray-500">Lampiran (Opsional)
                        <span class="justify-center rounded-full bg-indigo-500 px-1 text-white"> {{ count($lampirans) }}</span>
                        <x-ts:upload wire:model.defer='lampirans' multiple delete accept="images/png"></x-ts:upload>
                    </div>

                    <div class="flex w-full items-center justify-end gap-2">
                        <x-ts:button type="submit" sm icon="tabler.device-desktop-share" loading="submit">
                            Ajukan Permintaan
                        </x-ts:button>
                    </div>

                </form>
            </x-ts:tab.items>

            {{-- tab maintenance annual / berkala --}}
            <x-ts:tab.items tab="Maintenance Annual">
                <livewire:Maintenance.Permintaan.AnnualSchedule :asset_id="$assetBarang->id" :key="'annual-' . $assetBarang->id" />
            </x-ts:tab.items>

        </x-ts:tab>

    </div>
</div>
