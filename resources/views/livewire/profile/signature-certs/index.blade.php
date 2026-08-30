<div>
    @if ($this->getCertificate)
        @php

            $text = 'text-green-500';
            $bg = 'bg-green-100';
            $border = 'border-green-400';
            if ($this->getCertificate['is_expired']) {
                $text = 'text-red-500';
                $bg = 'bg-red-100';
                $border = 'border-red-400';
            }
        @endphp
        <div class="flex flex-col gap-2">
            <div class="{{ $bg }} w-full rounded-md p-4 lg:w-1/3">
                <span class="{{ $text }} flex flex-row items-center font-semibold">
                    <x-ts:icon name="tabler.certificate" class="w-10" />
                    Certifacate Digital {{ $this->getCertificate['is_expired'] ? 'Kadaluarsa' : 'Valid' }}
                </span>
                <div class="{{ $border }} mt-2 flex flex-col rounded-md border p-4">
                    <span class="{{ $text }} font-bold"> SN : {{ '.... ' . Str::substr($this->getCertificate['cert_info']->serialNumberHex, -25) }}</span>
                    <span class="{{ $text }} text-sm font-semibold uppercase">Issuer : </span>
                    @if (is_iterable($this->getCertificate['cert_info']->issuer))
                        @foreach ($this->getCertificate['cert_info']->issuer as $issuer)
                            <span class="ms-2 text-sm">{{ is_string($issuer) ? $issuer : json_encode($issuer) }}</span>
                        @endforeach
                    @else
                        <span class="ms-2 text-sm">{{ $this->getCertificate['cert_info']->issuer ?? 'RS Bintang Amin Authority' }}</span>
                    @endif
                    <span class="text-sm mt-2 block">
                        <span> Dibuat : </span>{{ $this->getCertificate['created'] }} <br>
                        <span> Valid Sampai : </span>{{ $this->getCertificate['expired'] }} <br>
                    </span>
                </div>
            </div>
            <div class="flex flex-row gap-2">
                <x-ts:button sm color="green" icon="tabler.refresh" x-on:click="$dispatch('open-modal',{id:'modal-new-certificate'})">Baru</x-ts:button>
                {{-- <x-ts:button sm icon="tabler.arrow-right-dashed" x-on:click="$dispatch('open-modal',{id:'modal-regenerate-certificate'})">Perpanjang</x-ts:button> --}}
            </div>
        </div>
    @else
        <div class="bg-warning-100 text-warning-500 mb-2 flex flex-row items-center gap-2 rounded-lg p-1 lg:w-fit">
            <x-ts:icon name="tabler.info-circle" color='orange' class="h-6" />
            <span>Anda belum memiliki certificate tanda tangan.</span>
        </div>

        <x-ts:button xs icon="tabler.plus" x-on:click="$dispatch('open-modal',{id:'modal-new-certificate'})">Buat Certificate</x-ts:button>
    @endif

    {{-- modal tambah certificate baru --}}
    <x-filament::modal id="modal-new-certificate">
        <livewire:Profile.SignatureCerts.Add :$users :key="'create-signature-' . auth()->id()" @cert-created="$refresh" />
    </x-filament::modal>

    <x-ts:modal title="Password" wire="modalPassw" x-on:open="$tsui.focus('pkcs12_password')" center persistent blur>
        <form wire:submit.prevent='parseCertificate' class="flex flex-col gap-2">
            <x-ts:password id="pkcs12_password" wire:model.defer='pkcs12_password' placeholder="Input passsword anda." />

            <x-ts:button sm type="submit" icon="tabler.corner-down-left" loading="parseCertificate">Submit</x-ts:button>
        </form>
    </x-ts:modal>


    {{-- modal regenerate certificate p12 --}}
    <x-filament::modal id="modal-regenerate-certificate">
        <livewire:Profile.SignatureCerts.Regenerate :$users :key="'renew-signature-' . auth()->id()" />
    </x-filament::modal>
</div>
