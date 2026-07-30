<div class="flex flex-col gap-2">

    <livewire:Asset.Title :assetBarang="$assetBarang" :key="'title' . $assetBarang->id" />

    <form wire:submit.prevent="saveSpecs" class="flex w-full flex-col gap-2">
        <div x-data="{
            specs: @entangle('specs'),
            addSpec() {
                $wire.addSpec();
            },
            removeSpec(index) {
                $wire.removeSpec(index);
            }
        }" class="rounded-md border border-indigo-200">
            <template x-for="(item, index) in specs" :key="index">
                <div class="grid grid-cols-8 items-center gap-1 hover:bg-gray-50">

                    <div class="col-span-2">
                        <x-filament::input x-model="item.label" type="text" placeholder="Label" class="w-full rounded-md border border-gray-300 px-3 py-2" />
                    </div>

                    <div class="col-span-5">
                        <x-filament::input x-model="item.value" type="text" placeholder="Value" class="w-full rounded-md border border-gray-300 px-3 py-2" />
                    </div>

                    <div class="justify-self-end pe-2">
                        <button type="button" @click="removeSpec(index)" class="text-red-500">
                            <x-tabler-trash class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <div class="flex justify-between gap-4">
            <span @click="$wire.addSpec()" role="button" class="text-sm text-indigo-500 hover:font-semibold hover:italic"> + Specs</span>
            <x-ts:button sm color="green" type="submit" class="text-sm text-white">
                Simpan
            </x-ts:button>
        </div>
    </form>
</div>
