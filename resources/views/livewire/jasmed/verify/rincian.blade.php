<div class="flex flex-col gap-4">
    <div class="grid grid-cols-3 gap-2 text-sm lg:grid-cols-6">
        <div class="flex flex-col">
            <span class="text-xs italic text-gray-500">Nama Pasien</span>
            <span>{{ $jmPasien?->nama_pasien }}</span>
        </div>
        <div class="flex flex-col">
            <span class="text-xs italic text-gray-500">No Rekmedis</span>
            <span>{{ $jmPasien?->no_rekmedis }}</span>
        </div>
        <div class="flex flex-col">
            <span class="text-xs italic text-gray-500">SEP</span>
            <span>{{ $jmPasien?->sep }}</span>
        </div>
        <div class="flex flex-col">
            <span class="text-xs italic text-gray-500">Tgl Checkout</span>
            <span>{{ $jmPasien?->tgl_checkout }}</span>
        </div>
        <div class="flex flex-col">
            <span class="text-xs italic text-gray-500">Klaim</span>
            <span>{{ number_format($jmPasien?->klaim, 2, ',', '.') }}</span>
        </div>
        <div class="flex flex-col">
            <span class="text-xs italic text-gray-500">Disetujui</span>
            <span>{{ number_format($jmPasien?->disetujui, 2, ',', '.') }}</span>
        </div>
        <div class="flex flex-col">
            <span class="text-xs italic text-gray-500">DPJP</span>
            <span>{{ $jmPasien?->dpjp }}</span>
        </div>
        <div class="flex flex-col">
            <span class="text-xs italic text-gray-500">Diaglist</span>
            <span>{{ $jmPasien?->diaglist }}</span>
        </div>

        <div class="flex flex-col">
            <span class="text-xs italic text-gray-500">Proclist</span>
            <span>{{ $jmPasien?->proclist }}</span>
        </div>
        <div class="flex flex-col">
            <span class="text-xs italic text-gray-500">Kelompok Jasa</span>
            <span>{{ $jmPasien?->kelompok }}</span>
        </div>
    </div>
    <hr class="my-2 border-gray-300">
    <div class="grid w-full grid-cols-2 gap-4 lg:w-1/2">
        <div class="flex rounded-md bg-gray-200/50 p-2 text-lg text-gray-500">
            <span>Total Billing </span>: {{ number_format($this->getTotal, 2, ',', '.') }}
        </div>
        <div class="flex rounded-md bg-indigo-200/50 p-2 text-lg text-indigo-500">
            <span>Billing Jasa </span> : {{ number_format($jmPasien?->prosentase->total_billing, 2, ',', '.') }}
        </div>
    </div>


    <form wire:submit.prevent='recalc' class="flex flex-col gap-2">

        <div class="flex flex-row items-end gap-2" x-data="{
            manual: {{ !empty($real_billing_jasa) && $real_billing_jasa != 0 ? 'true' : 'false' }}
        }" x-init="$watch(
            'manual',
            val => {
                if (!val) $wire.set('real_billing_jasa', 0)
            })">

            <div class="flex flex-col">
                <span class="text-sm italic text-indigo-500">Chosaring</span>
                <input type="text" wire:model.defer="chosaring" class="h-6 max-w-48 rounded-md border-gray-300 text-sm">
            </div>

            <div class="flex flex-col">
                <span class="text-sm italic text-indigo-500">Riil Billing Manual</span>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="manual_toggle" x-model="manual" class="rounded border-gray-300 text-indigo-500">
                    <label for="manual_toggle" class="text-sm text-gray-600">Aktifkan</label>
                </div>
            </div>

            <div class="flex flex-col" x-show="manual" x-transition>
                <span class="text-sm italic text-indigo-500">Nominal Manual</span>
                <input type="text" wire:model.live.debounce.500="real_billing_jasa" x-on:input="manual = ($event.target.value != '' && $event.target.value != '0')"
                    class="h-6 max-w-48 rounded-md border-gray-300 text-sm" :disabled="!manual">
            </div>

            <span x-show="manual" x-transition class="self-end text-xs italic text-amber-500">
                Jasa akan dihitung berdasarkan riil billing manual.
            </span>
        </div>

        <div class="grid w-full grid-cols-3 gap-4 lg:grid-cols-6">
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Prosedur Non Bedah</span>
                <input type="text" wire:model.defer='prosedur_non_bedah' class="h-6 w-auto rounded-md border-gray-300 text-sm">

            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Prosedur Bedah</span>
                <input type="text" wire:model.defer='prosedur_bedah' readonly class="h-6 w-auto rounded-md border-red-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Konsultasi</span>
                <input type="text" wire:model.defer='konsultasi' readonly class="h-6 w-auto rounded-md border-red-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Tenaga Ahli</span>
                <input type="text" wire:model.defer='tenaga_ahli' readonly class="h-6 w-auto rounded-md border-red-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Keperawatan</span>
                <input type="text" wire:model.defer='keperawatan' readonly class="h-6 w-auto rounded-md border-red-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Penunjang</span>
                <input type="text" wire:model.defer='penunjang' class="h-6 w-auto rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Radiologi</span>
                <input type="text" wire:model.defer='radiologi' class="h-6 w-auto rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Laboratorium</span>
                <input type="text" wire:model.defer='laboratorium' class="h-6 w-auto rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Pelayanan Darah</span>
                <input type="text" wire:model.defer='pelayanan_darah' class="h-6 w-auto rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Rehabilitasi</span>
                <input type="text" wire:model.defer='rehabilitasi' class="h-6 w-auto rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Kamar Akomodasi</span>
                <input type="text" wire:model.defer='kamar_akomodasi' class="h-6 w-auto rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Rawat Intensif</span>
                <input type="text" wire:model.defer='rawat_intensif' class="h-6 w-auto rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Obat</span>
                <input type="text" wire:model.defer='obat' class="h-6 w-auto rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Alkes</span>
                <input type="text" wire:model.defer='alkes' class="h-6 w-auto rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">BMHP</span>
                <input type="text" wire:model.defer='bmhp' class="h-6 w-auto rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Sewa Alat</span>
                <input type="text" wire:model.defer='sewa_alat' class="h-6 w-auto rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Obat Kronis</span>
                <input type="text" wire:model.defer='obat_kronis' class="h-6 w-auto rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex flex-col">
                <span class="text-sm italic text-gray-500">Obat Kemo</span>
                <input type="text" wire:model.defer='obat_kemo' class="h-6 w-auto rounded-md border-gray-300 text-sm">
            </div>
        </div>

        <div class="">
            <x-ts:button type="submit" loading="recalc" color="red" sm icon="tabler.refresh">Re-Calculate</x-ts:button>
        </div>
    </form>

    <hr class="my-2 border-gray-300">
    <div>
        <span class="text-indgo-500 font-semibold italic"> Prosentase Jasa</span>
        <livewire:Jasmed.Verify.Prosentase :jmProsentase="$jmProsentase" key="prosentase" />
    </div>
    <div>
        <span class="font-semibold italic text-indigo-500">Jasa Dokter</span>
        <livewire:Jasmed.Verify.Jasa :prosentaseId="$jmPasien?->prosentase?->id" key="jasa" />
    </div>
</div>
