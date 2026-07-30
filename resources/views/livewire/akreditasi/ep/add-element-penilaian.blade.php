<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-4" autocomplete="off">
        <div class="flex w-full flex-col gap-2">
            <x-ts:select.styled wire:model.defer='bab_id' searchable :options="$this->bab()" select="label:nama|value:value" placeholder="Standar">
            </x-ts:select.styled>

            <div class="flex gap-4">
                <x-ts:radio wire:model.defer='jenisPenomoran' id="nomor" value="alfabet" label="Alafabet" />
                <x-ts:radio wire:model.defer='jenisPenomoran' id="nomor" value="nomor" label="Numeric" />
            </div>

            <x-ts:input wire:model.defer='element' placeholder="Elelment Penilaian" />

            <x-ts:select.styled wire:model.defer='methode' searchable multiple :options="$this->methode()" select="label:label|value:value" placeholder="Methode">
            </x-ts:select.styled>


            <div>
                {{ $this->form }}
            </div>

            {{-- <x-ts:textarea wire:model.defer='kelengkapan' placeholder="Kelengkapan Bukti"></x-ts:textarea> --}}

            <x-ts:number wire:model.defer='target_nilai' min="0" max="10" step="5" placeholder="Target Nilai"></x-ts:number>

        </div>

        <div class="ml-auto flex justify-end gap-2">
            <x-ts:button outline x-on:click="$dispatch('close-modal',{id:'modal-new-penilaian'})">Tutup</x-ts:button>
            <x-ts:button type="submit" loading="submit" icon="tabler.checks">Simpan</x-ts:button>
        </div>

    </form>
</div>
