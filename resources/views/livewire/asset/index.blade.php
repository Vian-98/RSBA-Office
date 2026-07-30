<div class="flex w-full flex-col gap-2">

    <div class="flex flex-row justify-between rounded-lg bg-white p-4">
        <div class="relative w-3/4 lg:w-1/3">
            <input id="search-asset" placeholder="Cari Barang..." type="text" class="h-8 w-full rounded-lg border-gray-200 px-10 transition-all duration-300 focus:outline-none" autocomplete="off" />

            <!-- Icon (Search) -->
            <x-ts:icon name="tabler.scan" class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
        </div>

    </div>

    <div class="w-full rounded-lg bg-white p-4">
        <livewire:Asset.TableAsset :key="Str::random()" />
    </div>

</div>
