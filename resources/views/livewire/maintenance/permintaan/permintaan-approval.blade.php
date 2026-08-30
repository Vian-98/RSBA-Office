<div class="flex w-full flex-col gap-2">
    @php
        $color = match ($maintenanceRequest->priority) {
            'normal' => 'bg-blue-100 text-blue-800',
            'penting' => 'bg-yellow-100 text-yellow-800',
            'darurat' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };

        $priorityLabel = match ($maintenanceRequest->priority) {
            'normal' => 'Normal',
            'penting' => 'Penting',
            'darurat' => 'Darurat',
            default => 'Tidak Diketahui',
        };
    @endphp
    <span class="{{ $color }} flex flex-row justify-between rounded-md p-2 text-lg font-semibold">
        <div>
            <span class="text-gray-500">#{{ $maintenanceRequest->id }}</span>
            <span>
                {{ $maintenanceRequest->asset?->kode ?? 'NON-ASET' }} - {{ $maintenanceRequest->item_nama }}
            </span>
        </div>

        <span class="me-2 flex items-center justify-end text-sm italic text-gray-500"> {{ $priorityLabel }}</span>
    </span>
    <div class="flex flex-col gap-2 p-2 text-sm">
        <span class="text-sm italic text-gray-500">Detail</span>
        <div class="grid grid-cols-2 items-center gap-2">
            <div class="flex flex-col gap-1">
                <span>Item : {{ $maintenanceRequest->item_nama }}</span>
                <span>Lokasi : {{ $maintenanceRequest->lokasi_nama }}</span>
                <span>Pengaju : {{ $maintenanceRequest->user_request }}</span>

            </div>
            <div class="flex flex-col gap-1">
                <span>Prioritas : {{ Str::ucfirst($maintenanceRequest->priority) }}</span>
                <span>Alasan : {{ $maintenanceRequest->ket_priority }}</span>
                <span role="button" x-on:click="$dispatch('open-modal',{id:'modal-lampiran-permintaan'})" class="flex flex-row items-center gap-1">
                    <x-ts:icon name="tabler.paperclip" class="h-4 w-auto" />
                    <span class="text-indigo-500">
                        {{ count($maintenanceRequest->lampiran ?? []) }}</span>
                    Lampiran
                </span>
            </div>
        </div>
        <span class="col-span-2">Kendala / Masalah :
            <p class="ms-4">{{ $maintenanceRequest->note }}</p>
        </span>
    </div>

    <form wire:submit.prevent="submit" class="flex flex-col gap-4">
        <legend class="border-t border-gray-200"></legend>
        <div x-data="{ approval: @entangle('approval') }" class="flex flex-col gap-2 text-sm">
            <span class="text-sm italic text-gray-500">Persetujuan</span>
            <div class="mb-4 flex flex-row gap-4">
                <x-ts:radio sm wire:model.defer='approval' color="green" id="approved" value="approved" label="Setujui, Jadwalkan" />
                <x-ts:radio sm wire:model.defer='approval' color="red" id="rejected" value="rejected" label="Ditolak" />
            </div>

            <div class="grid grid-cols-1 gap-2 lg:grid-cols-2" x-show="approval === 'approved'">
                <div class="lg:col-span-2">
                    <x-ts:select.styled wire:model.defer='teknisi_id' placeholder="Teknisi" :request="route('api.users.ref')" select="label:nama|value:id" multiple />
                </div>
                <x-ts:date wire:model.defer='jadwal' placeholder="Jadwal" />
                <x-ts:select.styled wire:model.defer='priority' :options="$priorityOptions" select="label:label|value:value" />

                <div class="lg:col-span-2">
                    <x-ts:textarea wire:model.defer='catatan_teknisi' placeholder="Catatan Untuk Teknisi" />
                </div>
            </div>

            <div class="flex flex-col gap-2" x-show="approval === 'rejected'">
                <x-ts:textarea wire:model.defer='ket_reject' placeholder="Catatan Jika Tidak Disetujui" />
            </div>

        </div>

        <div class="flex flex-row justify-end gap-2">
            <x-ts:button sm type="submit" icon="tabler.checks">
                Simpan
            </x-ts:button>
        </div>
    </form>


    {{-- Modal lampiran --}}
    <x-filament::modal id="modal-lampiran-permintaan" width="max-w-3xl">
        <x-slot name="heading">
            <span class="text-lg font-semibold">Lampiran Permintaan</span>
        </x-slot>

        <x-image-gallery :images="$maintenanceRequest->lampiran" />
    </x-filament::modal>

</div>
