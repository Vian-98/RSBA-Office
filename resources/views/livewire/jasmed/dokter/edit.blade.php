<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-2">
        <div x-data="{
            dokters: @entangle('dokters'),
            addDokter() {
                this.dokters.push({ dokter: '', status: '', jumlah: 1 });
            },
            removeDokter(index) {
                this.dokters.splice(index, 1);
            }
        }" class="flex flex-col gap-2">
            <template x-for="(dokter, index) in dokters" :key="index">
                <div class="grid grid-cols-6 justify-between gap-2 rounded-md p-1 hover:bg-gray-100">
                    <div class="col-span-3 flex items-center gap-2">
                        <x-ts:icon name="tabler.trash" class="text-red-500" role="button" @click="removeDokter(index)" />
                        <div class="w-full">
                            <x-ts:input x-model="dokter.dokter" placeholder="Dokter" />
                        </div>
                    </div>
                    <div class="col-span-2">
                        <x-ts:select.styled x-model="dokter.status" :options="$statusOptions" select="value:value|label:label" placeholder="Status" />
                    </div>
                    <div class="col-span-1">
                        <x-ts:number x-model="dokter.jumlah" placeholder="Jumlah" min="1" />
                    </div>
                </div>
            </template>
            <div class="flex w-full">
                <span role="button" class="text-sm text-indigo-500 hover:font-semibold hover:italic" @click="addDokter">Tambah Dokter</span>
            </div>

        </div>

        <div class="flex flex-col justify-end gap-2 pt-4">
            <x-ts:button type="submit" sm class="text-sm font-semibold" role="button">Simpan</x-ts:button>
        </div>
    </form>
</div>
