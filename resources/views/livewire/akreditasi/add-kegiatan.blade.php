<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-4" autocomplete="off">
        <div class="flex w-full flex-col gap-2">
            <x-ts:input wire:model.defer='nama' placeholder="Nama Kegiatan" />
            <x-ts:date wire:model.defer='tanggal' placeholder="Rencana Dilaksanakan" />
            <x-ts:select.styled wire:model.defer='standar' searchable :options="$optionsStandar" select="label:nama|value:value">

                <x-slot:after>
                    <div class="mb-2 flex items-center justify-center px-2">
                        <x-ts:button sm x-on:click="show = false; $dispatch('open-modal', {id:'modal-new-standar-akre'}); $wire.set('newStandar',search)">
                            <span x-html="`Buat <b>${search}</b>`"></span>
                        </x-ts:button>
                    </div>
                </x-slot:after>
            </x-ts:select.styled>

            <x-ts:input wire:model.defer='lembaga' placeholder="Lembaga Survey" />
        </div>

        <div class="ml-auto flex justify-end gap-2">
            <x-ts:button outline x-on:click="$dispatch('close-modal',{id:'modal-add-kegiatan-akre'})">Tutup</x-ts:button>
            <x-ts:button type="submit" loading="submit" icon="tabler.checks">Simpan</x-ts:button>
        </div>

    </form>

    <x-filament::modal id="modal-new-standar-akre">
        <form wire:submit.prevent='submit_new_standar' class="flex flex-col gap-4" autocomplete="off">
            <x-ts:input wire:model.defer='newStandar' placeholder="Standar Baru" />

            <div class="ml-auto flex justify-end gap-2">
                <x-ts:button sm outline x-on:click="$dispatch('close-modal',{id:'modal-new-standar-akre'})">Tutup</x-ts:button>
                <x-ts:button sm type="submit" loading="submit" icon="tabler.checks">Simpan</x-ts:button>
            </div>
        </form>
    </x-filament::modal>

</div>
