<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-4" autocomplete="off">
        <div class="flex w-full flex-col gap-2">

            <div x-data="{ showParent: false }" class="flex w-full flex-col gap-2 rounded-md border border-gray-200 p-2">
                <div class="flex gap-4">
                    <x-ts:radio wire:model.defer='bab' id="bab" value="bab" label="Bab" x-on:click="showParent=false;$wire.set('parent',null)" />
                    <x-ts:radio wire:model.defer='bab' id="bab" value="sub" label="Sub Bab" x-on:click="showParent=true; $wire.set('parent',null)" />
                </div>

                <div x-show="showParent">
                    <x-ts:select.styled wire:model.defer='parent' searchable :options="$this->bab()" select="label:nama|value:value" placeholder="Pilih Bab Utama">
                    </x-ts:select.styled>
                </div>
            </div>

            <div class="mt-4 flex w-full flex-col gap-2 rounded-md border border-gray-200 p-2">
                <div class="flex gap-4">
                    <x-ts:radio wire:model.defer='jenisPenomoran' id="nomor" value="alfabet" label="Alafabet" />
                    <x-ts:radio wire:model.defer='jenisPenomoran' id="nomor" value="nomor" label="Numeric" />
                </div>

                <x-ts:input wire:model.defer='nama' placeholder="Standar" />

                {{-- form input menggunakan filament --}}
                {{ $this->form }}
            </div>

            {{-- <x-ts:textarea wire:model.defer='deskripsi' placeholder="Deskripsi"></x-ts:textarea> --}}
            {{-- <x-ts:textarea wire:model.defer='maksud_tujuan' placeholder="Maksud Tujuan Standar"></x-ts:textarea> --}}


        </div>

        <div class="ml-auto flex justify-end gap-2">
            <x-ts:button outline x-on:click="$dispatch('close-modal',{id:'modal-add-kegiatan-akre'})">Tutup</x-ts:button>
            <x-ts:button type="submit" loading="submit" icon="tabler.checks">Simpan</x-ts:button>
        </div>

    </form>
</div>
